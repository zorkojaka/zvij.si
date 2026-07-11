# Zvij Koda Referral Spec

Status: **implementirano 11. 7. 2026 (zvij-core 0.7.0)** — `includes/zvij-referral.php`. Odločene vrednosti: prijatelj **10 % na izdelke prvega naročila** (opcija `zvij_referral_friend_percent`), lastnik **30 kristalov** ob plačilu prijateljevega naročila (opcija `zvij_referral_owner_kristali`). Koda `ZK-XXXXXX` (stolpec `zvij_code` v members tabeli, v Moj račun + welcome emailu). Zaščite: samo-priporočanje zavrnjeno, samo prvo plačano naročilo kupca, nagrada šele ob plačilu, storno ob preklicu. Ista koda gostu omogoča unovčenje lastnih kristalov (pol-skrivnost namesto prijave).

## Purpose

`Zvij koda` lets a `Član Zvij.si` invite a friend while keeping the system framed as dobroimetje for future reloads, not resale or commission.

## Core Flow

1. Member opens `Moj račun`.
2. Member copies their Zvij koda.
3. Friend enters the code at checkout or arrives through a code link.
4. Friend receives a first-order benefit.
5. Original member receives dobroimetje after the qualifying order is valid.
6. Both users can see the result in their account area.

## Language Rules

Allowed:

- `povabi prijatelja`
- `Zvij koda`
- `dobroimetje`
- `prijatelj dobi ugodnost`
- `ti dobiš dobroimetje`

Not allowed:

- dealer
- reseller
- preprodaja
- MLM
- commission
- cash payout
- passive income

## Future Data Model

- code
- owner user ID
- referred user/order ID
- status: `issued`, `used`, `qualified`, `credited`, `rejected`
- benefit type
- dobroimetje amount
- timestamps

## Guardrails

- Dobroimetje is not withdrawable cash.
- A friend benefit should not imply medical, CBD effect, or illegal-use claims.
- Abuse checks are required before production: self-referral, duplicate account, cancelled order, refunded order.

## Open Decisions

- Benefit amount for friend.
- Dobroimetje amount for member.
- Whether code is single-use per friend.
- Whether benefit applies before or after order validation.
- Refund/cancellation handling.

## MVP Recommendation

Do not implement custom referral accounting yet. Start with:

- account copy
- code placeholder field
- admin-visible spec
- draft checkout rules after legal/commercial approval
