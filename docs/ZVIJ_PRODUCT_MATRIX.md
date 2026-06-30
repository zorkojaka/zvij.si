# Zvij.si Product Matrix

Status: commercial backbone draft.

## Real inventory phase (2026-06-18)

Each kit component carries product meta (set in `scripts/wp-sync-catalog-dev.php`):

- `_zvij_inventory_status`: `received` | `incoming` | `supplier_candidate` | `later`
- `_zvij_image_kind`: `real_photo` | `supplier_photo` | `temporary_mockup` | `missing`
- `_zvij_image_source`: free text
- `_zvij_final_photo_pending`: `yes` | `no`

All components stay WooCommerce `draft` (no fake retail price exposed publicly).

| Component | slug | Inventory status | Image | Final photo |
|---|---|---|---|---|
| Zvij.si Mini Grinder 5 cm (logo) | `zvij-mini-grinder-5-cm` | received | missing | yes |
| Black Metal Tube | `black-metal-joint-tube` | received | missing | yes |
| Silver Metal Tube | `silver-metal-joint-tube` | received | missing | yes |
| Gold Metal Tube | `gold-metal-joint-tube` | received | missing | yes |
| Black lighter | `clipper-black` | incoming | temporary_mockup | yes |
| Silver lighter | `clipper-silver` | incoming | temporary_mockup | yes |
| Gold lighter | `clipper-gold` | incoming | temporary_mockup | yes |
| RAW rolls | `raw-rolls` | incoming | temporary_mockup | yes |
| Ziggi rolls | `ziggi-rolls` | incoming | temporary_mockup | yes |
| Silver grinder | `silver-grinder-placeholder` | incoming | temporary_mockup | yes |
| Gold grinder | `gold-grinder-placeholder` | incoming | temporary_mockup | yes |
| Throwie pouch | `throwie-bag` | incoming | temporary_mockup | yes |
| Champ High Black grinder, Clipper gradient, Smoking/IRIE/JaJa/SmK papers & rolls, FARO fajrji | (various) | supplier_candidate | supplier_photo | yes |
| Matching rolling tray | `matching-rolling-tray-placeholder` | later | missing | yes |

Received ≠ TBD: received items are real and in hand; they are draft only until
photographed and priced.

## Kit schema (unified)

One concept, five roles: `tube · lighter · grinder · paper · dubi`. Black, Silver
and Gold are colour variants of the same kit; Throwie is the separate lower-cost
setup. Mapping lives in `zvij_kit_definitions()` and is stored in the `zvij_kits`
option (data-driven; the theme renders names/components from it).

| Role | Black | Silver | Gold | Throwie |
|---|---|---|---|---|
| tube | black metal tube | silver metal tube | gold metal tube | pouch |
| lighter | black lighter | silver lighter | gold lighter | basic fajrji |
| grinder | Zvij.si mini grinder (received) | silver grinder | gold grinder | mini grinder |
| paper | RAW rolls | RAW rolls | RAW rolls | Ziggi rolls |
| dubi | DUBI 42 | DUBI 42 | DUBI 42 | DUBI 42 (optional) |

## Roadmap — CBD kapljice (NOT on frontend yet)

Future category: **CBD kapljice**. Status: supplier confirmed, commercial terms
pending. Must not be added to the public site until confirmed. Needed before launch:

- strengths
- bottle size
- wholesale price
- MOQ
- lab documentation
- shelf life
- private label option

Zvij.si product program is not a random accessories list. It is built on two axes:

1. three price/value tiers
2. two aesthetic/customer poles

The goal is to make buying decisions clear: what belongs in Throwie, what belongs in a normal setup, and what can become a premium object people are proud to own.

## Three Tiers

### 1. Ko zagusti

Cheap shit / no-brand for small money, without pretending.

Rules:

- direct value framing
- acceptable quality only
- no brand tax
- things you can lose without pain
- quality test decides whether it stays

Examples:

- 3-4 cheap lighters for 4.20
- cheap/no-brand backup lighter
- basic grinder if it does not feel like trash

### 2. Normalni setup

Known, reliable, everyday products.

Rules:

- balanced quality and price
- familiar brands are useful here
- should feel like a repeatable everyday setup

Examples:

- Clipper standard
- Smoking/Ziggy rolls
- DUBI 42
- Smoking/Ziggy roll + DUBI filters

### 3. Premium setup

Objects people are proud to own.

Rules:

- classy, durable, better margin
- premium item should not get a cheap sticker directly on it
- packaging/card/QR can carry Zvij.si layer
- limited series can come later

