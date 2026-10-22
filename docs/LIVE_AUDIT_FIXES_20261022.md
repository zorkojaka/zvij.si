# Public site audit corrections — 22 October 2026

Applied directly to the current production WordPress instance at Jaka's request. The theme and core plugin directories are shared bind mounts with development; content changes were applied only to the production database.

- Return-policy link in FAQ corrected; former /vracila-in-reklamacije/ URL now redirects permanently to /refund_returns/.
- Nonconformity wording corrected from one year to two years.
- Generic page template renders an intro only when an explicit excerpt exists. Three legal pages now have short editorial introductions instead of duplicated automatic excerpts.
- FAQ and origin story now describe the invitation-link / QR flow and 10% inviter bonus confirmed in zvij-invitations.php. Reward logic was not changed.
- Black grinder description updated with the owner's confirmed four parts, included scoop and removable mesh section for a three-part configuration. Material and dimensions were not invented.
- JollySafe no longer displays the weight row. Shipping/product weight data was not changed.
- Privacy policy now describes self-hosted Plausible CE, cookie-free analytics, account membership separately from optional newsletter consent, and the relevant purchase events.
- Cookie UI is informational with a single Razumem button. Prior all/necessary acknowledgement cookies remain understood; window.zvijConsent.analytics stays false so this acknowledgement does not authorize future nonessential trackers.
- Removed the explicit order-number property from future Plausible purchase events. Purchase amount/currency tracking remains. Existing historical analytics was not deleted. This is not a full audit of historical data, web server logs or all URL/referrer processing.

Backup before mutation: /home/jaka/backups/zvij-public-fixes-20261022 (three original PHP files and six public WordPress post records). Reviewed input and migration: /tmp/zvij-audit-fixes-20261022. No orders, users, prices, stock or reward balances were modified.

Validation: PHP lint on all three changed PHP files; git diff --check; public HTTP assertions for six affected routes; old returns redirect; visual browser verification of policy intro and cookie notice. Checkout, actual registration and email delivery were not exercised.

Sources: https://www.gov.si/zbirke/storitve/prijava-krsitve-trznemu-inspektoratu/ ; https://plausible.io/privacy-focused-web-analytics ; https://www.ip-rs.si/?id=156
