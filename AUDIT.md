# Zvij.si Repository Audit

Date: 2026-06-13

Scope: repository inspection only. No existing files were deleted, moved, edited, or committed.

## 1. What is currently inside the repo

The actual Git repository is in the `zvij.si/` directory under the current workspace.

Git state:

- Branch: `main`
- Remote: `https://github.com/zorkojaka/zvij.si.git`
- Status before this audit file: clean, aligned with `origin/main`
- Recent history: two commits, both initial/import style commits

Top-level contents:

- Full WordPress core tree:
  - `wp-admin/`
  - `wp-includes/`
  - root WordPress PHP entry files such as `index.php`, `wp-login.php`, `wp-settings.php`, `xmlrpc.php`
- WordPress sample/config/support files:
  - `wp-config-sample.php`
  - `.htaccess`
  - `license.txt`
  - `readme.html`
- `wp-content/` with themes, plugins, languages, mu-plugin, and Duplicator backup metadata
- `.well-known/apple-developer-merchantid-domain-association`
- Minimal `README.md`
- `.gitignore`
- No committed `wp-config.php`
- No committed `wp-content/uploads/`

Approximate size:

- Whole repo: `428M`
- `wp-admin`: `11M`
- `wp-includes`: `55M`
- `wp-content`: `260M`
- `wp-content/plugins`: `215M`
- `wp-content/themes`: `37M`

Detected WordPress version:

- WordPress `6.7.1` from `wp-includes/version.php`

Themes:

- `wp-content/themes/plamen`
  - Plamen theme
  - Version `1.3`
  - Qode Interactive / Edge Themes
  - Tobacco Store Theme
- Default WordPress themes:
  - `twentytwentyfive`
  - `twentytwentyfour`
  - `twentytwentythree`

Plugins:

- `woocommerce`
  - WooCommerce `8.2.1`
- `woocommerce-payments`
  - WooPayments `7.4.0`
- `woocommerce-services`
  - WooCommerce Shipping & Tax `2.4.0`
- `revolut-gateway-for-woocommerce`
  - Revolut Gateway for WooCommerce `4.12.0`
- `elementor`
  - Elementor `3.17.2`
- `contact-form-7`
  - Contact Form 7 `5.8`
- `custom-css-js`
  - Simple Custom CSS and JS `3.45`
- `duplicator`
  - Duplicator `1.5.6.1`
- `envato-market`
  - Envato Market `2.0.10`
- `plamen-core`
  - Plamen Core `1.2`
- `qode-framework`
  - Qode Framework `1.1.8`
- `qi-addons-for-elementor`
- `revslider`
  - Slider Revolution `6.5.14`

MU plugins:

- `wp-content/mu-plugins/elementor-safe-mode.php`

Backup-related files:

- `wp-content/backups-dup-lite/`
  - Contains Duplicator Lite metadata and an installer backup file:
    - `Zvijsi_plamen_default_5_2023_f8c90a036e8efb8a1135_20230522141433_installer.php.bak`
  - No large Duplicator archive was found in that folder during this inspection.

Ignored runtime paths already listed in `.gitignore`:

- `wp-content/uploads/`
- `wp-content/cache/`
- `wp-content/upgrade/`
- `wp-content/backups/`
- `wp-content/backup-db/`
- `wp-content/blogs.dir/`
- `wp-content/advanced-cache.php`
- `wp-content/wp-cache-config.php`
- `wp-config.php`
- `wp-config-local.php`
- logs/temp/editor files

## 2. Whether it contains usable WordPress/WooCommerce/theme/plugin/deploy code

Yes, it contains a usable WordPress file tree and WooCommerce/plugin/theme code, but it is not a clean development project yet.

Usable as WordPress/WooCommerce code:

- WordPress core is present.
- WooCommerce is present.
- Payment/shipping plugins are present.
- Elementor and commercial theme/plugin dependencies are present.
- The Plamen theme and Plamen/Qode support plugins are present.

Not clean as a dev project:

- WordPress core is committed directly into the repo.
- Third-party plugins are committed directly into the repo.
- Commercial/vendor code is committed directly into the repo.
- There is no Composer workflow.
- There is no Docker/dev environment.
- There is no deployment workflow.
- There is no environment separation for `dev.inteligent.si`.
- There is no local `wp-config.php`, which is good for secrets, but also means the checked-out repo alone will not boot without environment-specific setup.
- There is no database dump or content export in the inspected tree, so product/page/menu/state cannot be restored from Git alone.

Deploy code:

- No deploy scripts were found.
- No Hetzner-specific files were found.
- No Docker Compose files were found.
- No Makefile was found.
- No GitHub Actions or CI YAML files were found.
- No root Composer project was found.
- No root package manager project was found.

