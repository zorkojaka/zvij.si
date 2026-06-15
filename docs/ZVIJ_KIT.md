# Zvij.si Kit

Status: dev implementation.

Core idea: **Zvij.si Kit = vse, kar rabiš, da si zviješ.** The homepage presents kits as a
showcase of packages, not a builder.

Commercial selection source of truth: `docs/ZVIJ_PRODUCT_MATRIX.md`.

## Logic

- Black / Silver / Gold are **style**, not price tiers.
- Throwie is a utility setup pouch concept (cheap, useful, all-in-one) — never "throw away evidence".
- Kits include DUBI filters. SMOKEY / CHILLY / FRUTTY vršički are optional add-ons.
- Blunts and RAW are out of v1.
- Black/Silver/Gold tubes are ordered draft add-ons, not public confirmed products.
- Use **reload**, never refill.

## How it is built (custom showcase)

Data-driven, no cart/builder logic and no payments.

- **`scripts/wp-sync-catalog-dev.php`**
  - `zvij_kit_tag_registry()` — kit/reload product tags: `black-kit`, `silver-kit`, `gold-kit`, `throwie`, `reload`, `kit-addon`, `kit-component`, `dubi`, `vrsicki`.
  - `zvij_catalog_product()` accepts a `tags` key and assigns `product_tag` terms.
  - `zvij_kit_components()` — kit component products created as **draft** (Knistermann-sourced have
    `_zvij_supplier`, `_zvij_supplier_id`, `_zvij_supplier_sku`, `_zvij_b2b_price`,
    `_zvij_source_image_url` and `_zvij_source_product_url` in meta; purchase prices
    are wholesale and never shown publicly). TBD items have `_zvij_supplier = TBD`.
  - `data/knistermann_kadilski_pribor.csv` — semicolon-separated (`;`) Knistermann source rows,
    keyed by `ID`, with `Slika_URL` used for product image import.
  - `zvij_kit_definitions()` — the showcase structure (kits, items by product slug, optional add-ons),
    stored in the `zvij_kits` option for the theme.
  - Existing published products (DUBI 42/420, vršički) are tagged via a slug→tags map.
- **`wp-content/themes/zvij-theme/functions.php`**
  - `zvij_render_kit_showcase()` reads the `zvij_kits` option and renders **one full-width
    horizontal row per kit**: wide composed-kit visual (placeholder until a real photo exists),
    selectable product chips below, and an order panel on the right (selected count, "cena se
    izračuna iz izbranih izdelkov", `Naroči kit` CTA).
  - `zvij_kit_chip_markup()` renders each product as a toggle chip with `data-sku` / `data-price` /
    `data-product-id`, `data-sku`, `data-price` and `data-default`. Core products start selected; optional vršički start unselected (opt-in).
    Drafts keep a "kmalu" badge.
  - The DUBI single-product video is a premium block (rounded frame, shadow, caption).
- **`front-page.php`** — hero (`Vse, kar rabiš, da si zviješ.`) + `zvij_render_kit_showcase()` at `#kiti`.
- **`assets/kits.js`** — click a chip to toggle included/excluded (selected = active, unselected =
  dimmed + strikethrough), live count in the order panel, `Ponastavi kit` restores defaults, and
  `Naroči kit` collects selected product IDs and SKUs into a status message/dataset (placeholder until checkout exists).
- **`archive-product.php`** — kit/reload filter chips linking to `product_tag` archives.
- **`style.css`** — `.kit-*`, `.kit-filter*`, premium video frame styles.

## Run

```bash
scripts/wp-sync-catalog-dev.sh   # docker compose run --rm wp-cli eval-file ...
```

Idempotent: re-running updates products, tags and the `zvij_kits` option in place.

## Still placeholder

- Component supplier samples, final margins and photos (components stay draft until confirmed).
- Silver Clipper, gold/silver grinders, Throwie Bag and rolling tray are still TBD placeholders.
- Knistermann image URLs are sourced from `data/knistermann_kadilski_pribor.csv`. Sync imports them
  into the WP media library when possible; if import fails, the source URL remains in product meta.
- Kits are a showcase only; no bundle product, cart bundling or payments.
