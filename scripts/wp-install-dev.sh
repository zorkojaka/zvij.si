#!/usr/bin/env bash
set -Eeuo pipefail

ENV_FILE="${ZVIJ_ENV_FILE:-/var/www/dev.inteligent.si/.env}"

if [ -f "$ENV_FILE" ]; then
  # shellcheck disable=SC1090
  set -a
  . "$ENV_FILE"
  set +a
fi

URL="${WORDPRESS_URL:-https://dev.inteligent.si}"
TITLE="${WORDPRESS_TITLE:-Zvij.si dev}"
ADMIN_USER="${WORDPRESS_ADMIN_USER:-admin}"
ADMIN_EMAIL="${WORDPRESS_ADMIN_EMAIL:-admin@example.test}"
ADMIN_PASSWORD="${WORDPRESS_ADMIN_PASSWORD:-}"
COMPOSE_PROJECT_NAME="${ZVIJ_COMPOSE_PROJECT_NAME:-zvij-dev}"

die() {
  printf 'ERROR: %s\n' "$*" >&2
  exit 1
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

[ -n "$ADMIN_PASSWORD" ] || die "Set WORDPRESS_ADMIN_PASSWORD in your shell or external .env before installing WordPress"

compose_args=(--project-name "$COMPOSE_PROJECT_NAME")
if [ -f "$ENV_FILE" ]; then
  compose_args+=(--env-file "$ENV_FILE")
fi

if docker_compose "${compose_args[@]}" --profile tools run --rm wp-cli wp core is-installed; then
  printf 'WordPress is already installed.\n'
else
  docker_compose "${compose_args[@]}" --profile tools run --rm wp-cli \
    wp core install \
      --url="$URL" \
      --title="$TITLE" \
      --admin_user="$ADMIN_USER" \
      --admin_password="$ADMIN_PASSWORD" \
      --admin_email="$ADMIN_EMAIL" \
      --skip-email
fi

docker_compose "${compose_args[@]}" --profile tools run --rm wp-cli \
  wp theme activate zvij-theme

docker_compose "${compose_args[@]}" --profile tools run --rm wp-cli \
  wp plugin activate zvij-core

if docker_compose "${compose_args[@]}" --profile tools run --rm wp-cli wp plugin is-installed woocommerce; then
  printf 'WooCommerce is already installed.\n'
else
  docker_compose "${compose_args[@]}" --profile tools run --rm wp-cli \
    wp plugin install woocommerce
fi

docker_compose "${compose_args[@]}" --profile tools run --rm wp-cli \
  wp plugin activate woocommerce

printf 'WordPress dev install ready: %s\n' "$URL"
