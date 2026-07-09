# Zvij.si Design System

Status: implementation specification draft.

Important: approved visual mockups are expected in `docs/design/mockups/`, but that directory is not present in the current checkout. Values below translate the current approved direction and existing implementation into concrete tokens. Re-check every value against the mockup PNGs before frontend implementation.

## Design Intent

Zvij.si should feel like domestic premium: warm, useful, visually confident, and close to the culture without visual clutter. The interface should feel like a trusted local companion that gets the products sorted, not a corporate CBD store and not a teenage rebel brand.

Primary visual rules:

- warm cream page background
- black typography
- gold accent
- large lifestyle or product photography
- strong short headlines
- humor in copy, not in decorative UI noise
- no medical claims
- no smoking instructions
- no HHC as active offer

## Layout

### Containers

- `--zvij-max`: `1280px`
- Narrow text max-width: `640px`
- Wide editorial text max-width: `760px`
- Shop/product grid container: `1280px`
- Single product container: `1240px` minimum, review against mockups

### Gutters

- Desktop side padding: `32px`
- Tablet side padding: `24px`
- Mobile side padding: `16px`

Implementation target:

```css
:root {
  --zvij-max: 1280px;
  --zvij-gutter-desktop: 32px;
  --zvij-gutter-tablet: 24px;
  --zvij-gutter-mobile: 16px;
}
```

### Breakpoints

- Desktop: `>= 1180px`
- Small desktop/tablet: `920px - 1179px`
- Mobile navigation/layout stack: `<= 640px`
- Small mobile: `<= 520px`

Existing CSS already uses `920px`, `760px`, `640px`, and `520px`. Keep these unless mockups clearly require a different breakpoint.

### Section Spacing

- Desktop section gap: `96px`
- Large hero-to-next-section gap: `112px`
- Tablet section gap: `72px`
- Mobile section gap: `56px`
- Inner component gap: `16px - 32px`

### Grids

- Product grid desktop: `repeat(3, minmax(0, 1fr))`
- Product grid tablet: `repeat(2, minmax(0, 1fr))`
- Product grid mobile: `1fr`
- Homepage main products: 2-up desktop, stacked mobile
- Kit cards: full-width stacked cards; do not introduce admin-style product grids
- Kiti page color choices: Black/Silver/Gold are a single kit concept with color selections, not unrelated categories

### Cards

- Standard card radius: `20px`
- Large image card radius: `24px`
- Small controls/buttons radius: `6px - 10px`
- Card padding desktop: `24px - 32px`
- Card padding mobile: `18px - 20px`
- Large image aspect ratio: `16 / 7`
- Product image area aspect ratio: `1 / 1` or mockup-specific

## Color Tokens

Define or align existing CSS custom properties:

```css
:root {
  --zvij-bg: #f3ebdd;          /* cream page background */
  --zvij-panel: #fffaf1;       /* cream card background */
  --zvij-panel-alt: #e9dcc8;   /* warm secondary cream */
  --zvij-text: #191613;        /* primary black */
  --zvij-muted: #6a6258;       /* muted text */
  --zvij-gold: #c39a45;        /* gold accent */
  --zvij-green: #315f49;       /* green accent */
  --zvij-silver: #b9bcc0;      /* silver accent */
  --zvij-line: #d8c9b5;        /* border color */
  --zvij-shadow-color: rgba(39, 30, 20, 0.12);
  --zvij-shadow: 0 24px 80px var(--zvij-shadow-color);
}
```

Usage:

- Black text for headlines and primary CTAs.
- Gold for premium accents, selected kit color hints, badges.
- Green for trust, membership, and category labels.
- Silver only for Silver Kit color cues.
- Borders should be warm and quiet, not grey UI chrome.

### Commerce & transactional emails (rdeča nit)

WooCommerce privzeto uporablja **vijolično** (`--wc-primary: #720eec`, email base `#8526ff`).
To NI brand barva — povsod jo prepišemo, da je nakupni proces vizualno enoten.

Blagajna / gumbi (v `style.css`):

- Prepiši WooCommerce spremenljivke: `--wc-primary` → `--zvij-accent` (zelena `#315f49`),
  `--wc-primary-text` → `#fff`, enako `--wc-highlight`.
- Glavni CTA gumbi (`.button.alt`, `#place_order`, `.checkout-button`,
  `.wc-block-components-button`) = **črni** (`--zvij-text`), **hover zelen** — enako kot
  primarni gumb v temi.
- Povezave/poudarki v WooCommerce prevzamejo zeleno prek `--wc-primary`.

Transakcijski emaili (WooCommerce → Nastavitve → Emaili; opcije v bazi):

| Opcija | Vrednost | Vloga |
|---|---|---|
| `woocommerce_email_base_color` | `#191613` | glava + naslovi (brand črna) |
| `woocommerce_email_background_color` | `#f3ebdd` | krem stran okoli sporočila |
| `woocommerce_email_body_background_color` | `#fffaf1` | krem kartica sporočila |
| `woocommerce_email_text_color` | `#191613` | besedilo |

Računska glava in UPN QR blok (zvij-core) že uporabljata isto toplo paleto
(`#fbf8f3`, `#e0d9cf`, `#c8934e`) — ohrani skladnost pri novih email blokih.

