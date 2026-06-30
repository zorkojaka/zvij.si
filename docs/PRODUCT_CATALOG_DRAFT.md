# Product Catalog Draft

Status: current dev shop catalog.

## Real inventory phase (2026-06-18)

Kit-component products now carry an inventory status (`received` | `incoming` |
`supplier_candidate` | `later`) and image-governance meta. Received items (Zvij.si mini
grinder + black/silver/gold metal tubes) and incoming items (lighters, RAW rolls, Ziggi
rolls, colour grinders, pouch) stay WooCommerce `draft` with no fake retail price. Full
matrix, unified kit schema and the **CBD kapljice** roadmap (documented only, not on the
frontend) live in `docs/ZVIJ_PRODUCT_MATRIX.md`.

Commercial product selection is governed by `docs/ZVIJ_PRODUCT_MATRIX.md`. The shop should not become a random accessories list: every product should fit a value tier, an aesthetic pole, or a kit role.

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

### Zvij.si Kit Components

The kit component catalog is synced as draft/candidate WooCommerce products from `scripts/wp-sync-catalog-dev.php`.

Current first-wave kit component groups:

- Vžigalniki: Cheap fajrji HEMP 2, Clipper Black, Clipper Black Gradient, Clipper Gold, Silver Clipper placeholder.
- Rizle/rolce: IRIE XTRA Light, JaJa Noir Black, Smoking Black Rolls, Smoking Brown Rolls, Smoking Silver Rolls, SmK Gold Rolls, SmK Gold Papers + Filter Tips.
- Grinderji: Champ High Black Grinder 60 mm, Zvij.si Mini Grinder 5 cm, Silver/Gold grinder placeholders.
- Tubes/setup: Black/Silver/Gold Metal Joint Tube, Throwie Bag / setup pouch, rolling tray placeholder.

Knistermann candidate images are sourced from semicolon CSV `data/knistermann_kadilski_pribor.csv`.
Products are mapped by Knistermann `ID`, not by generic SKU. Smoking Brown uses ID `8046`
(`Smoking BROWN THINNEST Rolls`); ID `1521` is intentionally not used.

All first-wave supplier candidates stay draft until Jaka confirms samples, images, margins, and final kit composition.

## Implementation Notes

The repeatable sync script is:

```bash
scripts/wp-sync-catalog-dev.sh
```

It updates WooCommerce products, categories, prices, sale price for FRUTTY first offer, page/menu terminology, product copy, dobroimetje metadata, and packaging metadata for dev.
