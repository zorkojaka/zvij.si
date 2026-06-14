# Repeat Order And Refill Spec

## Purpose

Repeat order and refill logic should make the next purchase easier without over-automating before product quantities, prices, and legal checkout rules are known.

## Core Flows

### Repeat Last Order

1. Member opens `Moj račun`.
2. Member sees previous setup/order.
3. Member clicks repeat.
4. Store rebuilds a cart from eligible previous items.
5. Member can adjust quantities.
6. Member applies available dobroimetje.
7. Member checks out.

### Refill Reminder

1. User buys DUBI filters, setup, or package.
2. System records product type and expected refill interval.
3. User receives a reminder.
4. Reminder routes to `Refill` or relevant product/category.
5. User can reorder without searching from scratch.

## Future Data Model

- user ID
- source order ID
- product/setup type
- refill interval
- next reminder date
- reminder status: `pending`, `sent`, `paused`, `completed`
- last repeated order ID

## Product Inputs Needed

- package contents
- expected refill interval per package
- refill SKU/product mapping
- pricing
- whether subscriptions are in scope later
- email/SMS consent requirements

## Guardrails

- Do not create production reminders without consent rules.
- Do not configure payment gateways yet.
- Do not configure production shipping yet.
- Keep CBD copy as tea-only and without medical claims.

## MVP Recommendation

For dev:

- create `Refill` product category
- create draft placeholder products
- write account-area copy
- avoid automated reminder sending until product and consent rules are approved
