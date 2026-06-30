# Zvij.si Blueprint

## Locked decisions — homepage v1 (2026-06-18)

These supersede any conflicting older notes below.

- Site spine is **izdelek-first**: the two hero products are **DUBI filtri** and
  **CBD/CBG vršički**. Kits are bundled convenience; membership drives repeat purchase.
- Single primary line: **`Tvoj ritual. Tvoja mera. Tvoj setup.`** The kit slogan
  `Vse, kar rabiš, da si zviješ.` is used only inside the Kit visual/section.
- Naming: use **`CBD/CBG vršički`** everywhere. Do not use `CBD čaj` as a product name.
- **Zvij.si Kit = one setup in three colours** (Black / Silver / Gold) — colour/style only,
  not different meanings. **Throwie** is a separate, simpler utility kit.
- Homepage section order: hero → DUBI + CBD/CBG → Zvij.si Kit (colour selector) →
  Throwie → Reload → Član Zvij.si teaser → FRUTTY 4,20 € → zaključni CTA.
- Member mechanics (QR, Zvij koda, dobroimetje) are shown only once implemented; until
  then the homepage member block is a `v pripravi` teaser linking to the info page.

## Purpose

Zvij.si is not just a webshop. It is a membership system for people with their own ritual, setup, rhythm, and culture.

The shop sells products, but the brand builds a repeatable system:

```text
first purchase
-> QR code in package
-> become Član Zvij.si
-> get Zvij koda
-> invite friend
-> both get dobroimetje
-> repeat order
-> reload reminder
```

The product is the entry point. Membership is the loop.

## Core Positioning

Primary line:

```text
Tvoj ritual. Tvoja mera. Tvoj setup.
```

Meaning:

- Tvoj ritual: the user already has a personal routine; Zvij.si respects it.
- Tvoja mera: the user decides what works for them.
- Tvoj setup: the product mix supports the user's preferred way of preparing and using their ritual.

The brand does not tell users who they are. It gives them better tools, better reloads, and a cleaner system around what they already do.

## Brand Voice

Zvij.si should sound:

- adult
- calm
- direct
- non-corporate
- from within the culture
- practical
- human

Zvij.si should not sound:

- medical
- moralizing
- fake-edgy
- corporate wellness
- youth-coded
- like a headshop caricature
- like a resale scheme

The brand does not moralize. It does not present cannabis users as broken, wrong, irresponsible, or in need of fixing. It also does not provoke for the sake of provocation.

The tone should show by example:

- cleaner setup
- smarter reloads
- calmer choices
- more control
- better ritual

## Product System

Hero products:

1. DUBI filters
2. DUBI + rolca setup
3. CBD vršički sold as CBD tea
4. Reload packages
5. Member packages

Product roles:

- DUBI filters: the signature utility product.
- DUBI + rolca setup: the starter setup for a cleaner first purchase.
- CBD tea: the calm, controlled ritual option.
- Reload packages: the repeat-order engine.
- Member packages: the membership value layer.

## Membership System

Language:

- Use `Član Zvij.si`.
- Use `Zvij koda`.
- Use `dobroimetje`.

Avoid:

- dealer program
- MLM
- preprodaja
- reseller language
- anything implying resale, brokerage, or cash payout

Membership promise:

```text
Postaneš Član Zvij.si, dobiš svojo Zvij kodo in zbiranje dobroimetja postane del tvojega rituala.
```

Membership should feel like a practical insider layer, not a hype club.

## Commerce Loop

The first purchase should lead into a membership loop:

1. User buys a package.
2. Package includes a QR code.
3. QR opens a membership onboarding page.
4. User becomes `Član Zvij.si`.
5. User receives a `Zvij koda`.
6. User shares the code with a friend.
7. Friend receives a first-order benefit.
8. Original member receives `dobroimetje`.
9. User receives reload reminders based on the purchased setup.
10. User repeats with reload or member package.

This loop must stay simple. The user should understand it in one pass.

## CBD Positioning

CBD is sold and communicated as tea / dried hemp flower for tea.

Allowed direction:

- CBD čaj
- posušeni konopljini vršički za čaj
- ritual z manj THC
- bolj jasna glava
- več kontrole nad ritualom
- mirnejši večerni setup

Not allowed:

- saying CBD is for smoking
- medical claims
- treatment claims
- disease claims
- cure language
- guaranteed effects
- intoxication promises

Preferred natural copy style:

```text
CBD čaj je za tiste dni, ko želiš ritual ohraniti, ampak ga narediti mirnejšega in bolj pod kontrolo.
```

## Site Role

The site must do three jobs:

1. Sell the first package.
2. Explain the setup clearly.
3. Move buyers into membership and reload behavior.

The homepage should not feel like a generic webshop grid. It should introduce the ritual/setup idea, then route users into packages, DUBI filters, CBD teas, reloads, and membership.

## Strategic Guardrails

Do:

- speak to adults
- keep wording calm and direct
- frame products around ritual, setup, reload, and control
- make membership concrete
- use CBD tea language consistently
- make repeat purchase easy

Do not:

- touch live `zvij.si`
- blur dev and production credentials
- frame cannabis users as damaged
- imply CBD is for smoking
- imply medical benefit
- imply members are resellers
- build infrastructure before the product system is clear
# Član Zvij.si Email Membership

Membership name: `Član Zvij.si`.

Signup CTA: `Postani član`.

Email content series: `Zvijače za zvijače`.

V1 lives in `wp-content/plugins/zvij-core/zvij-core.php` and provides:

- reusable membership signup form,
- consent storage,
- unique first-order WooCommerce coupon generation,
- welcome email delivery through WordPress mail,
- MailerLite-ready provider sync,
- optional checkout marketing opt-in,
- admin status and test-signup page.

Provider setup and tests are documented in:

- `docs/email/EMAIL_SYSTEM_AUDIT.md`
- `docs/email/MAILERLITE_SETUP.md`
- `docs/email/MEMBERSHIP_FLOW.md`
- `docs/email/AUTOMATION_ROADMAP.md`
- `docs/email/TEST_PLAN.md`
