# Zvij.si Component Map

Status: audit and implementation planning map.

Mockup status: `docs/design/mockups/` is missing in the current checkout. Component decisions below are based on existing code and the requested design-first hierarchy. Reconcile visual details against mockups before implementation.

## Theme Structure

| Area | File | Current status | Decision |
| --- | --- | --- | --- |
| Header | `wp-content/themes/zvij-theme/header.php` | Simple brand + primary nav | Reuse structure, restyle to mockup |
| Footer | `wp-content/themes/zvij-theme/footer.php` | Simple dev footer | Replace content/visual treatment after mockup review |
| Homepage | `wp-content/themes/zvij-theme/front-page.php` | Hardcoded sections and kit showcase | Replace section order/content to approved mockup; preserve kit logic |
| Page | `wp-content/themes/zvij-theme/page.php` | Generic page hero + content card + CTAs | Reuse for fallback; likely create page-specific templates for Kiti/Reload/O nas |
| Shop archive | `wp-content/themes/zvij-theme/archive-product.php` | Custom shop hero, product-tag filters, Woo loop | Reuse behavior; restyle to mockup |
| Product category | `taxonomy-product_cat.php` | Delegates to archive-product | Reuse |
| Index | `index.php` | Fallback only | Keep |
| CSS | `style.css` | Single-file CSS with tokens, layout, Woo, kit sections | Refactor into clearer sections or keep single file with stricter component comments |
| Kit JS | `assets/kits.js` | Toggle chips, count, reset, placeholder order | Reuse behavior if mockup still supports selection |

## Plugin Structure

| Area | File | Current status | Decision |
| --- | --- | --- | --- |
| Core plugin | `wp-content/plugins/zvij-core/zvij-core.php` | Admin notice only | Leave alone; do not move frontend display logic here yet |

## Data / Sync

| Component/Data | Source | Current status | Decision |
| --- | --- | --- | --- |
| Product catalog | `scripts/wp-sync-catalog-dev.php` | Idempotent Woo product sync | Preserve |
| CBD/CBG variables | `zvij_catalog_variable_product()` | SMOKEY/CHILLY/FRUTTY variable products with `1 g / 5 g` | Preserve |
| Kit data | `zvij_kit_definitions()` -> `zvij_kits` option | Black/Silver/Gold/Throwie items and add-ons | Preserve data; revise labels/copy to mockup |
| Kit components | `zvij_kit_components()` | Draft candidate products | Preserve |
| Knistermann images | `data/knistermann_kadilski_pribor.csv` | Source image URLs and imported media | Preserve |
| Pages/menu | `zvij_catalog_sync_pages_and_menu()` | Generates current baseline pages/menu | Adjust after mockup/page decision |

## Components

### SiteHeader

- PHP/template: `header.php`
- CSS classes: `.site-header`, `.site-brand`, `.site-nav`
- JS behavior: none
- Data source: WP menu location `primary`
- Pages using it: all
- Exists: yes
- Decision: reuse/refactor styling

Implementation notes:

- Keep nav data-driven.
- Avoid adding hardcoded page links in template.

### SiteFooter

- PHP/template: `footer.php`
- CSS classes: `.site-footer`
- JS behavior: none
- Data source: currently hardcoded
- Pages using it: all
- Exists: yes
- Decision: replace content/visual treatment after mockup review

### Hero

- PHP/template: currently hardcoded in `front-page.php`, `page.php`, `archive-product.php`
- CSS classes: `.hero`, `.page-hero`, `.shop-hero`
- JS behavior: none
- Data source: template copy/page fields
- Pages using it: homepage, generic pages, shop
- Exists: yes
- Decision: refactor into reusable hero patterns after mockup review

Needed variants:

- `HomeHero`
- `ShopHero`
- `EditorialPageHero`
- `KitPageHero`

### Button

- PHP/template: anchor/button markup across templates and WooCommerce
- CSS classes: `.button`, `.button--ghost`, `.button--light`, Woo `.button`
- JS behavior: native link/button
- Data source: template
- Exists: yes
- Decision: reuse styling tokens; tighten variants

### SectionHeading

- PHP/template: repeated in `front-page.php`
- CSS classes: `.section-heading`, `.eyebrow`
- JS behavior: none
- Data source: template copy
- Exists: yes
- Decision: reuse/refactor into helper only if duplication grows

### ProductCard

- PHP/template: WooCommerce `content-product` from plugin default, modified through hooks
- CSS classes: `.woocommerce ul.products li.product`, `.product-card__cat`, `.product-card__badge`, `.product-card__credit`
- JS behavior: WooCommerce default
- Data source: WooCommerce products/categories/meta
- Pages using it: shop, product tag/category archives, related products
- Exists: yes
- Decision: reuse behavior; restyle to match shop mockup

Do not break:

- Variable product `Select options`
- Dobroimetje note
- Category label
- FRUTTY badge

### ShopFilter

- PHP/template: `archive-product.php`
- CSS classes: `.kit-filter`, `.kit-filter__chip`
- JS behavior: none; links to product tag archive
- Data source: product tags `black-kit`, `silver-kit`, `gold-kit`, `throwie`, `reload`
- Pages using it: shop archive
- Exists: yes
- Decision: reuse behavior; restyle