## 3. Whether any existing scripts can help deploy to Hetzner

No.

The repository currently has no deployment automation that can be reused for Hetzner. There are no shell scripts, Docker files, CI workflows, Ansible files, Terraform files, rsync scripts, or server provisioning notes in the inspected paths.

The only potentially migration-related tooling is the committed Duplicator plugin and its backup metadata folder. That can help as a manual WordPress migration tool if a valid Duplicator archive and database package exist elsewhere, but it is not a deploy system and should not be treated as infrastructure automation.

## 4. What should be reused

Reuse as reference or controlled dependencies:

- `wp-content/themes/plamen/`
  - Keep as reference if the rebuild should preserve the current visual direction or theme-specific templates.
  - Verify license before committing or redeploying commercial theme code.
- `wp-content/plugins/plamen-core/`
  - Likely required by the Plamen theme.
  - Verify license/source before reuse.
- `wp-content/plugins/qode-framework/`
  - Likely required by Plamen/Qode theme ecosystem.
- `wp-content/plugins/qi-addons-for-elementor/`
  - Keep only if current Elementor layouts depend on it.
- `wp-content/plugins/revslider/`
  - Keep only if the rebuilt site needs existing Slider Revolution content.
  - Verify license.
- WooCommerce plugin list
  - Use as a dependency checklist for the clean rebuild.
  - Prefer installing fresh/current versions rather than committing plugin source.
- `wp-content/plugins/revolut-gateway-for-woocommerce/`
  - Reuse as a dependency requirement if Revolut remains the payment provider.
  - Do not reuse production credentials.
- `.well-known/apple-developer-merchantid-domain-association`
  - Reuse only if Apple Pay merchant verification is still needed for the new dev/prod payment setup.
- `.gitignore`
  - Reuse the intent, but expand it for the new project structure.

Reuse only as forensic/reference material:

- Existing committed WordPress core.
- Existing committed third-party plugin directories.
- Existing `.htaccess`.
- Existing Duplicator metadata.

## 5. What should be deleted or archived

For this audit step, nothing was deleted or moved.

Recommended later cleanup after creating a backup/tag/branch:

- Archive or remove committed WordPress core:
  - `wp-admin/`
  - `wp-includes/`
  - root WordPress core files such as `wp-login.php`, `wp-settings.php`, `xmlrpc.php`, etc.
- Archive or remove committed WordPress.org plugin source and reinstall through a repeatable dependency process:
  - WooCommerce
  - Elementor
  - Contact Form 7
  - WooPayments
  - WooCommerce Shipping & Tax
  - Revolut gateway
  - Duplicator
  - Envato Market
  - Simple Custom CSS and JS
- Archive Duplicator backup artifacts:
  - `wp-content/backups-dup-lite/`
- Remove runtime/generated content from Git if it appears later:
  - uploads
  - cache
  - backups
  - logs
  - upgrade folders
- Keep commercial theme/plugin code outside public Git unless licensing explicitly permits this repository usage.

Recommended archival approach:

- Create a Git tag such as `legacy-full-wp-import-2026-06-13`.
- Create an `archive/legacy-import-notes.md` or external storage note describing where the full import can be found.
- Then rebuild `main` as a clean project, or create a new clean branch such as `rebuild/dev-inteligent`.

## 6. Recommended clean project structure

Recommended structure for a clean WordPress/WooCommerce dev project:

```text
.
├── README.md
├── AUDIT.md
├── .gitignore
├── .env.example
├── composer.json
├── composer.lock
├── docker-compose.yml
├── Makefile
├── config/
│   ├── nginx/
│   │   └── dev.inteligent.si.conf
│   ├── php/
│   │   └── php.ini
│   └── wordpress/
│       └── wp-config-env.php
├── deploy/
│   ├── README.md
│   ├── hetzner-bootstrap.sh
│   ├── deploy-dev.sh
│   └── systemd-or-cron-notes.md
├── scripts/
│   ├── bootstrap-local.sh
│   ├── wp-install.sh
│   ├── wp-plugin-sync.sh
│   └── search-replace-dev.sh
├── web/
│   ├── app/
│   │   ├── mu-plugins/
│   │   ├── plugins/
│   │   │   └── custom-site-plugin/
│   │   ├── themes/
│   │   │   └── zvijsi-child/
│   │   └── uploads/
│   │       └── .gitkeep
│   └── wp-config.php
└── docs/
    ├── environment.md
    ├── deployment.md
    └── migration.md
```

Preferred dependency model:

