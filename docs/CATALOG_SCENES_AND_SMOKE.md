# Catalog Scenes and Footer Smoke

## Catalog

- 18 existing product photographs were reframed with AI-extended photographic backgrounds and breathing room around the actual products.
- Three additional CBD/CBG multipack photographs replace the old overlapping-photo clones when selecting 5 g, in both the shop and homepage carousel.
- Square media frames display the complete square photograph edge to edge without inset padding. Text and purchase controls remain outside the image frame.
- Five products still use their existing clearly labeled image placeholders: Mycelium Mystique, Rocket's Odyssey, Clipper refill gas, gold grinder and small silver grinder. Actual product appearances were not invented for missing photographs.
- Reviewed JPEG assets are in `wp-content/themes/zvij-theme/assets/images/catalog-v2/`.
- Run `php /var/www/html/scripts/wp-import-catalog-scenes.php` inside the development WordPress container. It is development-only, validates the full asset set before writes and is idempotent. Source attachment IDs are checked before changing products.
- Original featured attachment IDs are retained in `_zvij_before_catalog_scene_v2` and the product gallery. New attachments use `_zvij_catalog_scene_v2`; multipack attachments use `_zvij_pack_5_image_id`. No images are deleted and prices/stock are not modified.

## Smoke

- `assets/smoke.js` renders soft, tapered animated wisps on a fixed, noninteractive canvas between page backgrounds and content.
- The source follows the ember at the top of the I in the footer logo, including document reflow and viewport resizing. The plume rises in document coordinates to the page top.
- Main content, header, footer logo, forms and links remain above the effect.
- Reduced-motion preference removes the canvas, including changes made while the page is open. Hidden tabs pause rendering. The low-resolution canvas uses four batched wisps, capped at 20 fps desktop and approximately 15 fps mobile.

## Verification

`scripts/test-catalog-scenes.cjs` requires Playwright and targets `ZVIJ_TEST_URL` (default development). It checks every shop group at 1440 px and 390 px, full-frame square image geometry, 1g/5g switching in shop/carousel, animated nonblank smoke, footer anchoring, click-through behavior and reduced-motion removal. Screenshot review covers desktop/mobile and light/dark footer views.

Existing kit-bonus, invitation, public-copy, shop-group and color-scheme test suites were also rerun. Integration fixtures are transactionally rolled back. Production catalog data was not changed.
