# JollySafe Gold and Story Images

2026-10-16, development catalog.

## Reviewed Assets

- `assets/images/jollysafe-story-v2/jollysafe-gold-main-v2.jpg`: square main photo recolored from the existing silver main photo (#584, `catalog-v2/244.jpg`).
- `assets/images/jollysafe-story-v2/jollysafe-gold-gallery-v2.jpg`: matching landscape gallery photo recolored from silver #392. Both preserve the tapered body, thin cap, black seal and silver chain. Only tube and cap metal become gold.
- `assets/images/kits/gold-kit-real-v2.jpg`: corrected gold kit scene, replacing the inaccurate cylindrical tube with the tapered shape. Other kit components and configuration are retained.
- `assets/images/jollysafe-story-v2/zgodba-zvij-si-products-v2.jpg`: blog cover composed from the existing Smokey, Frutty, Chilly and DUBI 42 product references. No post text changes.

All four assets were made using the built-in image generation tool and converted to JPEG without cropping. Prompt set: gold main/gallery change metal color only, keep exact silver geometry and framing; gold kit replace only tube and correct the thin cap/edge loop; story compose actual three named pouches and DUBI filters on a single charcoal tabletop, no added captions, people, smoke or unrelated products. The initial gold kit correction retained an oversized knob and was rejected in favor of the second correction.

## Existing Variations Preserved

JollySafe remains parent #243 (`zvij-si-jollysafe`) with existing variations #718 black, #719 silver and #720 gold. No new product or variation was created. Gold's variation image and the merged parent gallery now use the corrected gold photos. Known inaccurate attachments #585, #393 and mixed-color #394 are removed from the visible parent gallery but not deleted. All black/silver photos, attributes, defaults, prices, SKUs, costs and inventory are retained.

`zvij_kit_flatlay_url()` prefers a real-v2 asset when present, otherwise keeps real-v1/legacy fallbacks. Only the gold kit has a v2 file. Kit content/options are unchanged. The shared theme mount makes this asset preference available wherever the theme is used; product and blog database updates are development only.

## Operations and Checks

`scripts/wp-refresh-jollysafe-story-images.php` is CLI/development-only. It validates the existing merged product and gold mapping, expected previous photos and article identity, backs up parent/variation data and the article thumbnail/content hash, imports media idempotently, assigns the gold image to the existing variation, and changes only article #86's thumbnail. Backups: `_zvij_jollysafe_gold_before_v2`, `_zvij_story_before_products_v2`.

`scripts/test-jollysafe-story-images.php` verifies all variation commerce/identity fields, retained black/silver images, removed incorrect gallery photos, kit photo choice and unchanged article text. `scripts/test-jollysafe-story-images.cjs` verifies desktop/mobile color switching, the merged shop card, kit image and blog cover, plus adding the actual gold variation through the product form and cleaning the isolated cart. No order is placed.
