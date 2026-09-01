#!/usr/bin/env bash
set -Eeuo pipefail

# Prenese vsebino iz dev v produkcijski stack: baza, uploads in vse
# produkcijske nastavitve po uvozu. To je izvedbeni del Faze 2 iz
# docs/PROD_MIGRATION_RUNBOOK.md, zapisan kot en ponovljiv ukaz.
#
# NE dotika se DNS, nginxa ali žive strani. Varno je pognati večkrat —
# produkcijska baza se ob vsakem zagonu prepiše z dev vsebino.
#
# Uporaba: bash scripts/sync-dev-to-prod.sh

APP_DIR="${ZVIJ_APP_DIR:-/home/jaka/apps/zvijsi/zvij.si}"
ENV_FILE="${ZVIJ_ENV_FILE:-/var/www/zvij.si-app/.env}"
DEV_DB="${ZVIJ_DEV_DB:-zvij_dev}"
PROD_DB="${ZVIJ_PROD_DB:-zvij_prod}"
DEV_URL="${ZVIJ_DEV_URL:-dev.inteligent.si}"
PROD_URL="${ZVIJ_PROD_URL:-zvij.si}"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

log() { printf '%s %s\n' "[$(date -u '+%H:%M:%SZ')]" "$*"; }
die() { log "ERROR: $*"; exit 1; }

[ -f "$ENV_FILE" ] || die "manjka produkcijski .env: $ENV_FILE"
cd "$APP_DIR"

PROD=(docker compose --project-name zvij-prod --env-file "$ENV_FILE" -f docker-compose.prod.yml)
prod_wp() { "${PROD[@]}" --profile tools run --rm wp-cli wp "$@"; }

log "1/7 izvoz dev baze"
docker compose exec -T mariadb sh -c "mariadb-dump -u root -p\"\$MARIADB_ROOT_PASSWORD\" --single-transaction --default-character-set=utf8mb4 $DEV_DB" > "$TMP/dump.sql"
log "    $(du -h "$TMP/dump.sql" | cut -f1)"

log "2/7 uvoz v produkcijsko bazo (prepis)"
"${PROD[@]}" exec -T mariadb sh -c "mariadb -u root -p\"\$MARIADB_ROOT_PASSWORD\" -e 'DROP DATABASE IF EXISTS $PROD_DB; CREATE DATABASE $PROD_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL ON $PROD_DB.* TO \"$PROD_DB\"@\"%\";'"
"${PROD[@]}" exec -T mariadb sh -c "mariadb -u root -p\"\$MARIADB_ROOT_PASSWORD\" --default-character-set=utf8mb4 $PROD_DB" < "$TMP/dump.sql"

log "3/7 zamenjava URL-jev"
prod_wp search-replace "https://$DEV_URL" "https://$PROD_URL" --all-tables --precise --report-changed-only --quiet
prod_wp search-replace "$DEV_URL" "$PROD_URL" --all-tables --precise --report-changed-only --quiet

log "4/7 sinhronizacija uploadov"
docker cp zvij-dev-wordpress:/var/www/html/wp-content/uploads "$TMP/uploads" >/dev/null
docker cp "$TMP/uploads/." zvij-prod-wordpress:/var/www/html/wp-content/uploads/ >/dev/null
docker exec zvij-prod-wordpress chown -R www-data:www-data /var/www/html/wp-content/uploads
rm -rf "$TMP/uploads"

log "5/7 produkcijske nastavitve po uvozu"
prod_wp eval-file scripts/wp-configure-permalinks.php
# Dev pošilja v Mailpit, ta nastavitev pride z bazo; prod Mailpita nima.
prod_wp eval-file scripts/wp-mail-mode.php live
# Dev ima blog_public=0, da ga iskalniki ne indeksirajo. Prod mora biti 1.
prod_wp option update blog_public 1

log "6/7 spiranje predpomnilnikov"
prod_wp rewrite flush --hard 2>/dev/null || true
prod_wp cache flush
prod_wp transient delete --all

log "7/7 preverba"
prod_wp eval '
$q = new WP_Query(["post_type"=>"product","post_status"=>"publish","posts_per_page"=>-1,"fields"=>"ids"]);
$value = 0; $tracked = 0;
foreach ($q->posts as $id) {
    $p = wc_get_product($id);
    if ($p->get_manage_stock()) { $value += $p->get_stock_quantity() * (float) $p->get_price(); $tracked++; }
}
printf("    home=%s | izdelkov=%d | z zalogo=%d (%s EUR) | narocil=%d | blog_public=%s\n",
    get_option("home"), count($q->posts), $tracked, number_format($value, 2),
    count(wc_get_orders(["limit"=>-1,"status"=>"any"])), get_option("blog_public"));'

log "KONCANO — prod stack posodobljen (DNS in ziva stran nedotaknjena)"
