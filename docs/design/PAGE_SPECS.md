# Zvij.si Page Specifications

Status: design-first implementation specification draft.

Mockup status: blocked. The expected `docs/design/mockups/*.png` files are not present in the current checkout, so this document captures the known approved hierarchy from project instructions and the current implementation audit. Reconcile section dimensions and exact visual order against the PNG mockups before implementation.

## Shared Page Rules

- Do not place full mockup screenshots on the website.
- Do not use mockup PNGs as production assets.
- Use real WooCommerce product data where the page refers to products.
- Preserve working catalog, variable products, shop filters, and kit selection logic.
- Keep copy short.
- Keep product/kit imagery large.
- Avoid medical CBD claims and smoking instructions.

## Homepage

### Required Hierarchy

1. Hero
2. Main products
3. Kiti Zvij.si
4. Reload
5. Membership/trust content
6. Footer

### Hero

Content:

- Brand statement:

```text
Tvoj vajb.
Tvoja rutina.
Tvoj lajf.
Tvoja pravila.
```

- Supporting copy: concise, domestic premium, product-culture friendly.
- Primary CTA: shop or products.
- Secondary CTA: kiti or membership.
- Asset: large lifestyle photography, not a screenshot or UI mockup.

Desktop:

- Max width `1280px`.
- Hero should occupy first viewport strongly.
- Copy/image ratio likely `45/55` or image-dominant depending mockup.
- H1 must be the strongest text on the page.

Mobile:

- Stack image and copy.
- Buttons full-width only if needed.
- H1 must fit without clipping.

Acceptance criteria:

- Hero does not use the current "Vse, kar rabiš..." kit-first headline.
- Uses the approved four-line brand statement.
- Does not introduce extra sections beyond the specified hierarchy.

### Main Products

Products:

- CBD/CBG vršički
- DUBI filtri

Component: `MainProductFeature`

Data source:

- WooCommerce category `CBD/CBG vršički`
- WooCommerce category `DUBI filtri`
- Featured product images from WooCommerce or final owned photography

Desktop:

- Two large product/category cards or two editorial product blocks.
- Image-to-copy ratio at least `60/40`.

Mobile:

- Stack cards.
- Image first, concise copy below.

Acceptance criteria:

- CBD/CBG is not named `CBD čaj`.
- DUBI is clearly a core product family.
- Links route to shop/category or relevant page.

### Kiti Zvij.si

Concept:

- One product concept: Zvij.si Kit.
- Black, Silver, Gold are color variants/selections of the same kit.
- Throwie is separate lower-cost setup.

Component: `KitShowcase`

Current reusable logic:

- `zvij_kits` option from `scripts/wp-sync-catalog-dev.php`
- `zvij_render_kit_showcase()` in theme
- `assets/kits.js` toggle/selection behavior

Desktop:

- Large flat-lay images.
- Minimal labels.
- Compact CTA.
- Do not return to admin/configurator product grids.

Mobile:

- One kit card at a time in vertical stack or mockup-defined carousel.
- Product labels wrap naturally.

Acceptance criteria:

- Do not present Black/Silver/Gold as `Diskreten kit`, `Darilni kit`, `Čist kit` categories.
- They may have short mood labels but must remain color selections.
- Throwie is visibly separate from the main three-color kit.

### Reload

Component: `ReloadStrip` or `ReloadFeature`

Content:

- Explain next reload, dobroimetje, repeat order, and "ko ti zmanjkuje".
- Avoid `refill` language.
- Copy examples:
  - `Ko ti zmanjkuje, ne iščeš znova. Samo reload.`
  - `Dobroimetje za naslednji reload.`

Data source:

- Product metadata `_zvij_dobroimetje_note`
- Future repeat-order logic from `docs/REPEAT_ORDER_SPEC.md`

Acceptance criteria:

- Does not imply customer returns empty packaging.
- Does not configure payments/shipping.

### Membership / Trust

Component: `MembershipStrip` plus optional `TrustStrip`

Content:

- `Član Zvij.si`
- CTA and heading: `Postani član` / `Postani član Zvij.si`
- Email content series after signup: `Zvijače za zvijače`
- Zvij koda
- dobroimetje
- reload
- repeat order

Acceptance criteria:

