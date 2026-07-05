# Zvij.si — Release plan in gap analiza

Datum: 5. 7. 2026
Veja: `chore/docker-wordpress-dev` · Dev: https://dev.inteligent.si

Ta dokument je operativni vir resnice za pot do prve prodajne verzije. Nadgrajuje handoff dokument (1. 7. 2026); zaklenjene odločitve iz handoffa ostajajo v veljavi.

---

## 1. Potrjeno trenutno stanje (5. 7. 2026)

### Deluje in je preverjeno (E2E test s testnim naročilom #369)

- **Celoten nakupni tok:** izdelek → košarica → blagajna → naročilo → stran »naročilo prejeto«. Preverjen z DUBI 42, plačilo po predračunu, skupni znesek pravilen (izdelek 7,99 € + dostava 3,90 €).
- **Katalog:** DUBI 42, DUBI 420, SMOKEY/CHILLY/FRUTTY kot variabilni izdelki 1 g / 5 g s cenami in slikami. Homepage carousel (tag `homepage-carousel`) deluje.
- **Dostava:** cona Slovenija — dostava 3,90 €, brezplačna nad 40 € (PREDLOG, glej odprta vprašanja).
- **Plačila (dev):** predračun/nakazilo (BACS) in po povzetju (COD). Revolut/kartice čakajo na produkcijske ključe.
- **Pravne strani:** Politika zasebnosti, Vračila in reklamacije, Pogoji poslovanja (18+, cene z DDV, dostava, CBD disclaimer) — objavljene in povezane s checkoutom (obvezno soglasje).
- **Član Zvij.si:** obrazec (homepage/footer), soglasje, kupon 10 % (enkraten, 30 dni, vezan na email), welcome email (dev pregled v adminu), checkout opt-in, ob nakupu se član označi kot `customer`. MailerLite sinhronizacija je pripravljena, čaka na API ključ.
- **Operativni admin (novo, zvij-core 0.3.0):**
  - meni **Zvij.si** → operativni pregled: prihodek danes/7/30 dni, št. naročil, povprečno naročilo, člani, naročila za obdelavo, top izdelki, opozorila o zalogi, novi člani;
  - **natisljiv odpremni dokument** (naslovnica za paket + seznam za pakiranje s SKU in checkboxi, COD znesek) — gumb na seznamu naročil, na naročilu in na dashboardu;
  - **status naročila »Pripravljeno za odpremo«** (`wc-zvij-ready`) + bulk akcija; šteje kot plačan status v statistiki.
- **Checkout UX:** klasični (shortcode) cart/checkout/moj račun, stilizirani v brand smeri (dvokolonska blagajna s povzetkom naročila, mobilna postavitev). Prehod z block checkouta je bil nujen, ker block checkout ne podpira obstoječih hookov (opt-in, member status).
- **Jezik:** nameščen sl_SI za WordPress in WooCommerce (checkout, emaili, statusi v slovenščini).
- **Očiščen javni katalog:** »Sample paket« in »Zvij setup paket« (DEV placeholderja) umaknjena v draft. Javno je 5 realnih izdelkov.
- **Izdaja računov (ZVIJ-08, zvij-core 0.4.0):** email kupcu o naročilu (processing / on-hold predračun / pripravljeno / completed) vsebuje računsko glavo — »Račun št. {št. naročila}«, datum izdaje, izdajatelj (Zvij.si d.o.o., naslov trgovine, zvijace@zvij.si, TRR/davčna če vpisani) in opombo o DDV; WooCommerce že izpiše postavke in znesek. HTML + plain-text. Ni davčno/fiskalno zaporedno številčenje — pravi računovodski sistem je kasnejša naloga (`includes/zvij-invoice.php`).

### Znane omejitve dev okolja

- **Email dostava:** dev nima SMTP — emaili se ne dostavijo (vsebina welcome emaila je vidna v adminu). Za produkcijo obvezen SMTP ali transakcijski ponudnik.
- Testno naročilo #369 (test-narocilo@example.com) je ostalo v bazi kot demo za dashboard — pred preklopom na produkcijo počistiti.
- Draft izdelki (tulci, grinderji, vžigalniki, rolce, kiti) čakajo na cene/fotografije/nabavo — pravilno so skriti.

