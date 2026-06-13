#!/usr/bin/env bash
set -Eeuo pipefail

# Server-side dev deploy for dev.inteligent.si.
# Safe by design:
# - only uses /home/jaka/apps/zvijsi/zvij.si and /var/www/dev.inteligent.si
# - never touches live zvij.si paths
# - keeps secrets in an external .env file
# - prepares Docker deploy hooks without inventing app infrastructure before it exists

APP_NAME="zvij-dev"
APP_DIR="${ZVIJ_APP_DIR:-/home/jaka/apps/zvijsi/zvij.si}"
BRANCH="${ZVIJ_DEPLOY_BRANCH:-chore/dev-deploy-script}"
WEB_ROOT="${ZVIJ_WEB_ROOT:-/var/www/dev.inteligent.si}"
ENV_FILE="${ZVIJ_ENV_FILE:-$WEB_ROOT/.env}"
URL="https://dev.inteligent.si"
LOCK_FILE="/tmp/deploy-zvij-dev.lock"
COMPOSE_PROJECT_NAME="${ZVIJ_COMPOSE_PROJECT_NAME:-zvij-dev}"

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

require_safe_paths() {
  case "$APP_DIR" in
    /home/jaka/apps/zvijsi/zvij.si) ;;
    *) die "Refusing unexpected APP_DIR: $APP_DIR" ;;
  esac

  case "$WEB_ROOT" in
    /var/www/dev.inteligent.si) ;;
    *) die "Refusing unexpected WEB_ROOT: $WEB_ROOT" ;;
  esac

  if [ "$WEB_ROOT" = "/var/www/zvij.si" ] || [ "$WEB_ROOT" = "/var/www/html" ]; then
    die "Refusing to deploy to a live/shared web root: $WEB_ROOT"
  fi
}

find_compose_file() {
  for candidate in \
    "$APP_DIR/docker-compose.yml" \
    "$APP_DIR/docker-compose.yaml" \
    "$APP_DIR/compose.yml" \
    "$APP_DIR/compose.yaml"
  do
    if [ -f "$candidate" ]; then
      printf '%s\n' "$candidate"
      return 0
    fi
  done

  return 1
}

docker_compose() {
  if docker compose version >/dev/null 2>&1; then
    docker compose "$@"
  elif command -v docker-compose >/dev/null 2>&1; then
    docker-compose "$@"
  else
    die "Docker Compose is not available"
  fi
}

health_check() {
  if command -v curl >/dev/null 2>&1; then
    log "Health check: $URL"
    if curl -fsS --max-time 20 "$URL" >/dev/null; then
      log "Health check passed"
    else
      log "Health check did not pass yet. This can be expected before nginx/app config exists."
    fi
  else
    log "curl not installed; skipping health check"
  fi
}

exec 9>"$LOCK_FILE"
if ! flock -w 600 9; then
  die "Another dev deploy is still running (lock busy > 600s)"
fi

log "== DEPLOY ZVIJ DEV START =="
log "App dir: $APP_DIR"
log "Branch: $BRANCH"
log "Web root: $WEB_ROOT"
log "URL: $URL"

require_safe_paths

[ -d "$APP_DIR/.git" ] || die "Missing git repository at $APP_DIR"

run mkdir -p "$WEB_ROOT"

cd "$APP_DIR"

log "1. Fetch from git"
run git fetch origin

log "2. Verify origin/$BRANCH exists"
run git rev-parse --verify "origin/$BRANCH"

log "3. Reset tracked files to origin/$BRANCH"
run git reset --hard "origin/$BRANCH"

log "4. Load environment file if present"
if [ -f "$ENV_FILE" ]; then
  # shellcheck disable=SC1090
  set -a
  . "$ENV_FILE"
  set +a
  log "Loaded env file: $ENV_FILE"
else
  log "No env file found at $ENV_FILE"
  log "TODO: create it before real app deploy. Keep secrets out of git."
fi

log "5. Docker Compose deploy hook"
if COMPOSE_FILE="$(find_compose_file)"; then
  log "Compose file found: $COMPOSE_FILE"
  if [ -f "$ENV_FILE" ]; then
    run docker_compose --project-name "$COMPOSE_PROJECT_NAME" --env-file "$ENV_FILE" -f "$COMPOSE_FILE" pull
    run docker_compose --project-name "$COMPOSE_PROJECT_NAME" --env-file "$ENV_FILE" -f "$COMPOSE_FILE" up -d --build --remove-orphans
  else
    run docker_compose --project-name "$COMPOSE_PROJECT_NAME" -f "$COMPOSE_FILE" pull
    run docker_compose --project-name "$COMPOSE_PROJECT_NAME" -f "$COMPOSE_FILE" up -d --build --remove-orphans
  fi
else
  log "No Docker Compose file found."
  log "TODO: add docker-compose.yml/compose.yml when the clean dev app exists."
  log "TODO: define WordPress/PHP/database services for dev.inteligent.si."
  log "TODO: keep uploads/database/secrets outside git."
fi

log "6. Health check"
health_check

log "7. Rollback note"
log "Rollback for now: rerun with ZVIJ_DEPLOY_BRANCH=<previous-safe-branch-or-tag>."
log "Once Docker/app releases exist, add image/tag or release-directory rollback."

log "== DEPLOY ZVIJ DEV DONE =="
log "Final URL: $URL"