### VariationSelector

- PHP/template: WooCommerce default single product variable form
- CSS classes: Woo `.variations_form`, `.variations`, `.single_variation_wrap`
- JS behavior: WooCommerce variation JS
- Data source: WooCommerce variation attributes
- Pages using it: SMOKEY/CHILLY/FRUTTY product pages
- Exists: yes
- Decision: preserve; style only

### SingleProductHero

- PHP/template: WooCommerce single product template from plugin default, styled by theme CSS and hooks
- CSS classes: `.single-product div.product`, `.summary`, `.woocommerce-product-gallery`, `.single-product__chips`
- JS behavior: Woo gallery/variation scripts
- Data source: WooCommerce product data/meta
- Pages using it: product detail pages
- Exists: yes
- Decision: reuse; improve only after shop/product mockup review

### ProductContextBlocks

- PHP/template: hook in `functions.php` on `woocommerce_after_single_product_summary`
- CSS classes: `.zvij-product-context`, `.zvij-credit-note`, `.zvij-packaging-note`
- JS behavior: none
- Data source: product meta and product name
- Pages using it: all single products
- Exists: yes
- Decision: likely replace generic copy with mockup-aligned product detail blocks later; preserve metadata behavior

### DubiVideoBlock

- PHP/template: hook in `functions.php`
- CSS classes: `.zvij-product-video-panel`, `.zvij-video-frame`, `.zvij-video-caption`
- JS behavior: iframe only
- Data source: `_zvij_dubi_youtube_url`
- Pages using it: DUBI products
- Exists: yes
- Decision: preserve; restyle to mockup if visible

### KitShowcase

- PHP/template: `zvij_render_kit_showcase()` in `functions.php`
- CSS classes: `.kit-showcase`, `.kit-row`, `.kit-visual`, `.kit-info`, `.kit-chip`, `.kit-actions`
- JS behavior: `assets/kits.js`
- Data source: `zvij_kits` option; resolves products and product variations by slug
- Pages using it: homepage; future Kiti page
- Exists: yes
- Decision: preserve logic; redesign only to match mockup, not ad-hoc

Current limitation:

- It presents Black/Silver/Gold/Throwie as stacked cards. Approved Kiti page requires Black/Silver/Gold together as color choices of one kit concept.

### MainProductFeature

- PHP/template: not yet implemented as distinct component
- CSS classes: likely new
- JS behavior: none
- Data source: WooCommerce categories/products
- Pages using it: Homepage
- Exists: no
- Decision: create during implementation

### HomepageProductCarousel

- PHP/template: `zvij_render_homepage_product_carousel()` in `functions.php`, inserted in `front-page.php`
- CSS classes: `.zv-carousel`, `.zv-carousel__track`, `.zv-carousel-card`
- JS behavior: native infinite carousel and variable add-to-cart in `assets/home.js`
- Data source: WooCommerce product tag `homepage-carousel`; admin order meta `_zvij_homepage_carousel_order`
- Pages using it: Homepage
- Exists: yes
- Decision: preserve WooCommerce products/images and keep ordering admin-controlled

Current notes:

- No external slider dependency is used.
- Variable products add selected variations only.
- See `docs/design/HOMEPAGE_PRODUCT_CAROUSEL.md`.

### ReloadFeature

- PHP/template: not yet implemented as distinct component
- CSS classes: likely new
- JS behavior: none initially
- Data source: product meta and docs strategy
- Pages using it: Homepage, Reload page
- Exists: no
- Decision: create during implementation

### MembershipStrip

- PHP/template: currently hardcoded split section in `front-page.php`
- CSS classes: `.split-section`, `.section-tagline`
- JS behavior: none
- Data source: template copy/docs
- Pages using it: Homepage, O nas or Reload
- Exists: partial
- Decision: refactor after mockup review

### TrustStrip

- PHP/template: not yet implemented as distinct component
- CSS classes: likely new
- JS behavior: none
- Data source: static copy
- Pages using it: Homepage, Shop, O nas
- Exists: no
- Decision: create only if present in mockups

## Implementation Order Proposal

1. Add missing mockups to `docs/design/mockups/` and update this map from actual visuals.
2. Freeze final page list and nav order: Homepage, Shop, Kiti, Reload, O nas, plus any support pages.
3. Refactor CSS tokens/layout without changing data logic.
4. Implement homepage sections against `homepage.png`.
5. Implement shop visual pass against `shop.png`, preserving WooCommerce data.
6. Implement Kiti page against `kiti.png`, reusing `zvij_kits`.
7. Implement Reload and O nas pages.
8. Browser verify desktop/mobile after each page.

## Non-Negotiable Preserve List

- `scripts/wp-sync-catalog-dev.php` product sync.
- SMOKEY/CHILLY/FRUTTY variable products.
- DUBI products and video metadata.
- Product tag filters.
- Kit add-on product/variation ID collection.
- No production payment/shipping configuration.
