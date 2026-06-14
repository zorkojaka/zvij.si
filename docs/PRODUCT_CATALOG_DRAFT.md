# Product Catalog Draft

Status: current dev shop catalog.

## Naming Map

- CBD NM = SMOKEY
- CBG NM = CHILLY
- Bubble Gum = FRUTTY

Public product names should use `SMOKEY`, `CHILLY`, and `FRUTTY`.

## Active Product Families

### CBD čaj

- SMOKEY CBD čaj 1 g
- SMOKEY CBD čaj 5 g
- FRUTTY CBD čaj 1 g
- FRUTTY CBD čaj 5 g
- CHILLY CBG čaj 1 g
- CHILLY CBG čaj 5 g

Copy direction:

- ritual
- brez THC učinka
- jasna mera
- urejena izbira
- mirnejši setup

Avoid:

- smoking references
- medical claims
- anxiety, sleep, pain, stress relief, treatment, illness, or similar
- HHC as an offer

### DUBI filtri

- DUBI 42 aktivnih ogljikovih filtrov
- DUBI 420 aktivnih ogljikovih filtrov

### Zvij setup

- Sample paket: DEV placeholder until contents are confirmed.
- Zvij setup paket: DEV bundle until confirmed.

Planned setup contents:

- DUBI 42
- rolca
- sample CBD/CBG čaja

## Implementation Notes

The repeatable sync script is:

```bash
scripts/wp-sync-catalog-dev.sh
```

It updates WooCommerce products, categories, prices, statuses, product copy, and packaging metadata for dev.
