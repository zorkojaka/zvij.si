# Shop Prototype Status

## Completed

- Split `zvij-theme` into maintainable templates:
  - `header.php`
  - `footer.php`
  - `front-page.php`
  - `page.php`
  - `archive-product.php`
  - `taxonomy-product_cat.php`
  - `index.php`
- Added a stronger visual system in `style.css`.
- Added WooCommerce product grid/card styling.
- Added visible product category labels on product cards.
- Updated the baseline script to create richer static page copy.
- Disabled WooCommerce coming-soon mode for dev.
- Published current dev catalog products with real reviewed prices.

## Public Dev Products

The public shop currently uses active dev products:

- `SMOKEY CBD čaj 1 g`
- `SMOKEY CBD čaj 5 g`
- `CHILLY CBG čaj 1 g`
- `CHILLY CBG čaj 5 g`
- `FRUTTY CBD čaj 1 g`
- `FRUTTY CBD čaj 5 g`
- `DUBI 42 aktivnih ogljikovih filtrov`
- `DUBI 420 aktivnih ogljikovih filtrov`
- `Sample paket`
- `Zvij setup paket`

`Sample paket` is marked as a DEV placeholder until contents are confirmed. `Zvij setup paket` is marked as a DEV bundle until contents are confirmed. Payments and production shipping are not configured.

Older draft placeholders may still exist in WordPress; they are not public.

## Static Pages Updated

- `Član Zvij.si`
- `DUBI filtri`
- `CBD čaj`
- `Zvij setup`
- `Kontakt`
- `Trgovina`

Each has a clear headline, short intro/excerpt, body copy, and CTA buttons through the page template.

## Still Placeholder

- final production product names
- product photography
- production approval of prices
- final Sample/Zvij setup package contents
- refill intervals
- legal checkout copy
- shipping rules
- payment provider
- CBD certificates/lab documents, if required
- membership/dobroimetje mechanics
- QR token behavior

## Verification Notes

Public prototype checks should include:

```bash
curl -I https://dev.inteligent.si
curl -sL https://dev.inteligent.si | grep -i "http://" || true
curl -sL https://dev.inteligent.si/trgovina/ | grep "DEV placeholder"
```

Use browser screenshots for final design review because CSS layout quality cannot be fully judged from curl output.

## Recommended Next Task

Review the public prototype in an incognito browser and decide:

1. Which product placeholders should become real first.
2. Whether the visual tone is warm enough or needs more product-culture edge.
3. Which images to use for DUBI filters, CBD čaj, setup, and refill.
4. Whether placeholder products should remain public while product data is being gathered.
