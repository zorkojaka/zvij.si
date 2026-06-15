# Član Zvij.si Membership Spec

## Purpose

`Član Zvij.si` is the account layer that turns a first purchase into a repeatable ritual, reload, and dobroimetje loop. It should feel practical and internal to Zvij.si, not like a resale program or hype club.

Source of truth:

- `docs/ZVIJ_BLUEPRINT.md`
- `docs/USER_FLOWS.md`
- `docs/LEGAL_CBD_COPY_RULES.md`

## Positioning

Primary promise:

```text
Postaneš Član Zvij.si, dobiš svojo Zvij koda in zbiranje dobroimetja postane del tvojega rituala.
```

Main name:

```text
Član Zvij.si
```

Supporting line:

```text
Zvijače za zvijače.
```

Use the supporting line as a clever inside-culture phrase for member perks, dobroimetje and reload habits. Do not replace the main membership name with it.

Use:

- `Član Zvij.si`
- `Zvijače za zvijače.`
- `Zvij koda`
- `dobroimetje`
- `povabi prijatelja`
- `reload`

Avoid:

- dealer program
- MLM
- reseller / preprodaja framing
- cash payout language
- passive income language

## Entry Points

Membership can start from:

1. QR code in a delivered package.
2. Account area after first purchase.
3. Dedicated `Član Zvij.si` page.
4. Post-checkout prompt after eligible purchase.

## Member State

Minimum future data model:

- WordPress user ID
- membership status: `none`, `pending`, `active`
- Zvij koda
- dobroimetje balance
- source order ID
- setup/product preference
- reload reminder preference
- created date

## MVP Behavior

For dev baseline:

1. User opens membership onboarding.
2. User logs in or creates account.
3. System marks user as `Član Zvij.si`.
4. System assigns a unique Zvij koda.
5. Account area explains dobroimetje and reload loop.

No cash payouts. Dobroimetje is store credit only.

## Current Dev Dobroimetje Copy

Product cards and product pages may show:

```text
Član prejme X € za naslednji reload.
```

Current dev values are documented in `docs/DOBROIMETJE_STRATEGY.md`.

The member story should stay simple:

- first purchase = low-friction try
- next orders = dobroimetje
- goal = reload rhythm, not action hunting
- no MLM, no reselling, no payout promise

## Open Decisions

- Whether current dobroimetje values are final.
- Whether dobroimetje expires.
- Whether every customer can become a member.
- Whether QR code proves purchase or only routes to onboarding.
- Whether guest checkout can later attach to membership.
- How membership is shown in `Moj račun`.

## Acceptance Criteria

- Membership copy uses approved terms.
- No resale, dealer, MLM, or income framing appears.
- User can understand the loop in one pass.
- Production implementation waits for dobroimetje and legal checkout decisions.
