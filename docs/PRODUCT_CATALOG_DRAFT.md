# Product Catalog Draft

Status: current dev shop catalog.

## Naming Map

- CBD NM = SMOKEY
- CBG NM = CHILLY
- Bubble Gum = FRUTTY

Public names should use `SMOKEY`, `CHILLY`, and `FRUTTY`.

## Active Product Families

### CBD/CBG vršički

- SMOKEY CBD vršički 1 g
- SMOKEY CBD vršički 5 g
- CHILLY CBG vršički 1 g
- CHILLY CBG vršički 5 g
- FRUTTY CBD vršički 1 g
- FRUTTY CBD vršički 5 g

Product framing:

- premium vršički
- izbrani vršički
- 1 g packaging
- 5 g = 5 x 1 g packages
- čajna uporaba as one possible use
- brez THC učinka
- jasna mera
- ritual

Avoid:

- `CBD čaj` as a primary product or category name
- smoking instructions
- medical claims
- anxiety, sleep, pain, stress relief, treatment, illness, or similar
- legal high language
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
- sample CBD/CBG vršičkov

## Implementation Notes

The repeatable sync script is:

```bash
scripts/wp-sync-catalog-dev.sh
```

It updates WooCommerce products, categories, prices, sale price for FRUTTY first offer, page/menu terminology, product copy, dobroimetje metadata, and packaging metadata for dev.
