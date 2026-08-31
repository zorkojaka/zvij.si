#!/usr/bin/env bash
set -Eeuo pipefail

# Server-side production deploy for zvij.si (glej docs/PROD_MIGRATION_RUNBOOK.md).
# Safe by design:
# - only uses /home/jaka/apps/zvijsi/zvij.si and /var/www/zvij.si-app
# - refuses to run without an explicit production .env (no default secrets)
# - deploys only the isolated Docker Compose project zvij-prod
# - never touches the dev stack, other apps or other web roots

APP_NAME="zvij-prod"
APP_DIR="${ZVIJ_APP_DIR:-/home/jaka/apps/zvijsi/zvij.si}"
BRANCH="${ZVIJ_DEPLOY_BRANCH:-main}"
WEB_ROOT="${ZVIJ_WEB_ROOT:-/var/www/zvij.si-app}"
ENV_FILE="${ZVIJ_ENV_FILE:-$WEB_ROOT/.env}"
URL="https://zvij.si"
LOCK_DIR="$APP_DIR/.deploy"
LOCK_FILE="$LOCK_DIR/deploy-prod.lock"
COMPOSE_PROJECT_NAME="${ZVIJ_COMPOSE_PROJECT_NAME:-zvij-prod}"
COMPOSE_FILE="$APP_DIR/docker-compose.prod.yml"
WORDPRESS_PORT="${WORDPRESS_PORT:-8099}"
# Pred DNS preklopom javni URL še ne kaže sem; ZVIJ_ALLOW_PUBLIC_FAIL=1
# spremeni javni health check v opozorilo namesto napake.
ALLOW_PUBLIC_FAIL="${ZVIJ_ALLOW_PUBLIC_FAIL:-0}"

log() {
  printf '%s %s\n' "[$(date -u '+%Y-%m-%dT%H:%M:%SZ')]" "$*"
}

die() {
  log "ERROR: $*"
  exit 1
}

run() {
  log "+ $*"
  "$@"
}

require_user() {
  local current_user

  current_user="$(id -un)"
  if [ "$current_user" != "jaka" ]; then
    die "Deploy must run as user jaka, not $current_user"
  fi
}

require_safe_paths() {
  case "$APP_DIR" in
    /home/jaka/apps/zvijsi/zvij.si) ;;
    *) die "Refusing unexpected APP_DIR: $APP_DIR" ;;
  esac

  case "$WEB_ROOT" in
    /var/www/zvij.si-app) ;;
    *) die "Refusing unexpected WEB_ROOT: $WEB_ROOT" ;;
  esac
}

docker_compose() {
  docker compose "$@"
}

require_docker() {
  if ! command -v docker >/dev/null 2>&1; then
    die "Docker command is not available"
  fi

  if ! docker compose version >/dev/null 2>&1; then
    die "Docker Compose plugin is not available"
  fi

  if ! docker ps >/dev/null 2>&1; then
    die "User jaka cannot access Docker. Verify docker group membership and Docker socket permissions."
  fi
}

health_check() {
  if ! command -v curl >/dev/null 2>&1; then
    log "curl not installed; skipping health check"
    return 0
  fi

  log "Health check: http://127.0.0.1:$WORDPRESS_PORT"
  for attempt in $(seq 1 30); do
    if curl -fsS --max-time 5 "http://127.0.0.1:$WORDPRESS_PORT/wp-login.php" >/dev/null; then
      log "Local WordPress health check passed"
      break
    fi

    if [ "$attempt" -eq 30 ]; then
      die "Local WordPress health check failed on port $WORDPRESS_PORT"
    fi

    sleep 2
  done

  # Pred DNS preklopom se zvij.si se vedno razresi na staro gostovanje, zato
  # bi curl na $URL uspel proti TUJI strani in dal lazen obcutek varnosti.
  # Javni check ima smisel samo, ce domena kaze na ta streznik.
  host_ip="$(getent ahostsv4 zvij.si 2>/dev/null | awk 'NR==1{print $1}')"
  server_ips="$(hostname -I 2>/dev/null || true)"
  if [ -z "$host_ip" ]; then
    log "WARNING: zvij.si se ne razresi — javni health check preskocen"
    return 0
  fi
  if ! printf '%s' "$server_ips" | grep -qw "$host_ip"; then
    log "WARNING: zvij.si kaze na $host_ip, ta streznik je ${server_ips% } — javni health check preskocen (DNS se ni preklopljen)"
    return 0
  fi

  log "Health check: $URL"
  if curl -fsS --max-time 20 "$URL" >/dev/null; then
    log "Public URL health check passed"
  elif [ "$ALLOW_PUBLIC_FAIL" = "1" ]; then
    log "WARNING: public URL check failed ($URL); allowed by ZVIJ_ALLOW_PUBLIC_FAIL=1 (pre-DNS deploy)"
  else
    die "Public URL health check failed: $URL (pred DNS preklopom uporabi ZVIJ_ALLOW_PUBLIC_FAIL=1)"
  fi
}

log "== DEPLOY ZVIJ PROD START =="
log "App dir: $APP_DIR"
log "Branch: $BRANCH"
log "Web root: $WEB_ROOT"
log "URL: $URL"

require_user
require_safe_paths

[ -d "$APP_DIR/.git" ] || die "Missing git repository at $APP_DIR"
[ -f "$COMPOSE_FILE" ] || die "Missing compose file: $COMPOSE_FILE"
[ -f "$ENV_FILE" ] || die "Production env file is required: $ENV_FILE (nikoli ne deploya s privzetimi gesli)"

cd "$APP_DIR"

run mkdir -p .deploy
exec 9>"$LOCK_FILE"
if ! flock -w 600 9; then
  die "Another prod deploy is still running (lock busy > 600s)"
fi

run mkdir -p "$WEB_ROOT"

log "1. Fetch from git"
run git fetch origin

log "2. Verify origin/$BRANCH exists"
run git rev-parse --verify "origin/$BRANCH"

log "3. Check out $BRANCH"
run git checkout "$BRANCH"

log "4. Reset tracked files to origin/$BRANCH"
run git reset --hard "origin/$BRANCH"

log "5. Load environment file"
# shellcheck disable=SC1090
set -a
. "$ENV_FILE"
set +a
log "Loaded env file: $ENV_FILE"
WORDPRESS_PORT="${WORDPRESS_PORT:-8099}"

log "6. Docker Compose deploy"
require_docker
run docker_compose --project-name "$COMPOSE_PROJECT_NAME" --env-file "$ENV_FILE" -f "$COMPOSE_FILE" config --quiet
run docker_compose --project-name "$COMPOSE_PROJECT_NAME" --env-file "$ENV_FILE" -f "$COMPOSE_FILE" up -d --build --remove-orphans

log "7. Health check"
health_check

log "8. Rollback note"
log "Rollback: rerun with ZVIJ_DEPLOY_BRANCH=<previous-safe-tag>; DNS rollback glej PROD_MIGRATION_RUNBOOK.md."

log "== DEPLOY ZVIJ PROD DONE =="
log "Final URL: $URL"
