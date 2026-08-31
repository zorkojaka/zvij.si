#!/usr/bin/env bash
set -Eeuo pipefail

# Dnevni backup produkcijske baze (zvij-prod) + tedenski backup uploadov.
# Cron (user jaka): 40 4 * * * /home/jaka/apps/zvijsi/zvij.si/scripts/backup-prod-db.sh
# Hrani 14 dnevnih dumpov in 4 tedenske arhive uploadov v ~/backups/zvij-prod.

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

echo "OK $(date -u '+%Y-%m-%dT%H:%M:%SZ') $(ls -1 "$BACKUP_DIR" | wc -l) datotek v $BACKUP_DIR"