## Typography

Use safe system sans until a brand font is selected.

```css
--zvij-font-sans: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
```

### Desktop Scale

- H1: `clamp(4.6rem, 9vw, 8.8rem)`, line-height `0.88 - 0.92`, weight `850 - 900`
- H2: `clamp(2.8rem, 5vw, 5.2rem)`, line-height `0.94 - 1.0`, weight `850 - 900`
- H3: `clamp(1.8rem, 3vw, 3.6rem)`, line-height `0.96 - 1.05`, weight `850 - 900`
- Body large: `1.25rem - 1.6rem`, line-height `1.35 - 1.5`
- Body: `1rem`, line-height `1.5`
- Small labels: `0.78rem`, uppercase, weight `800`, letter-spacing `0`
- Buttons: `0.95rem - 1rem`, weight `800`

### Mobile Scale

- H1: `clamp(3.2rem, 16vw, 5.2rem)`, line-height `0.9`
- H2: `clamp(2.2rem, 12vw, 3.6rem)`, line-height `0.96`
- H3: `clamp(1.8rem, 9vw, 2.6rem)`, line-height `1.0`
- Body large: `1.08rem - 1.2rem`
- Body: `1rem`
- Buttons: min-height `46px`, full-width where stacked

Text must never rely on negative letter spacing.

## Components

### Header

- Sticky desktop header.
- Cream translucent background.
- Brand mark left; one-line nav right.
- Mobile can stack brand/nav but must remain compact.
- No heavy borders beyond one warm bottom line.

Existing files:

- `wp-content/themes/zvij-theme/header.php`
- `.site-header`, `.site-brand`, `.site-nav`

### Navigation

- Primary nav is plain text, no button pills by default.
- Active/hover state: primary black.
- Muted state: muted text.
- Avoid icons unless mockups require them.

### Primary Button

- Background: primary black.
- Text: cream.
- Radius: `6px - 10px`.
- Min-height: `46px`.
- Padding: `0.8rem 1.05rem`.
- Hover: green or black-darkened state.

### Secondary Button

- Transparent or cream panel background.
- Border: warm line.
- Text: primary black.
- Same height as primary button.

### Product Card

- Card background: cream card.
- Radius: `20px - 24px`.
- Border: warm line, low contrast.
- Product image dominates top area.
- Category label above title.
- Title strong, max 2-3 lines.
- Price clearly visible.
- Variable products use CTA `Select options`.
- Simple products use CTA `Add to cart` until checkout is disabled/reworded.
- Dobroimetje copy is small supporting text.

### Category Card

- Used for homepage product families or shop category navigation.
- Large image or strong color block, short title, 1 sentence.
- Avoid dense explanatory copy.

### Kit Selector

- Black/Silver/Gold are color choices for one Zvij.si Kit concept.
- Throwie is separate lower-cost utility setup.
- Keep one large image per kit/card.
- Minimal product labels only.
- Add-ons are secondary and unselected by default.
- Current toggle logic can remain but must feel secondary.

Existing files:

- `zvij_render_kit_showcase()` in `functions.php`
- `assets/kits.js`
- `.kit-*` CSS

### Trust Strip

- Short, horizontal trust/membership proof block.
- Use 3-4 concise facts max.
- Example topics: local/domestic handling, clear reload, dobroimetje, no medical claims.
- No dense legal copy.

### Membership Strip

- Headline: `Član Zvij.si`
- Supporting line: `Zvijače za zvijače.`
- Must explain Zvij koda, dobroimetje, reload, repeat orders in concise copy.
- Should sit as a warm strip/card, not as a form-heavy dashboard.

### Footer

- Cream/dark contrast section depending mockup.
- Include primary CTA, shop link, membership link, contact/about links.
- Current footer is simple and can remain as skeleton, but content and visual hierarchy likely need replacement.

### Section Heading

- Small green/gold label.
- Strong black headline.
- Optional one sentence.
- Left aligned by default.

### WooCommerce Filters

- Current kit filter chips can remain as behavior.
- Visual treatment should become closer to mockup: low chrome, warm pills, active black or gold.
- Data source: product tags.

### Variation Selector

- For SMOKEY/CHILLY/FRUTTY only one public product each.
- Variation attribute: `Pakiranje`
- Values: `1 g`, `5 g`
- Selector must be visually styled as part of product page, not browser default.
- Kit add-ons should target 1 g variations.

### Price Display

- Price must be direct and visible.
- Variable product price range format allowed: `8,00 € - 39,90 €`.
- FRUTTY first offer can keep badge `Prvič 4,20 €`.
- Dobroimetje copy secondary: `Član prejme X € za naslednji reload.`

## Current System To Preserve

- WooCommerce product sync and variable product logic.
- Product tag filters.
- Kit data in `zvij_kits` option.
- Kit add-on default to 1 g variations.
- DUBI video block.
- Generated temporary kit flat-lay images until final photography exists.

## Mockup Review Gate

Before frontend implementation, verify these files exist:

```text
docs/design/mockups/homepage.png
docs/design/mockups/shop.png
docs/design/mockups/kiti.png
docs/design/mockups/reload.png
docs/design/mockups/o-nas.png
```

If they are missing, implementation is blocked for visual fidelity. Do not improvise replacement layouts.
