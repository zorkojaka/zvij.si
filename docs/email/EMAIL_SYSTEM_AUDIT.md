# Email System Audit

Date: 2026-06-30
Environment: dev only (`/home/jaka/apps/zvijsi/zvij.si`)

## Summary

No reusable email marketing provider integration was found on dev.

Active plugins:

- WooCommerce
- Zvij Core

Inactive plugins:

- Akismet
- Hello Dolly

Active theme:

- `zvij-theme`

## Provider Audit

Checked for MailerLite, Mailchimp, Brevo/Sendinblue, MailPoet, Newsletter, FluentCRM, Klaviyo, Omnisend, HubSpot, Contact Form 7, WPForms, Elementor Forms, and SMTP.

Findings:

- No active provider plugin exists.
- No inactive provider plugin exists in the plugin list.
- No relevant provider options were found in `wp_options`.
- No custom provider integration was found in `zvij-theme` or `zvij-core`.
- No signup form was found in the custom theme/plugin before this implementation.
- WooCommerce language files and bundled marketplace references mention Mailchimp/MailPoet/Klaviyo, but those are not active integrations.

## Forms And Widgets

Before implementation:

- Homepage had a `Član Zvij.si` visual section with a CTA.
- Footer had no email form.
- Checkout had no separate marketing opt-in.
- No Elementor, WPForms, Contact Form 7, or page-builder forms were found.

## Coupons

WooCommerce coupon list was empty on dev.

Current V1 behavior after implementation:

- Unique first-order coupons are generated per subscriber.
- Code pattern: `ZVIJ-XXXXXX`
- Discount: 10%
- Individual use: yes
- Usage limit: 1
- Usage limit per user: 1
- Expiry: 30 days
- Restricted to subscriber email where WooCommerce supports it.

## SMTP And Transactional Email

No SMTP plugin was found.

Relevant WordPress/WooCommerce email settings found:

- Admin email: `jaka@zvij.si`
- WooCommerce from address: `podpora@zvij.si`
- WooCommerce from name option was not present.

WooCommerce transactional emails are not changed by the membership implementation.

## Cron And Webhooks

No email marketing cron jobs or webhooks were found.

Visible cron jobs are default WordPress/WooCommerce jobs such as Action Scheduler, update checks, privacy cleanup, and WooCommerce admin tasks.

## Reusability Decision

There is no existing production/dev provider integration in this checkout that can be reused. The implementation therefore prepares a MailerLite-first integration without committing credentials.

## Test Signup Status

Automated provider delivery cannot be fully verified until Jaka adds MailerLite credentials and sending-domain authentication.

The dev implementation stores consent locally, generates coupons locally, and sends a local WordPress welcome email. Provider sync status is stored per subscriber as `not_configured`, `synced`, or `failed`.

