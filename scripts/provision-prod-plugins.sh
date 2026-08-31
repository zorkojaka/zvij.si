#!/usr/bin/env bash
set -Eeuo pipefail

# Prenese vticnike in jezikovne pakete iz dev v prod stack.
#
# Zakaj obstaja: docker-compose.prod.yml bind-mounta samo zvij-theme in
# zvij-core, WooCommerce / WP Mail SMTP / Revolut gateway pa v dev okolje
# namesti scripts/wp-install-dev.sh v Docker volume. Sveze postavljen prod
# stack jih zato NIMA in stran po uvozu baze pade na "WooCommerce not loaded".
# Kopiranje iz dev-a zagotovi enake verzije, kot so bile testirane.

DEV_WP="${ZVIJ_DEV_WP:-zvij-dev-wordpress}"
PROD_WP="${ZVIJ_PROD_WP:-zvij-prod-wordpress}"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

for name in woocommerce wp-mail-smtp revolut-gateway-for-woocommerce; do
  docker cp "$DEV_WP:/var/www/html/wp-content/plugins/$name" "$TMP/$name" >/dev/null
  docker cp "$TMP/$name" "$PROD_WP:/var/www/html/wp-content/plugins/" >/dev/null
  rm -rf "${TMP:?}/$name"
  echo "vticnik: $name"
done

docker cp "$DEV_WP:/var/www/html/wp-content/languages" "$TMP/languages" >/dev/null
docker cp "$TMP/languages" "$PROD_WP:/var/www/html/wp-content/" >/dev/null
echo "jezikovni paketi: sl_SI"

# zvij-core in zvij-theme sta read-only bind mounta, zato chown na njiju
# javi napako - to je pricakovano in neskodljivo.
docker exec "$PROD_WP" chown -R www-data:www-data /var/www/html/wp-content 2>/dev/null || true

docker exec "$PROD_WP" ls /var/www/html/wp-content/plugins