- Manage WordPress core with Composer, for example `johnpbloch/wordpress`, or use a Bedrock-style layout if desired.
- Manage public WordPress plugins with Composer/WPackagist where possible.
- Keep paid plugins/themes out of Git unless licensing allows it; document manual install or use a private package/artifact store.
- Keep `wp-content/uploads/` out of Git.
- Keep secrets in `.env`, never in Git.
- Use WP-CLI for reproducible install, plugin activation, permalink setup, WooCommerce setup steps, and domain search/replace.

Recommended dev environment:

- Docker Compose with:
  - PHP-FPM or Apache/PHP container
  - MariaDB
  - Nginx or Caddy reverse proxy
  - WP-CLI container
  - optional Mailpit for email testing
- Local domain can be `dev.inteligent.si` if DNS/hosts points to the dev server, or `dev.inteligent.local` for local-only work.

Recommended server layout on Hetzner:

```text
/var/www/dev.inteligent.si/
├── current -> releases/<timestamp>
├── releases/
├── shared/
│   ├── .env
│   └── uploads/
└── backups/
```

Deploy process:

- Build or install dependencies locally/CI.
- Upload release to Hetzner.
- Symlink shared `.env` and uploads.
- Run database migrations/plugin updates only against the dev database.
- Flip `current` symlink.
- Reload PHP/Nginx if needed.

## 7. Exact next steps to bootstrap dev.inteligent.si

No live `zvij.si` action should be performed. Treat `dev.inteligent.si` as a separate site, separate database, separate files, and separate credentials.

Recommended next steps:

1. Create a safety marker for the imported repo.
   - Tag current state, for example: `legacy-full-wp-import-2026-06-13`.
   - Do this before deleting or restructuring anything.

2. Decide the clean rebuild branch strategy.
   - Option A: create `rebuild/dev-inteligent` and clean it there.
   - Option B: reset `main` into a clean project after preserving the legacy tag.

3. Create the new clean project skeleton.
   - Add Composer or Bedrock-style WordPress structure.
   - Add Docker Compose for local dev.
   - Add `.env.example`.
   - Add `README.md` setup instructions.
   - Add `docs/deployment.md`.

4. Define separate dev environment values.
   - Site URL: `https://dev.inteligent.si`
   - Separate database name, user, password.
   - Separate WordPress salts.
   - `WP_ENV=development`
   - `WP_DEBUG=true`
   - Disable production payment capture unless intentionally testing sandbox mode.

5. Provision Hetzner dev host or vhost.
   - Create Linux user for the dev site.
   - Create `/var/www/dev.inteligent.si`.
   - Create a new MariaDB database dedicated to dev.
   - Configure Nginx/Apache vhost for `dev.inteligent.si`.
   - Install TLS certificate for `dev.inteligent.si`.
   - Ensure DNS points `dev.inteligent.si` to the dev server.

6. Bootstrap WordPress dev install.
   - Install WordPress via Composer or WP-CLI.
   - Generate `wp-config.php` from environment variables.
   - Run `wp core install` for `dev.inteligent.si`.
   - Install and activate required plugins.
   - Install theme/child theme.

7. Reinstall dependencies cleanly.
   - WooCommerce
   - Elementor
   - Contact Form 7 if still needed
   - Revolut gateway only in sandbox/test mode
   - Plamen/Qode/Slider Revolution only if rebuild will preserve the old theme

8. Import content safely.
   - Preferred: export/import from a sanitized live backup or WordPress XML/WooCommerce CSV.
   - Run search-replace from `zvij.si` to `dev.inteligent.si` on the dev database only.
   - Never point dev at the live database.
   - Never reuse live payment API keys in dev.

9. Add deployment automation.
   - `deploy/hetzner-bootstrap.sh` for one-time server setup notes or scripted provisioning.
   - `deploy/deploy-dev.sh` for rsync/release/symlink deployment.
   - Document rollback.
   - Add GitHub Actions only after the manual deploy path is proven.

10. Verify dev site.
    - Home page loads on `https://dev.inteligent.si`.
    - Admin login works.
    - WooCommerce pages exist.
    - Checkout uses sandbox/test payment settings.
    - Emails go to test sink or safe recipients.
    - No links point to production `zvij.si` unintentionally.

## Bottom line

This repository is a legacy full WordPress import, not a clean deployable dev project. It is useful as a reference for theme/plugin choices and possibly as a source for commercial theme/plugin files if licensing allows. It does not currently provide deploy automation for Hetzner or a safe separated environment for `dev.inteligent.si`.

The safest path is to preserve this import with a tag, then rebuild the repo around a clean WordPress project structure with Composer, Docker/WP-CLI, environment-specific config, and a dedicated Hetzner deployment flow for `dev.inteligent.si`.
