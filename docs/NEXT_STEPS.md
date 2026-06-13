# Next Steps

## Current State

- Repository contains a legacy WordPress import.
- `AUDIT.md` documents the repo state.
- Live `zvij.si` must not be touched.
- Development target is `dev.inteligent.si`.
- This phase is strategy and documentation only.

## Immediate Next Steps

1. Review the blueprint documents.
2. Confirm the product hierarchy:
   - DUBI filters
   - DUBI + rolca setup
   - CBD čaji
   - Refill packages
   - Member packages
3. Confirm membership mechanics:
   - QR in package
   - Član Zvij.si onboarding
   - Zvij koda
   - friend benefit
   - dobroimetje
   - refill reminders
4. Confirm CBD copy rules before product copy is written.
5. Decide whether the rebuild keeps Plamen/Elementor or moves to a cleaner custom theme.

## Content Work Before Infrastructure

Create draft copy for:

- Home hero
- package category pages
- DUBI filter product pages
- CBD tea product pages
- refill landing page
- membership onboarding page
- QR landing page
- account membership panel copy
- first refill reminder email
- referral/dobroimetje email

Each CBD page must follow `LEGAL_CBD_COPY_RULES.md`.

## Product Data Needed

Collect:

- product names
- product photos
- package contents
- pricing
- refill intervals
- member package rules
- shipping rules
- payment provider decision
- CBD certificate/lab documentation if available

## Membership Decisions Needed

Decide:

- how much dobroimetje each side receives
- whether dobroimetje expires
- whether codes are single-use per friend
- whether existing customers can become members
- how QR onboarding verifies purchase
- whether guest checkout can later become membership
- how refill reminders are scheduled

## Infrastructure Comes After This

Do not create infrastructure until the above product and membership decisions are clear.

Later infrastructure tasks:

- create clean project structure
- decide Composer/Bedrock vs standard WordPress
- define Docker local dev
- provision `dev.inteligent.si`
- create separate dev database
- create separate dev secrets
- install WordPress cleanly
- install required plugins cleanly
- import sanitized content only
- add deployment automation

## Hard Boundaries

Do not:

- touch live `zvij.si`
- reuse production credentials
- point dev at the live database
- implement Docker in this documentation branch
- create deployment scripts in this documentation branch
- modify legacy WordPress files in this phase
- write CBD smoking claims
- write medical CBD claims
- frame membership as resale

## Recommended Next Branch After Approval

After this blueprint is approved, create a separate branch for content architecture, for example:

```text
docs/zvij-content-v1
```

That branch should turn the blueprint into page-level copy blocks and product copy templates before any infrastructure work begins.
