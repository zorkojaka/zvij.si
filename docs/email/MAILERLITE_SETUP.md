# MailerLite Setup

Provider preference: MailerLite.

Do not commit credentials to Git.

## 1. Create MailerLite Account

Create or use the official Zvij.si MailerLite account.

## 2. Authenticate Sending Domain

Authenticate the sending domain used for marketing email.

Recommended sender identity:

- From name: `Zvij.si`
- From email: `podpora@zvij.si`

Complete MailerLite DNS authentication before sending real campaigns.

## 3. Create Subscriber Group

Create one group:

```text
Člani Zvij.si
```

This is the membership marketing group.

Do not create a separate group named `Zvijače za zvijače`; that is the email content series identity.

## 4. Create API Token

Create a MailerLite API token with subscriber/group write access.

## 5. Enter Credentials Safely

Preferred: environment variables in the protected dev/prod runtime.

```text
MAILERLITE_API_KEY
MAILERLITE_GROUP_ID
MAILERLITE_WEBHOOK_SECRET
```

Alternative protected WordPress options:

```text
zvij_mailerlite_api_key
zvij_mailerlite_group_id
zvij_mailerlite_webhook_secret
```

Never paste these values into docs, Git, templates, public HTML, or screenshots.

## 6. Test Subscriber

In WordPress admin, open:

```text
Settings -> Član Zvij.si Email
```

Use `Send test signup` with a test email address only.

Expected result:

- Local subscriber row is created or updated.
- Unique WooCommerce coupon is created.
- Welcome email is attempted through WordPress mail.
- MailerLite provider status becomes `synced`.

## 7. Check Delivery

Verify:

- MailerLite subscriber exists in `Člani Zvij.si`.
- Custom fields are populated where configured:
  - `source`
  - `signup_date`
  - `customer_status`
  - `first_order_coupon`
- Welcome email arrives.
- Spam/promotions placement is acceptable.
- Unsubscribe link is present in provider-sent campaigns.

## Required Custom Fields

Create these fields in MailerLite if they do not exist:

```text
source
signup_date
customer_status
first_order_coupon
preferred_products
last_order_date
```

