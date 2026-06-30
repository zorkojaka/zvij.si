# Automation Roadmap

## V1 Implemented

- Signup
- Consent storage
- Unique first-order coupon generation
- Welcome email via WordPress mail
- MailerLite subscriber sync structure
- Admin status page
- Admin test signup
- Checkout marketing opt-in
- WooCommerce customer status update after order

## V1 External Requirements

- MailerLite credentials
- MailerLite group
- MailerLite custom fields
- Authenticated sending domain
- Provider-side unsubscribe link in campaign/welcome templates

## Future Hooks

Prepare automations around:

```text
welcome
first_purchase
post_purchase
repeat_purchase
cart_abandoned
product_back_in_stock
```

## Later

- Abandoned cart
- Post-purchase follow-up
- Product-based repeat-purchase reminders
- Dobroimetje
- Segmentation
- Provider unsubscribe webhook
- Provider delete/export workflow

Reload reminders must depend on real purchase history and confirmed consent. Do not send fixed reminders before business rules are confirmed.

