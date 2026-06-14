#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="/home/jaka/apps/zvijsi/zvij.si"

cd "$PROJECT_DIR"

docker compose run --rm wp-cli eval-file scripts/wp-import-live-content-dev.php
