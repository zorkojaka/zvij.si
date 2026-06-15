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

### Setup dodatki

Draft-only accessory category for small setup objects that belong naturally next to DUBI, vršički, setup bundles, and membership.

Current draft products:

- Rezervni vžigalnik
- Clipper standard
- Premium Clipper

Accessory logic:

- Cheap disposable lighter: backup / when needed / package add-on, target 1.50-2.50 EUR.
- Standard Clipper: main normal lighter, target 2.90-4.20 EUR.
- Premium Clipper: premium personal setup object / gift / pride item, target 14.90-29.90 EUR.

Keep all accessory products as draft until Jaka confirms supplier, exact prices, images, packaging, and legal/commercial handling.

Brand framing:

- A lighter is an "always with you" object.
- It belongs to setup culture.
- It can support the member line `Zvijače za zvijače.`
- It should feel useful, adult, and domestic, not childish or cheap.

## Implementation Notes

The repeatable sync script is:

```bash
scripts/wp-sync-catalog-dev.sh
```

It updates WooCommerce products, categories, prices, sale price for FRUTTY first offer, page/menu terminology, product copy, dobroimetje metadata, and packaging metadata for dev.
