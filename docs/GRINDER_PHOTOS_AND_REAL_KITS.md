# Grinder Photos and Real Kit Scenes

AI-assisted product photographs were created from Jaka's supplied silver grinder, gold grinder and metal grinder card photographs. Each has three reviewed square views. The assets retain clear space around the products and full-bleed photographic backgrounds.

## Products

- Existing silver grinder #217: closed, open with lid, both inner halves.
- Existing gold grinder #216: closed, four separate parts, open cutting chamber with lid.
- New metal grinder card #618: card with sleeve, card alone, card overlapping sleeve. It remains a draft with no price and is not purchasable until the selling price is supplied. No stock quantity was invented.
- Existing grinder prices, stock and statuses were preserved. Old attachment IDs are retained in `_zvij_before_grinder_media_v1`; the three new attachment IDs are in `_zvij_grinder_media_v1`.

## Default Kits

Each kit photograph shows exactly the five configured default products:

| Kit | Tube | Lighter | Grinder | Papers | Filters |
| --- | --- | --- | --- | --- | --- |
| Black | Black JollySafe #243 | Clipper Black #211 | Black Zvij.si #218 | Ziggi Wide Extra #441 | DUBI 42 #71 |
| Silver | Silver JollySafe #244 | Clipper Black #211 | Silver grinder #217 | Ziggi Original Classic Slim #435 | DUBI 42 #71 |
| Gold | Gold JollySafe #245 | Clipper Gold #213 | Gold grinder #216 | Ziggi Natural Classic Slim #437 | DUBI 42 #71 |

The silver Clipper #227 remains an unpublished draft; neither its color nor availability was fabricated. The silver kit's note explicitly identifies its black Clipper. Optional CBD add-on references now resolve to actual published parent products. The hidden Throwie kit is unchanged.

The old kit configuration is backed up in `zvij_kits_before_real_photos_v1`. Real kit JPEGs take priority over legacy flatlays throughout the theme; square card frames display the complete photograph.

## Operations and Tests

`php /var/www/html/scripts/wp-import-grinders-real-kits.php` inside the development WordPress container imports the reviewed assets, creates the card only if absent, and updates default kit references. It refuses production, preflights all photographs and available components, preserves legacy media and is repeatable without duplicate product/media records.

`scripts/test-grinders-real-kits.php` checks actual product/gallery assignments, square image dimensions, draft safety, available kit components, optional references and backup state. `scripts/test-grinder-kits.cjs` uses Playwright at 1440 px and 390 px to verify galleries, real kit photo/default agreement, selectable components and a real kit add-to-cart in an isolated anonymous session. The test cart is emptied; no order is placed. Age confirmation is synthetic UI fixture input only.

The existing kit-bonus, invitation, shop-group, public-copy, smoke-wind and color-scheme suites were also checked. Production database data was not changed.

Still missing primary product photographs: Ziggi Mycelium Mystique Special Edition, Ziggi Rocket's Odyssey Special Edition and Clipper refill gas 16 ml.
