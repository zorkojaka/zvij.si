# Homepage Product Carousel

Homepage carousel is rendered by `zvij_render_homepage_product_carousel()` in `wp-content/themes/zvij-theme/functions.php` and inserted in `front-page.php` between the hero/service strip and the image-led editorial category blocks.

## Product Source

- WooCommerce products must have product tag `homepage-carousel`.
- Products must be published, purchasable, priced, and in stock or backorderable.
- Products with `sample`, `internal`, or `tbd` in name/slug are excluded.
- Admin order is controlled by product meta `_zvij_homepage_carousel_order`.
- Admin field label: `Homepage carousel order`.

Initial dev products:

- DUBI 42 aktivnih ogljikovih filtrov
- DUBI 420 aktivnih ogljikovih filtrov
- SMOKEY CBD vrsicki
- CHILLY CBG vrsicki
- FRUTTY CBD vrsicki

## Variable Product Behavior

Variable products render inline quantity buttons from purchasable variations. The add button is disabled until a variation is selected.

Add-to-cart submits WooCommerce AJAX data with:

- `product_id`
- `variation_id`
- `quantity=1`
- all variation attributes, for example `attribute_pakiranje`

The parent variable product is never added directly.

## Frontend Behavior

- Native lightweight JavaScript in `assets/home.js`; no external slider dependency.
- Infinite loop uses cloned cards before and after the real product set.
- Autoplay pauses on hover, focus, and drag.
- Previous/next controls, keyboard arrows, and pointer swipe are supported.
- `prefers-reduced-motion: reduce` disables autoplay.

Analytics attributes are present on cards and add buttons:

- `data-product-id`
- `data-variation-id`
- `data-carousel-position`
- `data-carousel-source="homepage"`

## Styling

Styles live in `style.css` under `.zv-carousel*`.

Responsive card widths:

- Desktop: four cards visible.
- Tablet: two cards visible.
- Mobile: one dominant card with partial next-card preview.

## Verification Checklist

- PHP lint `functions.php` and `front-page.php`.
- Homepage contains `.zv-carousel`.
- Desktop, tablet, and mobile viewports do not overflow.
- Simple products add through WooCommerce AJAX.
- Variable products require a variation and add the selected variation ID.
- No browser console errors.
