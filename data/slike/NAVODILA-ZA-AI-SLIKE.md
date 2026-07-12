# Navodila za AI generiranje slik — Zvij.si

Datum: 12. 7. 2026 · Vse potrebne slike v enem dokumentu.
Vsaka postavka ima **samostojen angleški prompt** (image AI modeli delujejo najbolje v angleščini) in točno mesto umestitve. Generirane datoteke shrani **v to mapo** (`data/slike/`) s predpisanim imenom — agent jih nato umesti z ukazi na dnu.

Na strani so izdelki brez slike trenutno pokriti z **označenimi placeholderji** (kremast okvir z napisom »SLIKA V PRIPRAVI«) — vsaka spodnja slika enega od njih nadomesti.

---

## Skupni slog (velja za VSE slike razdelkov A in B)

> **Base style prompt (pripni vsakemu promptu spodaj):**
> "Professional e-commerce product photography, single product centered on a warm cream studio background (soft gradient from #f4eadb to #fff8ec), soft natural daylight from the left, subtle soft shadow under the product, slight 3/4 angle, product fills about 70% of the frame, photorealistic, sharp focus, no text, no watermark, no logos except those physically printed on the product, square format 1200x1200."

Format: kvadrat ≥1200×1200, JPEG. Brez ljudi, brez dima, brez prižganih izdelkov, brez konoplje v kadru (razdelka A/B sta pribor).

---

## A. Izdelki brez slike (zdaj placeholder na strani)

| # | Datoteka | Prompt (dodaj base style) | Umesti na |
|---|---|---|---|
| A1 | `silver-grinder.jpg` | "A matte silver anodized aluminum herb grinder, 4-piece, 5 cm diameter, closed, clean brushed metal texture with fine knurled grip edge" | izdelek ID 217 (Silver grinder) |
| A2 | `gold-grinder.jpg` | "A matte gold anodized aluminum herb grinder, 4-piece, 5 cm diameter, closed, clean brushed metal texture with fine knurled grip edge, same framing as a silver version" | izdelek ID 216 (Gold grinder) |
| A3 | `raw-rolls.jpg` | "A box of RAW Classic unbleached rolling paper rolls, brown kraft cardboard box with the roll of thin brown paper partially pulled out, iconic RAW brand box design" | izdelek ID 332 (RAW Rolls) |
| A4 | `ziggi-rolls.jpg` | "A box of Ziggi rolling paper rolls, white and orange box with a roll of thin white paper partially pulled out" | izdelek ID 333 (Ziggi Rolls) |
| A5 | `throwie-bag.jpg` | "A small black canvas drawstring pouch (everyday-carry setup bag), slightly open showing dark interior, matte black cotton cord, compact and minimal" | izdelek ID 226 (Throwie Bag) |
| A6 | `rolling-tray.jpg` | "A matte black metal rolling tray with rounded corners and raised edges, empty, viewed from a slight angle" | izdelek ID 246 (tray — zdaj draft; umesti ob objavi) |

## B. Zamenjava nizko ločljivih dobaviteljevih sličic (200×200 → 1200×1200)

Trenutne slike so premajhne in raztegnjene. Motiv naj bo POSAMEZNA škatlica/izdelek (ne prodajni display z več kosi!).

| # | Datoteka | Prompt (dodaj base style) | Umesti na |
|---|---|---|---|
| B1 | `smoking-black-rolls.jpg` | "A single box of Smoking Deluxe black rolling paper rolls, elegant matte black slim cardboard box, roll of ultra-thin paper partially pulled out" | ID 222 |
| B2 | `smoking-silver-rolls.jpg` | "A single box of Smoking Silver rolling paper rolls, metallic silver slim cardboard box, roll of thin white paper partially pulled out" | ID 223 |
| B3 | `smoking-brown-rolls.jpg` | "A single box of Smoking Brown unbleached rolling paper rolls, natural kraft brown slim cardboard box, roll of thin brown paper partially pulled out" | ID 224 |
| B4 | `smk-gold-rolls.jpg` | "A single box of SmK gold rolling paper rolls, shiny gold slim cardboard box, roll of thin paper partially pulled out" | ID 225 |
| B5 | `smk-gold-papers.jpg` | "A pack of SmK gold king size rolling papers next to a matching pack of paper filter tips, both gold-branded, arranged side by side slightly overlapping" | ID 221 |
| B6 | `jaja-noir.jpg` | "A single pack of JaJa Noir black king size slim rolling papers, minimal elegant black box" | ID 220 |
| B7 | `irie-rolls.jpg` | "A single pack of IRIE extra light king size slim rolling papers, green red and yellow reggae-styled box" | ID 219 |
| B8 | `hemp-fajr.jpg` | "A single disposable pocket lighter with a natural hemp-leaf pattern wrap, standing upright" | ID 214 |

## C. Kit flat-layi (tema, ne produkti)

> **Flat-lay base style:** "Top-down flat lay photography, evenly lit, items arranged with generous spacing on a solid color background, photorealistic, no text, no hands, square format 1600x1600."

Vsak flat-lay vsebuje NATANKO teh 5 kosov: metal joint tube (tulec), Clipper-style lighter, 4-piece herb grinder ø5 cm, box of paper rolls, small kraft paper pouch labeled "dubi".

| # | Datoteka | Prompt (dodaj flat-lay base) | Umesti v |
|---|---|---|---|
| C1 | `black-kit-flatlay.png` | "…all items in matte black, on a near-black warm charcoal background (#201d19), subtle contrast between black finishes" | `wp-content/themes/zvij-theme/assets/images/kits/black-kit-flatlay.png` |
| C2 | `silver-kit-flatlay.png` | "…all items in brushed silver/aluminum, kraft pouch stays natural brown, on a light warm gray background (#d7d2c9)" | `…/silver-kit-flatlay.png` |
| C3 | `gold-kit-flatlay.png` | "…all items in matte gold, kraft pouch stays natural brown, on a warm golden background (#d9aa54)" | `…/gold-kit-flatlay.png` |
| C4 | `throwie-kit-flatlay.png` | "small black canvas drawstring pouch, simple hemp-pattern lighter, black mini grinder, Ziggi paper rolls box and a small kraft dubi pouch, on an olive-green background (#5c6650)" | `…/throwie-kit-flatlay.png` |

## D. Galerije (nižja prioriteta)

Po 2 dodatna kadra za: DUBI 42/420, tulce, grinder — detajl od blizu + izdelek v kontekstu setupa (na leseni mizi z rolling trayem). Isti base style; datoteke `slug-2.jpg`, `slug-3.jpg`.

---

## Umestitev (za agenta, ko so datoteke v tej mapi)

Razdelka A in B (produktne slike):

```bash
cd /home/jaka/apps/zvijsi/zvij.si
docker compose run --rm wp-cli wp media import /var/www/html/data/slike/<datoteka>.jpg \
  --post_id=<ID> --featured_image --title="<Naziv izdelka>"
```

Star placeholder/sličico po uvozu izbriši (`wp post delete <stari_attachment_id> --force`); placeholderji so označeni z attachment meta `_zvij_placeholder=1`. Po umestitvi nastavi `_zvij_image_kind` na `ai_render` (oz. `real_photo` za prave fotografije) in posodobi `docs/SLIKE_PRODUKTOV.md`.

Razdelek C: datoteke samo prepiši v `wp-content/themes/zvij-theme/assets/images/kits/` (ista imena) in commitaj.
