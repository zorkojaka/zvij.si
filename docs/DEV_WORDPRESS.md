# Clean WordPress Dev App

This is the isolated Docker WordPress/WooCommerce app for:

```text
https://dev.inteligent.si
```

It is intentionally separate from live `zvij.si` and does not mount the legacy WordPress import as the active app.

## Files

- `docker-compose.yml` defines `wordpress`, `mariadb`, optional `adminer`, and a `wp-cli` helper.
- `.env.example` contains safe placeholder values only.
- `wp-content/themes/zvij-theme/` is the minimal dev theme.
- `wp-content/plugins/zvij-core/` is the minimal dev plugin.
- `scripts/wp-install-dev.sh` installs WordPress, activates the theme/plugin, and installs WooCommerce.

## Environment

Create the real env file outside git:

```bash
cp .env.example /var/www/dev.inteligent.si/.env
chmod 600 /var/www/dev.inteligent.si/.env
```

Then replace the placeholder passwords. Do not commit real secrets.

Optional install-only values:

```bash
WORDPRESS_ADMIN_USER=admin
WORDPRESS_ADMIN_PASSWORD=<secret>
WORDPRESS_ADMIN_EMAIL=admin@example.test
WORDPRESS_TITLE="Zvij.si dev"
```

## Run

From the repo checkout:

```bash
docker compose --env-file /var/www/dev.inteligent.si/.env config
docker compose --env-file /var/www/dev.inteligent.si/.env up -d --build
WORDPRESS_ADMIN_PASSWORD=<secret> scripts/wp-install-dev.sh
scripts/wp-baseline-dev.sh
```

WordPress listens only on the server loopback interface:

```text
127.0.0.1:8098
```

Adminer is optional and only starts with the tools profile:

```bash
docker compose --env-file /var/www/dev.inteligent.si/.env --profile tools up -d adminer
```

## Nginx

Only the `dev.inteligent.si` server block should proxy to the container:

```nginx
location / {
  proxy_pass http://127.0.0.1:8098;
  proxy_http_version 1.1;
  proxy_set_header Host $host;
  proxy_set_header X-Real-IP $remote_addr;
  proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
  proxy_set_header X-Forwarded-Proto https;
  proxy_set_header X-Forwarded-Host $host;
  proxy_set_header X-Forwarded-Port 443;
}
```

Validate before reload:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

## Deploy

The server deploy script resets the checkout to the selected branch, loads the external env file if present, runs Compose, and checks the URL.

```bash
cd /home/jaka/apps/zvijsi/zvij.si
ZVIJ_DEPLOY_BRANCH=chore/docker-wordpress-dev scripts/deploy-dev.sh
```

## Baseline Content

After WordPress is installed, `scripts/wp-baseline-dev.sh` can be rerun safely. It:

- forces dev URLs to `https://dev.inteligent.si`
- disables search engine indexing
- activates `zvij-theme`, `zvij-core`, and WooCommerce
- creates the baseline pages and primary menu
- sets `Domov` as the static homepage
- sets `Trgovina` as the WooCommerce shop page
- creates placeholder product categories and draft placeholder products
