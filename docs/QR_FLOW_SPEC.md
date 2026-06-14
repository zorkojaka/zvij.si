# QR Landing Flow Spec

## Purpose

The QR flow turns a delivered package into membership onboarding. It should be simple, calm, and tied to the buyer's setup.

## Core Flow

1. Package includes QR code.
2. User scans QR.
3. QR opens a landing page on Zvij.si.
4. Page explains `Član Zvij.si`, `Zvij koda`, and dobroimetje.
5. User logs in or creates account.
6. User confirms purchase/setup if required.
7. User becomes `Član Zvij.si`.
8. User sees next-step actions:
   - copy Zvij koda
   - view refill recommendation
   - repeat last order later

## QR URL Shape

Candidate dev paths:

```text
/qr/
/qr/{batch}
/qr/{batch}/{token}
```

Production token design is not approved yet. Do not encode secrets or private customer data in a QR URL.

## Verification Options

Possible approaches:

- generic QR route with no purchase verification
- batch code route for package/source analytics
- single-use token tied to an order
- order lookup after login

## Guardrails

- QR should not expose private order data.
- QR copy must not imply resale or cash payout.
- CBD copy must remain tea-only if package includes CBD products.
- Token/verification design waits for privacy and commercial decisions.

## Open Decisions

- Whether QR verifies purchase.
- Whether QR supports guest checkout conversion.
- Whether QR differs by package type.
- Whether QR routes directly to account creation or an explanatory landing page first.
- What happens if a QR is scanned by someone who did not purchase.

## MVP Recommendation

Start with a generic `/qr/` landing page and no sensitive token. Add tokenized QR only after membership mechanics and privacy handling are approved.
