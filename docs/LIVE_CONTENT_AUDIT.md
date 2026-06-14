# Live Zvij.si Content Audit

Source site: https://zvij.si  
Audit date: 2026-06-14  
Scope: public pages only. Live site was used as a read-only content source.

## Products

| Product | Source URL | Category | Regular price | Sale price | Image URL | Dev import status | Risk notes |
| --- | --- | --- | ---: | ---: | --- | --- | --- |
| DUBI - 420 Aktivnih ogljikovih filtrov | https://zvij.si/izdelek/dubi-420-aktivnih-ogljikovih-filtrov/ | Pripomočki | 75.00 EUR | 67.50 EUR | https://zvij.si/wp-content/uploads/2023/10/dubi-front.png | Published on dev | Live copy includes strong respiratory/smoke wording. Dev public short copy is softened and should still get final legal review. |
| DUBI - 42 Aktivnih ogljikovih filtrov | https://zvij.si/izdelek/dubi-aktivni-ogljikovi-filtri-42-kosov/ | Pripomočki | 7.75 EUR | 6.50 EUR | https://zvij.si/wp-content/uploads/2023/10/dubi-front.png | Published on dev | Live copy includes strong respiratory/smoke wording. Dev public short copy is softened and should still get final legal review. |
| CHILLY - Premium CBG | https://zvij.si/izdelek/chilly-premium-cbg/ | Vršički / CBG | 7.50 EUR | 6.50 EUR | https://zvij.si/wp-content/uploads/2023/06/chilly-frontside.png | Draft on dev | CBG flower copy needs legal/product review before publication. |
| SMOKEY - Premium CBD | https://zvij.si/izdelek/smokey-premium-cbd/ | Vršički / CBD | 8.00 EUR | 7.20 EUR | https://zvij.si/wp-content/uploads/2023/06/smokey-frontside.png | Draft on dev | Live copy implies inhalation and wellness effects. Keep as draft until rewritten as CBD čaj / ritual copy. |
| FRUTTY - CBD | https://zvij.si/izdelek/frutty-cbd/ | Vršički / CBD | 5.00 EUR | 4.20 EUR | https://zvij.si/wp-content/uploads/2023/06/frutty-frontside.png | Draft on dev | Live copy implies relaxation/wellness effects. Keep as draft until rewritten as CBD čaj / ritual copy. |

## Extra Product Media

Useful public DUBI media found on product pages:

- https://zvij.si/wp-content/uploads/2023/10/Dubi_filters.png
- https://zvij.si/wp-content/uploads/2023/10/dubi-front.png
- https://zvij.si/wp-content/uploads/2023/10/dubi_back.png
- https://zvij.si/wp-content/uploads/2023/10/dubi3.png
- https://zvij.si/wp-content/uploads/2023/10/Graf_pretocnosti_zraka-1.jpg

Product featured images found:

- CHILLY: https://zvij.si/wp-content/uploads/2023/06/chilly-frontside.png
- SMOKEY: https://zvij.si/wp-content/uploads/2023/06/smokey-frontside.png
- FRUTTY: https://zvij.si/wp-content/uploads/2023/06/frutty-frontside.png

## Video Links

- DUBI product embed: https://www.youtube.com/embed/5oNlpY17v9w
- Homepage video module: https://vimeo.com/65924804

The YouTube embed is the more product-specific video and is used on dev DUBI product pages. The Vimeo URL is documented as a legacy homepage video reference.

## Blog Posts

| Title | Source URL | Source date | Dev import status | Risk notes |
| --- | --- | --- | --- | --- |
| Kaj je CBD: Od Prvinske Rastline do Sodobnih Možnosti | https://zvij.si/raziskovanje-cbd-od-prvinske-rastline-do-sodobnih-moznosti/ | 2023-05-20 | Draft | CBD educational content needs review for wellness/medical claims. |
| CBG: Skrivnostni Kanabinoid z Velikim Potencialom | https://zvij.si/cbg-skrivnostni-kanabinoid-z-velikim-potencialom/ | 2023-06-20 | Draft | CBG potential/effects wording needs review. |
| HHC: Skrivnostni kanabinoid, ki odklepa sproščanje | https://zvij.si/hhc-skrivnostni-kanabinoid-ki-odklepa-sproscanje/ | 2023-07-20 | Draft | HHC is historical only and must not be promoted as a current offering. |
| Primerjava kanabinoidov: CBD, CBG, THC in HHC | https://zvij.si/primerjava-kanabinoidov-cbd-cbg-thc-in-hhc/ | 2023-08-20 | Draft | Includes THC/HHC and possible medical/wellness claims. Needs rewrite before publication. |
| Zvij.si: Vaš sopotnik na poti do dobrega počutja brez THC-ja! | https://zvij.si/zvij-si-vas-sopotnik-na-poti-do-dobrega-pocutja-brez-thc-ja/ | 2023-04-20 | Draft | Launch/history post has broad health/wellness claims and outdated product direction. |

## Import Notes

The dev import script is `scripts/wp-import-live-content-dev.sh`. It is intentionally idempotent:

- products and posts are matched by `imported_from_live_url`
- featured images are matched by `_zvij_source_image_url`
- import status is stored in `import_status`
- legal review flag is stored in `legal_copy_review_needed`

Live `zvij.si` is not modified by the script.
