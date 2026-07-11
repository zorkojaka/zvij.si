# Slike produktov — kaj generirati / fotografirati in kam jih dati

Datum: 11. 7. 2026 · Vir resnice za slikovne potrebe kataloga.
Ta dokument je napisan tako, da lahko po njem **AI generira manjkajoče slike** in jih agent pravilno umesti. Realne fotografije imajo prednost — AI slika je vmesni nadomestek, dokler ni prave.

## Slogovni vodič za generiranje (obvezno za VSE nove slike)

Da bo katalog enoten, mora vsaka generirana slika slediti obstoječim realnim fotografijam:

- **Format:** kvadrat, najmanj 1200 × 1200 px, JPEG (kakovost ~85), brez napisov, logotipov tretjih znamk (razen dejanskih na izdelku), brez vodnih žigov.
- **Ozadje (dodatki/pribor):** topel kremast studio (barvni ton med `#f4eadb` in `#fff8ec`), mehka dnevna svetloba z leve, nežna senca pod izdelkom, rahel gradient. Izdelek zavzame ~70 % kadra, centriran, rahlo pod kotom (3/4 pogled).
- **Ozadje (vršički/pakiranja SMOKEY/CHILLY/FRUTTY sloga):** temno, moody, razpršeni vršički ob vrečki — TA SLOG JE ŽE POKRIT z realnimi fotografijami, ne generiraj.
- **Kit flat-layi:** pogled od zgoraj (top-down), vsi kosi kita razporejeni na enobarvnem ozadju v tonu kita (Black: skoraj črno #201d19; Silver: svetlo sivo #d7d2c9; Gold: toplo zlato #d9aa54), enakomerna svetloba, med kosi zrak.
- **Poimenovanje datotek:** `slug-izdelka.jpg` (ali `-2`, `-3` za galerijo), male črke, brez šumnikov.

## Umestitev (ukazi za agenta)

Novo sliko odloži v `data/slike/` v repozitoriju, nato:

```bash
docker compose run --rm wp-cli wp media import /var/www/html/data/slike/<datoteka>.jpg \
  --post_id=<ID> --featured_image --title="<Naziv izdelka>"
```

Za galerijo (brez `--featured_image`) dobljeni attachment ID pripni v meta `_product_image_gallery` (CSV ID-jev).

---

## 1. IMAJO REALNO FOTOGRAFIJO — ne generiraj ničesar ✅

| ID | Izdelek | Datoteka | Opomba |
|---|---|---|---|
| 71 | DUBI 42 | `2026/07/dubi-42.jpg` | realna, iz šuta 8. 7. |
| 69 | DUBI 420 | `2026/07/dubi-420.jpg` | realna |
| 74 (+308/309) | SMOKEY CBD vršički | `2026/07/smokey-1.jpg` | realna, temni slog |
| 72 (+310/311) | CHILLY CBG vršički | `2026/07/chilly-1.jpg` | realna |
| 76 (+312/313) | FRUTTY CBD vršički | `2026/07/frutty-1.jpg` | realna |
| 243 | JollySafe tulec Black | `2026/07/tulec-crn-1.jpg` | realna |
| 244 | JollySafe tulec Silver | `2026/07/tulec-srebrn-1.jpg` | realna |
| 245 | JollySafe tulec Gold | `2026/07/tulec-zlat-1.jpg` | realna |
| 211 | Clipper Black | `2026/07/BlackClipper.jpg` | realna |
| 213 | Clipper Gold | `2026/07/zlatClipper.jpg` | realna |
| 227 | Clipper Silver | `2026/07/SilverClipper.jpg` | realna |
| 218 | Zvij.si Mini Grinder 5 cm (črn) | `2026/07/grinder-crn.jpg` | realna |
| — | Hero domača stran | tulci hero | realna |

## 2. GENERIRAJ (AI) — izdelek je/bo v prodaji, slike ni

> Prompt sestavi iz slogovnega vodiča + spodnjega opisa motiva.

| ID | Izdelek (slug) | Motiv za generiranje | Ciljna datoteka |
|---|---|---|---|
| 217 | Silver grinder (`silver-grinder-placeholder`) | mat srebrn aluminijast 4-delni grinder ø5 cm, zaprt, 3/4 pogled, kremasto ozadje | `silver-grinder.jpg` |
| 216 | Gold grinder (`gold-grinder-placeholder`) | mat zlat aluminijast 4-delni grinder ø5 cm, enak kader kot srebrni | `gold-grinder.jpg` |
| 332 | RAW Rolls (`raw-rolls`) | RAW Classic rolls škatlica z delno izvlečenim rjavim papirjem, kremasto ozadje | `raw-rolls.jpg` |
| 333 | Ziggi Rolls (`ziggi-rolls`) | Ziggi rolls škatlica (bela/oranžna), delno izvlečen papir, kremasto ozadje | `ziggi-rolls.jpg` |
| 226 | Throwie Bag (`throwie-bag`) | majhna črna platnena vrečka z vrvico (setup pouch), rahlo odprta, kremasto ozadje | `throwie-bag.jpg` |
| 246 | Rolling tray (`matching-rolling-tray-placeholder`) | mat črn kovinski rolling pladenj z zaobljenimi robovi, prazen, rahlo od strani | `rolling-tray.jpg` |

## 3. ZAMENJAJ NIZKO LOČLJIVE dobaviteljeve sličice (200 × 200) — generiraj ali fotografiraj

Trenutne so premajhne za polno-krvave kartice (raztegnjene čez 300+ px pas).

| ID | Izdelek (slug) | Motiv | Ciljna datoteka |
|---|---|---|---|
| 222 | Smoking Black Rolls | Smoking Black rolls škatlica (črna), izvlečen papir | `smoking-black-rolls.jpg` |
| 223 | Smoking Silver Rolls | Smoking Silver škatlica (srebrna) | `smoking-silver-rolls.jpg` |
| 224 | Smoking Brown Rolls | Smoking Brown škatlica (rjava, unbleached) | `smoking-brown-rolls.jpg` |
| 225 | SmK Gold Rolls | SmK zlata rolls škatlica | `smk-gold-rolls.jpg` |
| 221 | SmK Gold Papers + Tips | SmK zlati papirčki + filter tips, oba v kadru | `smk-gold-papers.jpg` |
| 220 | JaJa Noir Black | JaJa Noir črna škatlica papirčkov | `jaja-noir.jpg` |
| 219 | IRIE Xtra Light | IRIE king size slim škatlica | `irie-rolls.jpg` |
| 214 | Cheap fajrji HEMP | preprost vžigalnik s hemp vzorcem | `hemp-fajr.jpg` |

## 4. KIT FLAT-LAYI — zamenjaj AI z realnimi, ko bo roba (tema, ne produkti)

Datoteke v `wp-content/themes/zvij-theme/assets/images/kits/`:

| Datoteka | Trenutno | Motiv (flat-lay slog zgoraj) |
|---|---|---|
| `black-kit-flatlay.png` | AI | tulec + Clipper + grinder + rolls + DUBI vrečka, vse črno, top-down |
| `silver-kit-flatlay.png` | AI | isti razpored, srebrni kosi |
| `gold-kit-flatlay.png` | AI | isti razpored, zlati kosi |
| `throwie-kit-flatlay.png` | AI | vrečka + fajr + grinder + Ziggi + DUBI |

Posterji z vpečenim besedilom (`crn/srebrn/zlat-tulec.png`) so rezervirani za promo bloke — NE za produktne galerije.

## 5. GALERIJE (nižja prioriteta, realne fotke ob priložnosti)

Za objavljene izdelke z eno samo sliko dodaj po 2 dodatna kadra (detajl + v roki/uporaba): DUBI 42/420, tulci, grinder. Vršički galerije že obstajajo.

---

## Status aktivacije kataloga (11. 7. 2026)

Za pravi občutek strani so kot aktivni objavljeni tudi izdelki z **DEV cenami** (cene pred produkcijo potrdi Jaka — postavka #5/#10 v RELEASE_PLAN checklistu): Clipper Silver 24,00 / Mini Grinder črn 14,90 / Silver in Gold grinder 14,90 / RAW Rolls 1,90 / Ziggi 1,50 / Smoking ×3 1,50 / SmK Gold Rolls 2,50 / SmK Papers+Tips 2,90 / JaJa 1,90 / IRIE 1,50 / Hemp fajr 1,50 / Throwie Bag 9,90. Neaktivni ostajajo: dev-placeholder-*, sample/zvij-setup paket, kit-tube, clipper-standard/premium/gradient, rezervni vžigalnik, Champ grinder, rolling tray (matrix: »later«).