---

## 2. Gap analiza — kaj še manjka za lansiranje

### MUST LAUNCH (blokira odprtje trgovine)

| # | Vrzel | Kdo | Status |
|---|-------|-----|--------|
| 1 | Produkcijsko plačilo s kartico (Revolut ali WooPayments) — namestitev vtičnika, sandbox test, produkcijski ključi | Jaka (ključi) + agent (integracija) | blokirano na Jaka |
| 2 | SMTP / transakcijski email (potrditve naročil, welcome email) | Jaka (račun) + agent | blokirano na Jaka |
| 3 | MailerLite: račun, skupina »Člani Zvij.si«, API ključ + group ID v env | Jaka + agent | blokirano na Jaka |
| 4 | zvijace@zvij.si + SPF/DKIM/DMARC | Jaka (cPanel/DNS) | blokirano na Jaka |
| 5 | Realne fotografije: grinder, tulci, kit flat-layi, hero | Jaka (fotografiranje) | blokirano na Jaka |
| 6 | Podatki podjetja: TRR za predračun, naslov trgovine, davčni status (DDV zavezanec?) | Jaka | blokirano na Jaka |
| 7 | Potrditev cen dostave in praga brezplačne dostave | Jaka (odločitev) | predlog vpisan (3,90 € / 40 €) |
| 8 | Izdaja računov (Woo email z računom zadošča za start; pravi računovodski sistem kasneje) | odločitev | ✅ urejeno (ZVIJ-08, zvij-core 0.4.0) — račun v email kupcu; poln naslov/TRR/davčna se izpišejo, ko so podatki podjetja (#6) vpisani |
| 9 | Migracija dev → zvij.si (backup, DNS, search-replace, test) | agent + Jaka potrditev | pripravljeno v DEPLOY_DEV.md konceptu |
| 10 | Pravni pregled pogojev/zasebnosti (osnutki so vpisani) | Jaka | osnutek pripravljen |

### LAHKO POČAKA (po prvem lansiranju)

- Kiti kot kupljivi bundle produkti (zdaj showcase; blokirano na nabavo komponent in cene)
- Reload email avtomatika, segmentacija ponovnih nakupov
- Zapuščene košarice, GA4/Plausible analitika in campaign attribution
- Dobroimetje / referral sistem
- Slovenski permalinki (`/izdelek/`, `/kosarica/`) — zdaj `/product/`, `/cart/` (delujejo)
- CBD kapljice, širši katalog
- Mailpit v docker-compose za lokalni email test

---

## 3. Kaj mora narediti Jaka (ročno, po prioriteti)

1. **Fotografiraj** grinder in tulce (navodila v PRODUCT_IMAGE_PLAN.md) — največji vizualni dvig.
2. **Odpri MailerLite** račun in ustvari skupino »Člani Zvij.si«; API ključ pošlji varno (env, ne Git).
3. **Ustvari zvijace@zvij.si** in uredi SPF/DKIM/DMARC DNS zapise.
4. **Odloči:** prag brezplačne dostave (predlog 40 €), cena dostave (predlog 3,90 €), DDV status podjetja, TRR za predračun.
5. **Revolut/WooPayments:** potrdi ponudnika kartic in priskrbi sandbox + produkcijske ključe.
6. **Preveri pravna besedila** (pogoji, zasebnost, vračila) — vpisani so razumni osnutki, niso pravno pregledani.

## 4. Agent-ready backlog (naslednje naloge za Codex/agenta)

1. **SMTP integracija** — ko Jaka izbere ponudnika: WP Mail SMTP ali wp_mail phpmailer konfiguracija prek env; test potrditvenih emailov.
2. **Revolut gateway** — namestitev v dev, sandbox konfiguracija prek env, testni nakup s kartico.
3. **MailerLite aktivacija** — vpis ključev v env, test sinhronizacije člana, unsubscribe webhook (secret že podprt v zvij-core).
4. **Fotografije v katalog** — ko so posnete: nadomesti AI flat-laye, product gallery za grinder/tulce, hero kompozicija.
5. **Kit kot kupljiv produkt** — ko so komponente in cene: grouped/bundle pristop, Black kit prvi.
6. **Analitika** — Plausible (samohostan na Hetznerju) ali GA4; event model: view_item, add_to_cart (carousel source že v DOM), begin_checkout, purchase.
7. **Produkcijska migracija** — runbook: backup live, izvoz dev baze, search-replace dev.inteligent.si → zvij.si, uploads sync, SSL, smoke testi, rollback plan.
8. **Čiščenje pred produkcijo** — izbriši testno naročilo #369 in testne člane, preveri da so vsi draft izdelki skriti, izklopi WP_DEBUG.

## 5. Release checklist (pred preklopom na zvij.si)

- [ ] Kartično plačilo testirano v sandboxu in preklopljeno na produkcijo
- [ ] SMTP: potrditveno naročilo prispe v pravi nabiralnik
- [ ] MailerLite: prijava člana konča v skupini, welcome email dostavljen, odjava deluje
- [ ] Realne fotografije vsaj za DUBI, vršičke, grinder, tulce
- [ ] Cene in zaloge potrjene za vse javne izdelke
- [ ] Pravi testni nakup od začetka do konca (pravi denar, majhen znesek)
- [ ] Odpremni dokument natisnjen in preverjen na papirju
- [ ] Backup live strani + baze pred migracijo
- [ ] DNS/SSL preklop izven prometnih ur, smoke testi po preklopu
- [ ] Mehko lansiranje: link prijateljem in obstoječim DUBI kupcem, IG objava

## 6. Odprta vprašanja (potrebna Jakova odločitev)

1. Prag brezplačne dostave: vpisan predlog 40 € — potrdi ali spremeni (WooCommerce → Dostava → Slovenija).
2. Cena dostave 3,90 € in ponudnik (Pošta Slovenije / GLS / DPD?) — vpliva na format naslovnice.
3. Po povzetju: obdržati na produkciji? (višji strošek, a dviguje konverzijo v SLO)
4. DDV: ali je Zvij.si d.o.o. zavezanec? (zdaj cene z vključenim DDV, davki izklopljeni)
5. ~~Računi: Woo email dovolj za start ali takoj povezava z računovodskim sistemom?~~ **Odločeno (ZVIJ-08): Woo email z računom zadošča za start** — implementirano v zvij-core 0.4.0. Računovodski sistem kasneje. Jaka naj vpiše podatke podjetja (#6: naslov trgovine, TRR prek `zvij_invoice_trr`, davčna prek `zvij_invoice_tax_id`), da je izdajatelj na računu popoln.
6. Silver kit v prvi val ali kasneje (odvisno od nabave grinder/vžigalnik)?

---

## Tehnične opombe za naslednjega agenta

- WP-CLI: `docker compose run --rm wp-cli wp ...` (profil `tools`).
- zvij-core 0.4.0: `includes/zvij-orders.php` (status + tiskanje), `includes/zvij-dashboard.php` (operativni pregled), `includes/zvij-invoice.php` (računska glava v email kupcu; hook `woocommerce_email_before_order_table`). Tiskanje: `admin-post.php?action=zvij_print_order&order_id=N` z noncem (`zvij_order_print_url()`).
- Račun (ZVIJ-08): prilagodljivo prek opcij `zvij_invoice_seller_name`, `zvij_invoice_seller_email`, `zvij_invoice_trr`, `zvij_invoice_tax_id`, `zvij_invoice_vat_note` in istoimenskih filtrov; naslov izdajatelja se bere iz `woocommerce_store_*`. Statusi z računom prek filtra `zvij_invoice_statuses`.
- Cart/checkout/account so **klasični shortcodi** (ne blocki) — namerno, zaradi zvij-core checkout hookov in popolnega brand stylinga. Ne vračaj na block checkout brez migracije hookov na Store API.
- Status `wc-zvij-ready` je vključen v `woocommerce_order_is_paid_statuses`.
- Dostava/plačila/pravne strani so bile nastavljene programatično; ponovljivo prek WP-CLI (glej git history tega dokumenta za ukaze).