- Do not use `Zvijače za zvijače` as the signup CTA or membership name.
- Do not use `Insiderske novice`, `Newsletter`, `Subscribe`, or `20% popust`.
- Not MLM.
- No resale or payout promises.
- Tone adult, witty, domestic.

## Shop

Component/template:

- `archive-product.php`
- WooCommerce loop
- product tag filter chips

Data source:

- Real WooCommerce products.
- Current public expected products:
  - `SMOKEY CBD vršički` variable `1 g / 5 g`
  - `CHILLY CBG vršički` variable `1 g / 5 g`
  - `FRUTTY CBD vršički` variable `1 g / 5 g`
  - DUBI 42
  - DUBI 420
  - Sample/Zvij setup only if dev placeholders remain approved

Desktop:

- Product grid: likely 3 columns at `1280px`.
- Product card image should dominate.
- Filters should be visible but low chrome.

Mobile:

- 1 column cards.
- Filters wrap as compact chips.
- Sort dropdown should not dominate.

Product Card Behavior:

- Variable products show `Select options`.
- No duplicate 1 g / 5 g public simple products.
- Price range visible for variables.
- Category label visible.
- Dobroimetje note secondary.

Acceptance criteria:

- Public shop must not display separate SMOKEY/CHILLY/FRUTTY 1 g and 5 g duplicate products.
- Product cards remain linked to WooCommerce data.
- Filters still work.

## Kiti Page

Likely target slug: `/kiti/` or page title `Kiti`.

Mockup file expected:

```text
docs/design/mockups/kiti.png
```

Page structure:

1. Hero explaining Zvij.si Kit as one concept.
2. Main three-color kit selector: Black / Silver / Gold.
3. What's included.
4. Optional CBD/CBG vršički add-ons.
5. Throwie section as separate lower-cost setup.
6. CTA to order/select kit.

Data source:

- `zvij_kits` option.
- Draft kit component products for components.
- Published DUBI 42 and 1 g vršički variations.

Desktop:

- Black/Silver/Gold should appear together.
- Color choice interaction should be prominent enough to understand, but not admin-like.
- Flat-lay or final product photography required.

Mobile:

- Stacked selector or tabs.
- Keep the CTA reachable.

Acceptance criteria:

- Black/Silver/Gold are color choices of one kit concept.
- Throwie is separate.
- Current kit toggle logic may be reused only if visually simplified to match mockup.

## Reload Page

Likely target slug: `/reload/`.

Mockup file expected:

```text
docs/design/mockups/reload.png
```

Page structure:

1. Hero: reload as the repeat-order/dobroimetje system.
2. How it works: choose setup, earn/use dobroimetje, repeat.
3. Product families tied to reload: DUBI, CBD/CBG vršički.
4. Membership tie-in.
5. CTA to shop or membership.

Data source:

- Product meta `_zvij_dobroimetje_note`
- Membership/repeat docs:
  - `docs/MEMBERSHIP_SPEC.md`
  - `docs/DOBROIMETJE_STRATEGY.md`
  - `docs/REPEAT_ORDER_SPEC.md`

Acceptance criteria:

- Uses `reload`, not `refill`.
- Does not imply packaging return/refill.
- Does not implement checkout/payment changes.

## O nas Page

Likely target slug: `/o-nas/`.

Mockup file expected:

```text
docs/design/mockups/o-nas.png
```

Page structure:

1. Brand story hero.
2. Domestic premium positioning.
3. What Zvij.si curates: vršički, DUBI, kits, reload.
4. Trust/legal-care paragraph.
5. CTA to shop/kiti.

Tone:

- domač premium
- svetla stran
- domači kompanjon, ki zrihta robo
- humor in copy, not visual clutter

Acceptance criteria:

- No medical claims.
- No "legal high" framing.
- No HHC as active offer.

## Existing Pages To Reconcile

Current generated pages:

- `Domov`
- `Trgovina`
- `Član Zvij.si`
- `DUBI filtri`
- `CBD vršički`
- `Zvij setup`
- `Kontakt`

New mockup pages expected:

- Homepage
- Shop
- Kiti
- Reload
- O nas

Implementation must decide whether to:

- add new pages `Kiti`, `Reload`, `O nas`,
- keep current static pages for SEO/support,
- adjust menu order to match mockups.

This decision should happen after mockups are available.
