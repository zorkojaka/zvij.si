# Dobroimetje Strategy

Status: **implementirano 11. 7. 2026 (zvij-core 0.7.0) kot KRISTALI** — `includes/zvij-credit.php` + `includes/zvij-referral.php`.

> **Posodobitev (Jaka, 4. 9. 2026): menjava je odslej 100 kristalov = 1 €** — en kristal je natanko en cent, številke pa so bolj igrive. Vse vrednosti na izdelkih so bile pomnožene z 10, da evrska vrednost ostane ista (DUBI 42: 13 → 130, DUBI 420: 75 → 750, vršički 8/10 → 80/100), nagrada za Zvij kodo 30 → 300. Ziggi izdelki so dobili svoje vrednosti (10 / 12 / 15 / 20 kristalov, ~5 % nazaj). Prehod je izvedel `scripts/wp-migrate-kristali-100.php` — skripta se zavaruje pred ponovnim zagonom (opcija `zvij_kristali_migrated_100`) in zavrne izvedbo, če ledger ni prazen. Ob prehodu odstranjena meta `_zvij_dobroimetje_note`: isti podatek na dveh mestih se je razšel (sistem je pripisoval 130, na strani je pisalo 13), zato se javni napis odslej izpelje iz vrednosti prek `zvij_credit_public_note()`.
>
> **Prvotna odločitev (Jaka, 11. 7. 2026):** valuta so kristali, menjava **10 kristalov = 1 €**. Kristali na izdelku/variaciji so v meta `_zvij_kristali` (spodnji € zneski pretvorjeni ×10, npr. DUBI 42: 1,25 € → 13 kristalov; seed: `scripts/wp-seed-kristali-dev.php`). Pripis ob plačanem naročilu, poraba na blagajni kot popust do vrednosti izdelkov (dostava se plača), rok trajanja 12 mesecev od zadnje aktivnosti (opcija `zvij_kristali_expiry_months`), Zvij koda za povabila in za unovčenje gostov. Javni napis: »Član prejme X kristalov za naslednji reload.« Brez izplačil.

## Purpose

Dobroimetje should make the next reload feel natural. The goal is not a discount-hunting store, but a member loop:

1. first purchase is a low-friction try
2. membership gives a clear Zvij koda
3. repeat orders return with dobroimetje
4. reload becomes easier than rebuilding the cart

Dobroimetje is store credit only. No cash payout, no reseller framing, no MLM.

## Current Product Notes

| Product | Dobroimetje |
| --- | ---: |
| DUBI 42 aktivnih ogljikovih filtrov | 1.25 EUR |
| DUBI 420 aktivnih ogljikovih filtrov | 7.50 EUR |
| SMOKEY CBD vršički 1 g | 0.80 EUR |
| SMOKEY CBD vršički 5 g | 4.00 EUR |
| CHILLY CBG vršički 1 g | 1.00 EUR |
| CHILLY CBG vršički 5 g | 4.50 EUR |
| FRUTTY CBD vršički 1 g | 0.80 EUR |
| FRUTTY CBD vršički 5 g | 3.50 EUR |

## Public Copy Pattern

Use:

```text
Član prejme X € za naslednji reload.
```

Avoid:

- income language
- cashback language unless legally approved
- payout language
- referral hype