Examples:

- Premium Clipper Gold
- black metal grinder
- DUBI 420 as organized bulk/reload
- premium packaging/card

## Two Aesthetic Poles

### 420 Vibe

Playful, cultural, visible, and fun, but not childish.

Signals:

- FRUTTY
- 4.20 references
- Zvijače za zvijače.
- green/inside-culture references when used carefully

### Quiet Setup

Discreet, classy, mature, calm.

Signals:

- black, gold, beige, metal
- no rasta/Jamaica/ganja-leaf default
- objects that do not shout
- premium and adult without feeling corporate

Important insight:

Not every customer who uses cannabis culture wants to look like a rasta/Jamaica stereotype. Some want quiet, discreet, adult, premium objects.

## Matrix

| Tier | 420 vibe | Bridge / neutral | Quiet setup |
| --- | --- | --- | --- |
| Ko zagusti | 3-4 cheap lighters for 4.20; FRUTTY first try signal | no-brand backup lighter; basic pouch | cheap but clean black lighter/grinder only if quality is acceptable |
| Normalni setup | IRIE / value papers if quality is acceptable | Clipper standard; Smoking/Ziggy rolls; DUBI 42 | Smoking Black/Brown Thinnest Rolls; JaJa Noir; DUBI 42 |
| Premium setup | limited 420/member drop only if it stays adult | DUBI 420 as organized bulk/reload | Premium Clipper Gold; black metal grinder; gold/black metal tube |

## Category Application

### Lighters

- Ko zagusti: FARO HEMP 2 / cheap fajrji, only after quality test.
- Normalni setup: Clipper standard.
- Premium setup: Clipper Gold / premium Clipper object.
- Quiet setup: black, gold, metal, understated.
- 420 vibe: 4.20 pack logic, not childish graphics.

### Rolling Papers / Rolls

- Ko zagusti: IRIE XTRA Light if value and quality hold.
- Normalni setup: Smoking/Ziggy rolls.
- Quiet setup: Smoking Black Rolls, Smoking Brown Thinnest Rolls (Knistermann ID 8046), JaJa Noir Black.
- Gold style: SmK Gold Rolls or SmK Gold Papers + Filter Tips.
- RAW is not part of the first wave.

### Grinders

- Ko zagusti: Zvij.si Mini Grinder 5 cm if logo quality is acceptable.
- Normalni setup: basic grinder that feels solid.
- Premium/quiet: Champ High Black Grinder 60 mm or better black metal grinder.
- Gold/Silver placeholders wait for matching quality.

### Blunts

Not in the first wave.

### Filters

- DUBI 42 is the core everyday kit component.
- DUBI 420 is organized bulk/reload: 10 small packs in one bigger package.
- DUBI 420 is not premium because it is 420; it is premium only if the overall object, packaging, and margin justify it.

### CBD/CBG Vršički

- Optional add-ons under kits.
- FRUTTY carries the 420/first-try signal.
- SMOKEY and CHILLY sit better in normal/quiet setup.
- Main identity is vršički, not CBD čaj.
- Čajna uporaba can be mentioned as one possible use.
- No medical claims and no smoking instructions.

### Setup Packages

- Throwie: functional kit for ko zagusti / cheap / everything in one place.
- Black Kit: quiet setup, discreet black objects.
- Silver Kit: neutral bridge only if matching components exist.
- Gold Kit: warm/premium style, not a price tier.
- DUBI 42 must be in every main kit.
- Vršički are optional add-ons under every kit.

## Buying Rules

- No-brand is allowed only if quality is acceptable.
- Zvij.si branded product must mean good value, not cheap trash.
- Custom print/stickers are phase 2.
- First phase is supplier research, samples, and margin testing.
- Premium items should not get cheap stickers directly on them.
- QR/member card can be included in package.
- Black / Silver / Gold are style lines, not price tiers.
- Throwie is functional: ko zagusti / poceni / vse na enem mestu.
- Do not add RAW products in the first wave.
- Do not add blunts in the first wave.

## Next Supplier Research

1. Order/test FARO HEMP 2 quality before building a cheap lighter pack.
2. Compare Clipper Black, Black Gradient, Gold, and possible Silver source.
3. Inspect Zvij.si Mini Grinder logo quality when received.
4. Compare Champ High Black Grinder margin and feel.
5. Confirm Smoking Black/Brown/Silver and SmK Gold pack quantities.
6. Photograph black/silver/gold tubes with matching setup context.
