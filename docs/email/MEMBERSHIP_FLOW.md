# Membership Flow

Membership name:

```text
Član Zvij.si
```

Email content series:

```text
Zvijače za zvijače
```

Signup CTA:

```text
Postani član
```

## Public Flow

1. Visitor sees `Postani član Zvij.si`.
2. Visitor enters optional name and required email.
3. Visitor checks explicit marketing consent.
4. Form submits to WordPress.
5. System stores consent timestamp, source, privacy-policy version, and IP hash.
6. System creates or reuses a unique first-order coupon.
7. System sends welcome email.
8. If MailerLite is configured, subscriber is synced to `Člani Zvij.si`.

## Consent

Checkbox copy:

```text
Strinjam se, da mi Zvij.si pošilja novice, ponudbe in vsebine po e-pošti. Odjavim se lahko kadarkoli.
```

The checkbox is never preselected.

Checkout marketing consent is separate from transactional WooCommerce emails.

## Duplicate Handling

Duplicate signup does not reveal whether an arbitrary email already exists.

Existing rows are updated with the latest source/name when appropriate and retain the existing first-order coupon.

## Coupon

V1 uses unique coupons.

Rules:

- 10% discount
- 30-day expiry
- individual use only
- usage limit: 1
- usage limit per user: 1
- bound to subscriber email
- no free shipping

## Data Storage

Local table:

```text
wp_zvij_members
```

Fields include:

- email
- name
- status
- source
- customer_status
- consent_at
- consent_ip_hash
- privacy_version
- first_order_coupon
- provider_status
- last_order_date

## Unsubscribe And Privacy

V1 relies on MailerLite unsubscribe links after provider setup.

Manual process until full provider webhook support is added:

1. Search the local member table by email.
2. Set status to unsubscribed if requested.
3. Remove/unsubscribe the same email in MailerLite.
4. Preserve legally required WooCommerce order data separately.

