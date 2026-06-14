# Dev Deployment

This document covers only the development target:

```text
https://dev.inteligent.si
```

It must never be used for live `zvij.si`.

## Server Convention

Existing apps on this Hetzner server use:

- repository checkouts under `/home/jaka/apps/...`
- web roots under `/var/www/...`
- server-side shell scripts for CI/manual deploy
- `git fetch` + reset to the selected remote branch
- `flock` locks to prevent overlapping deploys

Zvij.si follows that convention.

## Required Paths

Repository checkout:

```text
/home/jaka/apps/zvijsi/zvij.si
```

Dev web root:

```text
/var/www/dev.inteligent.si
```

Static placeholder document root:

```text
/var/www/dev.inteligent.si/public
```

Deploy script:

```text
/home/jaka/apps/zvijsi/zvij.si/scripts/deploy-dev.sh
```

Environment file:

```text
/var/www/dev.inteligent.si/.env
```

The `.env` file is intentionally outside the repo.

Nginx server block:

```text
/etc/nginx/sites-available/dev.inteligent.si
/etc/nginx/sites-enabled/dev.inteligent.si -> /etc/nginx/sites-available/dev.inteligent.si
```

Let's Encrypt certificate:

```text
/etc/letsencrypt/live/dev.inteligent.si/fullchain.pem
/etc/letsencrypt/live/dev.inteligent.si/privkey.pem
```

## Required Environment Variables

The deploy script has safe defaults, but GitHub Actions may pass:

```text
ZVIJ_DEPLOY_BRANCH=chore/docker-wordpress-dev
ZVIJ_APP_DIR=/home/jaka/apps/zvijsi/zvij.si
ZVIJ_WEB_ROOT=/var/www/dev.inteligent.si
ZVIJ_ENV_FILE=/var/www/dev.inteligent.si/.env
ZVIJ_COMPOSE_PROJECT_NAME=zvij-dev
```

App `.env` values:

```text
WORDPRESS_DB_NAME=zvij_dev
WORDPRESS_DB_USER=zvij_dev
WORDPRESS_DB_PASSWORD=
WORDPRESS_DB_ROOT_PASSWORD=
WORDPRESS_PORT=8098
WORDPRESS_URL=https://dev.inteligent.si
```

Do not put production credentials in this file.

## Manual Deploy

Run on the Hetzner server:

```bash
cd /home/jaka/apps/zvijsi/zvij.si
ZVIJ_DEPLOY_BRANCH=chore/docker-wordpress-dev scripts/deploy-dev.sh
```

After the dev branch changes, set `ZVIJ_DEPLOY_BRANCH` to the branch that should be deployed.

## Nginx And SSL Setup

Current dev nginx setup is intentionally separate from all production vhosts.

Active behavior after the Docker dev app is deployed:

- `http://dev.inteligent.si/` returns `301` to `https://dev.inteligent.si/`.
- `https://dev.inteligent.si/` proxies to the clean WordPress container.
- WordPress listens on `127.0.0.1:8098`.
- The static placeholder root remains available for ACME challenges only.

The setup was created in this order:

1. Created `/var/www/dev.inteligent.si/public`.
2. Added placeholder `/var/www/dev.inteligent.si/public/index.html`.
3. Added HTTP-only nginx vhost at `/etc/nginx/sites-available/dev.inteligent.si`.
4. Enabled it with a symlink in `/etc/nginx/sites-enabled/`.
5. Ran `sudo nginx -t`.
6. Reloaded nginx.
7. Verified HTTP placeholder.
8. Issued the certificate with Certbot webroot mode.
9. Updated only the `dev.inteligent.si` vhost for HTTPS.
10. Ran `sudo nginx -t` again.
11. Reloaded nginx again.
12. Verified HTTP redirect, HTTPS response, and certificate subject.

Certbot command used:

```bash
sudo certbot certonly --webroot \
  -w /var/www/dev.inteligent.si/public \
  -d dev.inteligent.si \
  --agree-tos \
  --non-interactive \
  --redirect \
  --email jaka@inteligent.si
```

The `certonly --webroot` method was used so Certbot would not edit existing nginx production vhosts.

Verification commands:

```bash
sudo nginx -t
sudo systemctl reload nginx
curl -I --max-time 20 http://dev.inteligent.si/
curl -i --max-time 20 http://127.0.0.1:8098/
curl -i --max-time 20 https://dev.inteligent.si/
echo | openssl s_client -servername dev.inteligent.si -connect dev.inteligent.si:443 2>/dev/null | openssl x509 -noout -subject -issuer -dates
```

Observed verification result on 2026-06-14:

```text
http://dev.inteligent.si/ -> 301 Location: https://dev.inteligent.si/
https://dev.inteligent.si/ -> HTTP/2 200
certificate subject -> CN = dev.inteligent.si
certificate issuer -> Let's Encrypt YR1
certificate expiry -> 2026-09-11
mixed content grep for http:// -> no matches
WordPress home/siteurl -> https://dev.inteligent.si
```

## GitHub Action SSH Command

Jaka can configure the GitHub Action to SSH into the server and run:

```bash
cd /home/jaka/apps/zvijsi/zvij.si && ZVIJ_DEPLOY_BRANCH="${GITHUB_REF_NAME:-chore/docker-wordpress-dev}" scripts/deploy-dev.sh
```

If the Action already knows the branch name, pass it explicitly:

```bash
cd /home/jaka/apps/zvijsi/zvij.si && ZVIJ_DEPLOY_BRANCH="chore/docker-wordpress-dev" scripts/deploy-dev.sh
```

## What The Script Does Today

1. Locks deploys with `.deploy/deploy-dev.lock` inside the project checkout.
2. Verifies it is using only the expected dev paths.
3. Creates `/var/www/dev.inteligent.si` if needed.
4. Fetches from GitHub.
5. Checks out `$ZVIJ_DEPLOY_BRANCH`.
6. Resets tracked files to `origin/$ZVIJ_DEPLOY_BRANCH`.
7. Loads `/var/www/dev.inteligent.si/.env` if present.
8. Verifies Docker access for user `jaka`.
9. Runs Docker Compose config and build/up for this project only.
10. Checks `http://127.0.0.1:8098/wp-login.php`.
11. Checks `https://dev.inteligent.si`.
12. Prints the final URL.

Only containers in Compose project `zvij-dev` should be affected.

## Health Check

The script checks:

```text
http://127.0.0.1:8098/wp-login.php
https://dev.inteligent.si
```

## Rollback Plan

Current rollback:

```bash
cd /home/jaka/apps/zvijsi/zvij.si
ZVIJ_DEPLOY_BRANCH=<previous-safe-branch-or-tag> scripts/deploy-dev.sh
```

Future rollback after real releases exist:

- pin the previous Git tag or release branch
- rerun deploy with that ref
- if Docker images are tagged, redeploy the previous image tag
- if release directories are introduced, flip the `current` symlink back

## Must Never Be Touched

The dev deploy must never touch:

- live `zvij.si`
- `/var/www/zvij.si`
- live production database
- live payment credentials
- production uploads
- other apps under `/home/jaka/apps`
- other web roots under `/var/www`

## Current Limitations

- No production deploy is implemented.
- No legacy WordPress files are deleted.
- No live `zvij.si` path is modified.
- The nginx proxy edit must be applied with server privileges because `/etc/nginx` is outside the repository.
