# Zvij.si Image Asset Map

Status: asset audit and planning map.

## Image governance meta (2026-06-18)

Per-product meta set by `scripts/wp-sync-catalog-dev.php` so temporary images can be
replaced later without touching templates:

- `_zvij_image_kind`: `real_photo` | `supplier_photo` | `temporary_mockup` | `missing`
- `_zvij_image_source`: free text (where the current image came from)
- `_zvij_final_photo_pending`: `yes` | `no`

Replacement locations (no code/layout changes required):

- **Product card / detail** images come from the WooCommerce featured image. Swap the
  media-library image (or set a real featured image) and the product updates everywhere.
- **Kit hero visuals** use temporary flat-lay assets at fixed aspect ratios:
  `wp-content/themes/zvij-theme/assets/images/kits/{black,silver,gold,throwie}-kit-flatlay.png`.
  Asset paths are resolved by `zvij_kit_flatlay_url()`; replace the files in place with real
  owned photography (keep filename and aspect ratio).

Per-component current status: `docs/ZVIJ_PRODUCT_MATRIX.md`.

## Homepage editorial block image slots (2026-06-18)

The homepage product/category section uses image-led editorial blocks. Each block
reads a configurable image slot via `zvij_home_block_img(<name>)`; drop a real photo
at the path below and it overrides the temporary fallback automatically (no code change).

Directory: `wp-content/themes/zvij-theme/assets/images/home/`
Accepted extensions: `jpg`, `jpeg`, `png`, `webp` (first match wins).

| Block | Slot file (`…/home/`) | Aspect | Temporary fallback | Final photo |
|---|---|---|---|---|
| DUBI (half) | `dubi.*` | ~4:3 | DUBI product image (kraft pouch) | needed |
| CBD/CBG (half) | `cbd.*` | ~4:3 | SMOKEY+CHILLY+FRUTTY packs composed | needed |
| Kiti — Black (full) | `kiti-black.*` | ~16:7 | `images/kits/black-kit-flatlay.png` | needed |
| Kiti — Silver (full) | `kiti-silver.*` | ~16:7 | `images/kits/silver-kit-flatlay.png` | needed |
| Kiti — Gold (full) | `kiti-gold.*` | ~16:7 | `images/kits/gold-kit-flatlay.png` | needed |
| Reload (full) | `reload.*` | ~16:7 | `images/kits/throwie-kit-flatlay.png` | needed |

Recommended sizes: full-width blocks ~2000×875 (16:7); half-width blocks ~1200×900
(4:3). The Kiti block colour dots (Black/Silver/Gold) swap the background to the
matching `kiti-<colour>` slot. Overlay copy and gradient are fixed in the theme; only
the images change.

## Reference Mockups

Expected but missing in current checkout:

```text
docs/design/mockups/homepage.png
docs/design/mockups/shop.png
docs/design/mockups/kiti.png
docs/design/mockups/reload.png
docs/design/mockups/o-nas.png
```

Rules:

- These are visual references only.
- Do not place full mockup screenshots on the public website.
- Do not use them as production image assets.
- Use them only to inspect layout, spacing, type hierarchy, component ratios, and section order.

Current status: `docs/design/` did not exist before this documentation pass, and no expected mockup PNG was found anywhere in the repo.

## Temporary AI Hero / Kit Images

Current local theme assets:

```text
wp-content/themes/zvij-theme/assets/images/kits/black-kit-flatlay.png
wp-content/themes/zvij-theme/assets/images/kits/silver-kit-flatlay.png
wp-content/themes/zvij-theme/assets/images/kits/gold-kit-flatlay.png
wp-content/themes/zvij-theme/assets/images/kits/throwie-kit-flatlay.png
```

Usage:

- Dev/prototype kit visual placeholders.
- Can remain until final owned product photography exists.
- Do not treat as final product photography.

Known issue:

- PNG files are large. Convert to optimized WebP/JPEG during implementation if tooling is available.

