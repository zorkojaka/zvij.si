# Zvij.si User Flows

## Flow 1: First Purchase To Membership

Goal:

- Turn a first-time buyer into `Član Zvij.si`.

Steps:

1. User lands on Home.
2. User sees `Tvoj ritual. Tvoja mera. Tvoj setup.`
3. User chooses a starter package or DUBI + rolca setup.
4. User completes checkout.
5. Package arrives with QR code.
6. User scans QR code.
7. QR opens membership onboarding.
8. User creates or confirms account.
9. User becomes `Član Zvij.si`.
10. User receives `Zvij koda`.
11. User sees how `dobroimetje` works.

Success state:

- User has account, membership status, and Zvij koda.

## Flow 2: Referral With Zvij Koda

Goal:

- Let a member invite a friend without implying resale.

Steps:

1. Member opens `Moj račun`.
2. Member copies `Zvij koda`.
3. Member sends code to a friend.
4. Friend lands on a product or package page.
5. Friend applies Zvij koda at checkout.
6. Friend receives first-order benefit.
7. Member receives `dobroimetje`.
8. Both users can see the result in account area.

Language rules:

- Say `povabi prijatelja`.
- Say `dobroimetje`.
- Say `Zvij koda`.
- Do not say dealer, reseller, MLM, preprodaja, or cash payout.

Success state:

- Friend makes a first purchase.
- Member receives dobroimetje.
- No part of the flow sounds like resale.

## Flow 3: Refill Reminder

Goal:

- Make repeat orders easy and timely.

Steps:

1. User buys DUBI filters, setup, or package.
2. System records product type and expected refill interval.
3. User receives refill reminder.
4. Reminder routes to `Refill`.
5. User sees relevant refill package.
6. User applies available dobroimetje.
7. User completes repeat order.

Success state:

- User repeats purchase without searching from scratch.

## Flow 4: CBD Tea Purchase

Goal:

- Sell CBD vršički as CBD tea with natural, compliant positioning.

Steps:

1. User enters `CBD čaji`.
2. Page frames products as dried hemp flower for tea.
3. User reads calm ritual copy.
4. User sees no smoking claims and no medical claims.
5. User chooses product.
6. User sees tea preparation guidance.
7. User checks out.
8. User is invited into membership after purchase.

Allowed motivation language:

- less THC
- clearer head
- more control
- calmer ritual

Success state:

- User understands CBD product as tea and completes purchase.

## Flow 5: Returning Member Reorder

Goal:

- Make the account area useful, not just administrative.

Steps:

1. Member logs into `Moj račun`.
2. Member sees previous setup.
3. Member sees available dobroimetje.
4. Member sees refill recommendation.
5. Member reorders or chooses a member package.
6. Member can share Zvij koda again.

Success state:

- Account area becomes the user's ritual/refill hub.

## Flow 6: Guide To Product

Goal:

- Educate without moralizing and route to product.

Steps:

1. User opens `Vodiči`.
2. User reads a practical guide.
3. Guide explains setup, measure, refill, or CBD tea clearly.
4. Guide links to relevant package or category.
5. User chooses product or saves guide.

Guide tone:

- calm
- useful
- direct
- non-judgmental

Success state:

- User feels informed, not corrected.

## Flow 7: Admin Content Review

Goal:

- Keep CBD and membership copy aligned before publishing.

Steps:

1. New page/product/guide copy is drafted.
2. Copy is checked against `LEGAL_CBD_COPY_RULES.md`.
3. CBD wording is checked for tea-only language.
4. Membership wording is checked for no resale/cash-payout framing.
5. Copy is approved for dev.
6. Copy is later reviewed before production.

Success state:

- Dev content can move forward without creating avoidable compliance or brand problems.
