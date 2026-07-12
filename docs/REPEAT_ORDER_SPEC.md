# Repeat Order And Reload Spec

Status: **»Ponovi naročilo« implementirano 12. 7. 2026 (zvij-core 0.7.1)** — `includes/zvij-repeat-order.php`: Woo »order again« razširjen na processing/pripravljeno/completed, gumb v Moj račun → Naročila in blok »Reload zadnjega naročila« na dashboardu (E2E: klik obnovi košarico s postavkami).

**Reload OPOMNIKI implementirani 12. 7. 2026 (zvij-core 0.8.0)** — `includes/zvij-reload-reminder.php`, a **dormant do aktivacije**: interval določa polje »Reload opomnik (dni)« na izdelku (admin → Splošno, meta `_zvij_reload_days`, variacija podeduje od starša); dokler intervali niso vpisani, se ne pošlje nič — aktivacija je Jakova odločitev, skladno z guardraili spodaj. Mehanika: ob plačanem naročilu ČLANA s privolitvijo (`zvij_members.status = subscribed`) se razporedi opomnik (max interval med postavkami, order meta `_zvij_reload_status`/`_zvij_reload_due`); novo plačano naročilo istega člana starejše čakajoče opomnike zaključi (max en pending na člana); dnevni cron (`zvij_reload_reminder_daily`) pošlje zapadle (plain-text email z linkom na Moj račun → »Ponovi naročilo«, stanjem kristalov in odjavnim linkom), pred pošiljanjem ponovno preveri privolitev. Odjava: lokalni HMAC endpoint (`admin-post.php?action=zvij_member_unsubscribe`) → status `unsubscribed`. Dashboard KPI »Reload opomniki« (čakajoči), zadnji poslani email viden v opciji `zvij_reload_last_reminder`. E2E 12. 7.: razpored/rok, pošiljanje po zapadlosti, nadomestitev z novim nakupom, preklic naročila, odjava (HTTP 200/400) in skip odjavljenega člana — vse potrjeno, testni podatki počiščeni.

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
