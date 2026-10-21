#!/usr/bin/env bash
# Razvojna trgovina (dev.inteligent.si) teče le, ko jo rabimo: strežnik ima 4 GB RAM.
#   scripts/dev.sh start | stop | status
set -euo pipefail
cd "$(dirname "$0")/.."
case "${1:-status}" in
  start) docker compose -f docker-compose.yml up -d && echo "dev teče: https://dev.inteligent.si/" ;;
  stop) docker compose -f docker-compose.yml stop && echo "dev ustavljen" ;;
  *) docker compose -f docker-compose.yml ps ;;
esac
