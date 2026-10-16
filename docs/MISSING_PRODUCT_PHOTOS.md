# Missing Product Photos Resolved

Development catalog, 2026-10-16. Scope: published products #436, #439, #440 and #445 only. No theme layout, prices, names, stock, categories or production database changes.

## Sources

- Ziggi product identity and edition names: https://www.ziggioriginal.com/product/ziggi-original-special-editions/
- Mycelium closed designs: https://ziggioriginal-prod.s3.amazonaws.com/uploads/2024/09/DSC_0081-copy-1.webp
- Mycelium open packs: https://ziggioriginal-prod.s3.amazonaws.com/uploads/2024/09/DSC_0153-copy-scaled.webp
- Rocket closed designs: https://ziggioriginal-prod.s3.amazonaws.com/uploads/2024/09/DSC_0079-copy-1.webp
- Rocket open packs: https://ziggioriginal-prod.s3.amazonaws.com/uploads/2024/09/DSC_0156-copy-scaled.webp
- Hemp open pack: https://ziggioriginal-prod.s3.amazonaws.com/uploads/2023/08/HEMP-1-600x600-copy.webp
- Clipper 16 ml identity: https://www.shellshock420.com/en-us/products/clipper-butane-refill
- Clipper 16 ml photo: https://www.shellshock420.com/cdn/shop/files/clipper-butane-16ml-refill.jpg

Jaka confirmed 16 ml. The attached 100 ml can photograph was deliberately not used. Source URLs document provenance, not a grant of copyright permission. Brand/supplier image reuse rights should be confirmed for a public commercial launch, especially the retailer-sourced Clipper photograph.

## Treatment

Built-in image generation tool adapted three main backgrounds to charcoal studio tabletops. Selected prompts: edit background only, preserve actual packaging, logos and proportions, square full-bleed backdrop, no cropping, no props or invented volume labels. Ziggi references show four design examples of each edition, not a new four-pack product. A first single-Mycelium generation was rejected for inaccurate small label text and was not imported.

Open Ziggi gallery images and the Clipper source photograph are unmodified original files. The Clipper gallery is the same front view on its original white background, not a second camera angle. Hemp's existing main photograph is retained, with the manufacturer's open-pack view added.

Seven delivered files and `manifest.json` are in `wp-content/themes/zvij-theme/assets/images/missing-products-v1/`. The manifest records original image URLs and treatment per asset. Adapted main images are JPEG; originals preserve WebP transparency where supplied.

## Import and Verification

Run `php /var/www/html/scripts/wp-import-missing-product-images.php` inside `zvij-dev-wordpress`. The script refuses production and unexpected product identities/featured images, validates all source files before changes, preserves previous media IDs and commercial data in `_zvij_missing_media_v1_before`, and records source/treatment on attachments. Repeat imports reuse media, merge galleries and remove placeholders from assigned gallery lists without deleting attachments.

Run `scripts/test-product-image-completeness.php` in the container to check every published product has at least two distinct existing non-placeholder files, imported images are square and sourced, and affected commercial data remains unchanged. `scripts/test-missing-product-images.cjs` checks the four public product pages at 1440 and 390 px for loaded photos, gallery spacing, overflow and the 16 ml name. No order or user account is created.