## Real Supplier Product Images

Knistermann candidate images:

- Source file: `data/knistermann_kadilski_pribor.csv`
- CSV delimiter: semicolon `;`
- Product key: `ID`
- Image source field: `Slika_URL`
- Sync/import path: `scripts/wp-sync-catalog-dev.php`
- Storage: imported into WP media library when possible; original URL stored in product meta `_zvij_source_image_url`

Current first-wave supplier candidates include:

- FARO HEMP 2
- Clipper Gold
- Clipper Black
- Clipper Black Gradient
- Champ High Black Grinder 60 mm
- IRIE XTRA Light
- JaJa Noir Black
- Smoking Black Rolls
- Smoking Brown Rolls, ID `8046`
- Smoking Silver Rolls
- SmK Gold Rolls
- SmK Gold Papers + Filter Tips

Rules:

- Use supplier images only for dev/candidate product display.
- Store traceability meta.
- Do not invent fake images where source image is missing.

## Live / Migrated Product Images

Sourced via `scripts/wp-sync-catalog-dev.php` from legacy/live public URLs:

- DUBI images from `zvij.si` uploads.
- SMOKEY image from `zvij.si` uploads.
- CHILLY image from `zvij.si` uploads.
- FRUTTY image from `zvij.si` uploads.

Rules:

- Live `zvij.si` is content source only; do not modify it.
- Imported media can be used on dev.
- Copy and legal positioning must stay careful.

## Homepage Product Carousel Images

The homepage product carousel uses real WooCommerce product images from the WP media library. It does not use mockup screenshots or generated placeholders.

Source rule:

- Products are selected by tag `homepage-carousel`.
- Product image comes from `WC_Product::get_image()`.
- Missing images should be fixed in WooCommerce media/product data, not hardcoded in the template.

Initial dev product image slots:

- DUBI 42 aktivnih ogljikovih filtrov
- DUBI 420 aktivnih ogljikovih filtrov
- SMOKEY CBD vrsicki
- CHILLY CBG vrsicki
- FRUTTY CBD vrsicki

## Real Owned Product Images

Needed and not yet present as final assets:

- Final DUBI 42 packshot
- Final DUBI 420 packshot or bulk/reload packshot
- SMOKEY CBD vršički final packshot
- CHILLY CBG vršički final packshot
- FRUTTY CBD vršički final packshot
- Black Kit flat-lay with real owned components
- Silver Kit flat-lay with real owned components
- Gold Kit flat-lay with real owned components
- Throwie Kit flat-lay with real owned components
- Reload/lifestyle imagery
- O nas/lifestyle or brand photography

Recommended final image sizes:

- Product card: `1200x1200`
- Product hero: `1600x1200`
- Kit wide flat-lay: `1920x840` or `2560x1120` (`16:7`)
- Homepage hero: confirm from mockup; likely wide lifestyle crop
- Social/story crop: `1080x1350`

## Missing Final Photography

Highest priority:

1. Homepage lifestyle hero aligned with approved `homepage.png`.
2. Main product family images for CBD/CBG vršički and DUBI filtri.
3. Black/Silver/Gold kit real flat-lays.
4. Throwie real flat-lay.
5. Reload visual explaining repeat/reorder without implying returned packaging.
6. O nas brand/lifestyle visual.

## Asset Governance

- Reference mockups live in `docs/design/mockups/`.
- Production theme assets live under `wp-content/themes/zvij-theme/assets/images/`.
- WooCommerce product images should live in WP media library via sync/import where possible.
- Source CSV/import data lives under `data/`.
- Do not commit private supplier credentials or private order screenshots.
- Do not use a user order screenshot as final product image.

## Implementation Notes

When mockups arrive:

1. Inspect each PNG visually.
2. Identify every image slot.
3. Update this map with exact asset requirement per slot.
4. Decide whether each slot uses final photography, temporary AI asset, Woo media image, or CSS-only background.
5. Only then implement frontend image placement.
