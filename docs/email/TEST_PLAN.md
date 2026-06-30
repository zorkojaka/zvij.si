# Email Membership Test Plan

Use dev only.

Use a test email address, not a real customer.

## Preflight

- Confirm no API keys appear in Git diff.
- Confirm `MAILERLITE_API_KEY` and `MAILERLITE_GROUP_ID` are either configured or intentionally missing on dev.
- Confirm WooCommerce is active.

## Tests

1. Submit homepage form with a new test email and checked consent.
2. Confirm success message appears.
3. Confirm local `wp_zvij_members` row exists.
4. Confirm consent timestamp, source, and privacy version are stored.
5. Confirm a unique `ZVIJ-XXXXXX` WooCommerce coupon exists.
6. Confirm duplicate signup returns the same public success behavior.
7. Confirm honeypot submission fails.
8. Confirm unchecked consent cannot submit.
9. Confirm MailerLite provider status:
   - `not_configured` before credentials,
   - `synced` after valid credentials.
10. Confirm welcome email arrives through WordPress mail or configured SMTP.
11. Confirm coupon applies in cart for the subscriber email.
12. Confirm coupon cannot be reused after permitted use.
13. Confirm expired coupon fails.
14. Confirm checkout opt-in is optional and unchecked by default.
15. Confirm an order without marketing consent still sends transactional WooCommerce emails.
16. Confirm order from a consenting email updates `customer_status = customer`.
17. Confirm mobile form is usable.
18. Confirm failed provider connection does not break public frontend.
19. Confirm no API keys appear in HTML, logs, docs, or Git.

## Current Dev Result

Before MailerLite credentials, provider sync is expected to be `not_configured`. This is not a frontend failure.

