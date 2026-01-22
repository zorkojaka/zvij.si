# Migration Notes

## Source inventory (local WP repo)

- Theme: `wp-content/themes/plamen` (Qode Plamen theme).
- Plugins detected:
  - `woocommerce`, `woocommerce-payments`, `woocommerce-services`
  - `elementor`, `qi-addons-for-elementor`
  - `revslider`
  - `contact-form-7`
  - `custom-css-js`
  - `duplicator`
  - `plamen-core`, `qode-framework`

## What was captured

- Initial Astro project scaffold.
- Content model skeleton in `src/content/`.
- Placeholder homepage using content-driven sections.

## What still needs live-crawl confirmation

- Exact navigation items, URLs, and IA.
- Global typography, spacing, colors, and responsive rules.
- UI component variants (hero, sliders, product grids, etc.).
- SEO meta, canonical, and OG tags per page.
- Cookie/consent behavior and GA4 integration.
