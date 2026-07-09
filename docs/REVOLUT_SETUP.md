# Revolut kartično plačilo (ZVIJ-01)

Uradni vtičnik **Revolut Gateway for WooCommerce** je nameščen in aktiviran na
dev okolju (provisionira ga `scripts/wp-install-dev.sh`). Doda štiri plačilne
metode: kartice (`revolut_cc`), Revolut Pay, Apple/Google Pay (`revolut_payment_request`)
in Pay by Bank. Način je nastavljen na **sandbox**.

Brez API ključa metode na blagajni **niso vidne** (gateway ni `available`), zato
dev checkout deluje normalno naprej (nakazilo/predračun in po povzetju).

## Kaj mora narediti Jaka

### 1. Sandbox test
1. Odpri **Revolut Business → Merchant API** (sandbox okolje:
   <https://sandbox-business.revolut.com>) in ustvari **Sandbox API secret key**.
   Navodila: <https://developer.revolut.com/docs/accept-payments/get-started/generate-the-api-key>
2. V WP adminu: **WooCommerce → Nastavitve → Plačila → Revolut** →
   *Select Mode* = **Sandbox** → prilepi ključ v **Sandbox API secret key** →
   **Shrani**. Vtičnik samodejno nastavi webhook.
3. Naredi testni nakup s testno kartico (Revolut sandbox testne kartice) in
   preveri, da naročilo preide v *processing* ter da račun (ZVIJ-08) prispe.

### 2. Produkcija (po uspešnem sandbox testu)
1. V **Revolut Business (live) → Merchant API** ustvari **Production API secret key**.
2. Ista stran: *Select Mode* = **Live** → vpiši produkcijski ključ → shrani.
3. En pravi nakup z minimalnim zneskom, da potrdiš, da denar prispe na TRR/Revolut.

## Opombe
- Ključi se hranijo v bazi (WC nastavitve), **ne v Gitu** — varno.
- Če želiš, izklopi metode, ki jih ne rabiš (npr. Pay by Bank), na isti strani.
- Podprte valute: trgovina je v EUR, kar Revolut podpira.
