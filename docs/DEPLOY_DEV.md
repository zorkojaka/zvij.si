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

Deploy script:

```text
/home/jaka/apps/zvijsi/zvij.si/scripts/deploy-dev.sh
```

Environment file:

```text
/var/www/dev.inteligent.si/.env
```

The `.env` file is intentionally outside the repo.

## Required Environment Variables

The deploy script has safe defaults, but GitHub Actions may pass:

```text
ZVIJ_DEPLOY_BRANCH=chore/dev-deploy-script
ZVIJ_APP_DIR=/home/jaka/apps/zvijsi/zvij.si
ZVIJ_WEB_ROOT=/var/www/dev.inteligent.si
ZVIJ_ENV_FILE=/var/www/dev.inteligent.si/.env
ZVIJ_COMPOSE_PROJECT_NAME=zvij-dev
```

Future app `.env` values should be added only after the clean WordPress/Docker structure exists. Expected future values:

```text
WP_ENV=development
WP_HOME=https://dev.inteligent.si
WP_SITEURL=https://dev.inteligent.si/wp
DB_NAME=
DB_USER=
DB_PASSWORD=
DB_HOST=
WORDPRESS_AUTH_KEY=
WORDPRESS_SECURE_AUTH_KEY=
WORDPRESS_LOGGED_IN_KEY=
WORDPRESS_NONCE_KEY=
WORDPRESS_AUTH_SALT=
WORDPRESS_SECURE_AUTH_SALT=
WORDPRESS_LOGGED_IN_SALT=
WORDPRESS_NONCE_SALT=
```

Do not put production credentials in this file.

## Manual Deploy

Run on the Hetzner server:

```bash
cd /home/jaka/apps/zvijsi/zvij.si
ZVIJ_DEPLOY_BRANCH=chore/dev-deploy-script scripts/deploy-dev.sh
```

After the dev branch changes, set `ZVIJ_DEPLOY_BRANCH` to the branch that should be deployed.

## GitHub Action SSH Command

Jaka can configure the GitHub Action to SSH into the server and run:

```bash
cd /home/jaka/apps/zvijsi/zvij.si && ZVIJ_DEPLOY_BRANCH="${GITHUB_REF_NAME:-chore/dev-deploy-script}" scripts/deploy-dev.sh
```

If the Action already knows the branch name, pass it explicitly:

```bash
cd /home/jaka/apps/zvijsi/zvij.si && ZVIJ_DEPLOY_BRANCH="chore/dev-deploy-script" scripts/deploy-dev.sh
```

## What The Script Does Today

Because the repo does not yet contain Docker Compose or a clean app implementation, the script currently:

1. Locks deploys with `/tmp/deploy-zvij-dev.lock`.
2. Verifies it is using only the expected dev paths.
3. Creates `/var/www/dev.inteligent.si` if needed.
4. Fetches from GitHub.
5. Resets tracked files to `origin/$ZVIJ_DEPLOY_BRANCH`.
6. Loads `/var/www/dev.inteligent.si/.env` if present.
7. Looks for a Compose file.
8. If Compose exists, runs Docker Compose pull/build/up for this project only.
9. If Compose does not exist, prints TODOs and exits safely.
10. Prints the final URL.

## Future Docker Hook

When the app structure exists, add one of:

```text
docker-compose.yml
docker-compose.yaml
compose.yml
compose.yaml
```

The script will then run:

```text
docker compose --project-name zvij-dev --env-file /var/www/dev.inteligent.si/.env -f <compose-file> pull
docker compose --project-name zvij-dev --env-file /var/www/dev.inteligent.si/.env -f <compose-file> up -d --build --remove-orphans
```

Only containers in Compose project `zvij-dev` should be affected.

## Health Check

The script checks:

```text
https://dev.inteligent.si
```

Before nginx/app setup exists, this may fail without failing the deploy. Once the app is implemented, make this check strict.

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
- No Docker app implementation is created in this branch.
- No legacy WordPress files are deleted.
- No live `zvij.si` path is modified.
