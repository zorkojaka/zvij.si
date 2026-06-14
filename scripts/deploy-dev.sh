#!/usr/bin/env bash
set -Eeuo pipefail

# Server-side dev deploy for dev.inteligent.si.
# Safe by design:
# - only uses /home/jaka/apps/zvijsi/zvij.si and /var/www/dev.inteligent.si
# - never touches live zvij.si paths
# - keeps secrets in an external .env file
# - deploys only the isolated Docker Compose dev app

APP_NAME="zvij-dev"
APP_DIR="${ZVIJ_APP_DIR:-/home/jaka/apps/zvijsi/zvij.si}"
BRANCH="${ZVIJ_DEPLOY_BRANCH:-chore/docker-wordpress-dev}"
WEB_ROOT="${ZVIJ_WEB_ROOT:-/var/www/dev.inteligent.si}"
ENV_FILE="${ZVIJ_ENV_FILE:-$WEB_ROOT/.env}"
URL="https://dev.inteligent.si"
LOCK_FILE="/tmp/deploy-zvij-dev.lock"
COMPOSE_PROJECT_NAME="${ZVIJ_COMPOSE_PROJECT_NAME:-zvij-dev}"
WORDPRESS_PORT="${WORDPRESS_PORT:-8098}"

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
    log "User jaka may need a new SSH login/session after usermod -aG docker jaka"
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

  log "Health check: $URL"
  if curl -fsS --max-time 20 "$URL" >/dev/null; then
    log "Public URL health check passed"
  else
    die "Public URL health check failed: $URL"
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

require_user
require_safe_paths

[ -d "$APP_DIR/.git" ] || die "Missing git repository at $APP_DIR"

run mkdir -p "$WEB_ROOT"

cd "$APP_DIR"

log "1. Fetch from git"
run git fetch origin

log "2. Verify origin/$BRANCH exists"
run git rev-parse --verify "origin/$BRANCH"

log "3. Check out $BRANCH"
run git checkout "$BRANCH"

log "4. Reset tracked files to origin/$BRANCH"
run git reset --hard "origin/$BRANCH"

log "5. Load environment file if present"
if [ -f "$ENV_FILE" ]; then
  # shellcheck disable=SC1090
  set -a
  . "$ENV_FILE"
  set +a
  log "Loaded env file: $ENV_FILE"
  WORDPRESS_PORT="${WORDPRESS_PORT:-8098}"
else
  log "No env file found at $ENV_FILE"
  log "Using safe Compose defaults. Create $ENV_FILE with real dev secrets before shared use."
fi

log "6. Docker Compose deploy hook"
require_docker

if COMPOSE_FILE="$(find_compose_file)"; then
  log "Compose file found: $COMPOSE_FILE"
  if [ -f "$ENV_FILE" ]; then
    run docker_compose --project-name "$COMPOSE_PROJECT_NAME" --env-file "$ENV_FILE" -f "$COMPOSE_FILE" config
    run docker_compose --project-name "$COMPOSE_PROJECT_NAME" --env-file "$ENV_FILE" -f "$COMPOSE_FILE" up -d --build --remove-orphans
  else
    run docker_compose --project-name "$COMPOSE_PROJECT_NAME" -f "$COMPOSE_FILE" config
    run docker_compose --project-name "$COMPOSE_PROJECT_NAME" -f "$COMPOSE_FILE" up -d --build --remove-orphans
  fi
else
  die "No Docker Compose file found"
fi

log "7. Health check"
health_check

log "8. Rollback note"
log "Rollback for now: rerun with ZVIJ_DEPLOY_BRANCH=<previous-safe-branch-or-tag>."

log "== DEPLOY ZVIJ DEV DONE =="
log "Final URL: $URL"
