# Repeat Order And Reload Spec

## Purpose

Repeat order and reload logic should make the next purchase easier without over-automating before product quantities, prices, and legal checkout rules are known.

## Core Flows

### Repeat Last Order

1. Member opens `Moj račun`.
2. Member sees previous setup/order.
3. Member clicks repeat.
4. Store rebuilds a cart from eligible previous items.
5. Member can adjust quantities.
6. Member applies available dobroimetje.
7. Member checks out.

### Reload Reminder

1. User buys DUBI filters, setup, or package.
2. System records product type and expected reload interval.
3. User receives a reminder.
4. Reminder routes to `Reload` or relevant product/category.
5. User can reorder without searching from scratch.

## Future Data Model

- user ID
- source order ID
- product/setup type
- reload interval
- next reminder date
- reminder status: `pending`, `sent`, `paused`, `completed`
- last repeated order ID

## Product Inputs Needed

- package contents
- expected reload interval per package
- reload SKU/product mapping
- pricing
- whether subscriptions are in scope later
- email/SMS consent requirements

## Guardrails

- Do not create production reminders without consent rules.
- Do not configure payment gateways yet.
- Do not configure production shipping yet.
- CBD/CBG copy should use `vršički` as the main product identity. Čajna uporaba can be mentioned as one possible use.
- Do not use `refill` as the main public term or imply returned packaging gets filled again.

## MVP Recommendation

For dev:

- create `Reload` product category
- create draft placeholder products
- write account-area copy
- avoid automated reminder sending until product and consent rules are approved
