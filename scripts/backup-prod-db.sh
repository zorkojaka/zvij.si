#!/usr/bin/env bash
set -Eeuo pipefail

# Dnevni backup produkcijske baze (zvij-prod) + tedenski backup uploadov.
# Cron (user jaka): 40 4 * * * /home/jaka/apps/zvijsi/zvij.si/scripts/backup-prod-db.sh
# Hrani 14 dnevnih dumpov in 4 tedenske arhive uploadov v ~/backups/zvij-prod.
# Izven strežnika: šifrirana kopija v Hetzner Object Storage (zvij-backups/), baza
# dnevno pod db-DD (31-dnevna rotacija s prepisom), uploadi tedensko pod uploads-wN.
# Poverilnice: ~/.config/zvij-offsite.env, ključ: ~/.config/proflow-backup/key.
# Obnova: node scripts/offsite-upload.cjs --get zvij-backups/db-16.sql.gz.enc x.enc
#         openssl enc -d -aes-256-cbc -pbkdf2 -iter 200000 -pass file:KLJUČ -in x.enc | gunzip > zvij.sql

BACKUP_DIR="${ZVIJ_BACKUP_DIR:-/home/jaka/backups/zvij-prod}"
STAMP="$(date +%Y%m%d)"

mkdir -p "$BACKUP_DIR"

docker exec zvij-prod-mariadb sh -c 'mariadb-dump -u root -p"$MARIADB_ROOT_PASSWORD" --single-transaction zvij_prod' \
  | gzip > "$BACKUP_DIR/db-$STAMP.sql.gz.tmp"
mv "$BACKUP_DIR/db-$STAMP.sql.gz.tmp" "$BACKUP_DIR/db-$STAMP.sql.gz"

if [ "$(date +%u)" = "7" ]; then
  docker exec zvij-prod-wordpress tar -C /var/www/html/wp-content -czf - uploads \
    > "$BACKUP_DIR/uploads-$STAMP.tar.gz.tmp"
  mv "$BACKUP_DIR/uploads-$STAMP.tar.gz.tmp" "$BACKUP_DIR/uploads-$STAMP.tar.gz"
fi

(ls -1t "$BACKUP_DIR"/db-*.sql.gz 2>/dev/null || true) | tail -n +15 | xargs -r rm --
(ls -1t "$BACKUP_DIR"/uploads-*.tar.gz 2>/dev/null || true) | tail -n +5 | xargs -r rm --

OFFSITE_ENV="${ZVIJ_OFFSITE_ENV:-$HOME/.config/zvij-offsite.env}"
OFFSITE_KEY="${ZVIJ_OFFSITE_KEY:-$HOME/.config/proflow-backup/key}"
if [ -f "$OFFSITE_ENV" ] && [ -f "$OFFSITE_KEY" ]; then
  set -a; . "$OFFSITE_ENV"; set +a
  offsite() {
    openssl enc -aes-256-cbc -pbkdf2 -iter 200000 -salt -pass "file:$OFFSITE_KEY" -in "$1" -out "$1.enc"
    node "$(dirname "$0")/offsite-upload.cjs" "$1.enc" "zvij-backups/$2.enc" || echo "OPOZORILO: kopija izven strežnika ni uspela ($2)"
    rm -f "$1.enc"
  }
  offsite "$BACKUP_DIR/db-$STAMP.sql.gz" "db-$(date +%d).sql.gz"
  if [ -f "$BACKUP_DIR/uploads-$STAMP.tar.gz" ]; then
    offsite "$BACKUP_DIR/uploads-$STAMP.tar.gz" "uploads-w$(( ($(date +%-V) % 4) + 1 )).tar.gz"
  fi
else
  echo "OPOZORILO: ni nastavitev za kopijo izven strežnika ($OFFSITE_ENV)"
fi

echo "OK $(date -u '+%Y-%m-%dT%H:%M:%SZ') $(ls -1 "$BACKUP_DIR" | wc -l) datotek v $BACKUP_DIR"
