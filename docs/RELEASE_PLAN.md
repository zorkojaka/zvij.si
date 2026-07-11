# Zvij.si — Release plan in gap analiza

Datum: 5. 7. 2026 · Zadnja posodobitev: 11. 7. 2026
Veja: `chore/docker-wordpress-dev` · Dev: https://dev.inteligent.si

Ta dokument je operativni vir resnice za pot do prve prodajne verzije. Nadgrajuje handoff dokument (1. 7. 2026); zaklenjene odločitve iz handoffa ostajajo v veljavi.

> **Posodobitev 11. 7. 2026:** trije MUST/backlog zadetki v enem dnevu — **email avtentikacija** (#4): DMARC vpisan, mail-tester 10/10 »properly authenticated«; **MailerLite** (#3): povezan in E2E testiran (obrazec → subscriber v skupini); **analitika** (backlog 6): samohostan Plausible CE na `https://analitika.zvij.si`, dogodki view_item/add_to_cart/begin_checkout/purchase E2E potrjeni. Pripravljena tudi produkcijska migracija (runbook + zvij-prod stack + deploy skripta, backlog 7). **Edini preostali tehnični blokator: Revolut sandbox ključ (#1).** Odprti odločitvi: permalinki (`/izdelek/` vs `/product/`) — priporočilo: slovenski.
>
> **Posodobitev 10. 7. 2026:** poenoten dizajn produktnih kartic v trgovini — lastna predloga `woocommerce/content-product.php` (slikovni pas čez celo širino kartice brez roba, značke Akcija/Prvič kot overlay na sliki, cena + gumb poravnana na dno kartice; enako v »Podobni izdelki«). Popravljena mobilna noga (obrazec »Postani član« se ni več prelomil v eno kolono in je bil odrinjen iz zaslona). E2E preverjeno: dodajanje v košarico z enostavno in variabilno kartico deluje.
>
> **Posodobitev 9. 7. 2026:** slike zapolnijo okvir (hero/karusel/trgovina, `object-fit: cover`); račun ima zaporedno št. `ZZ/MM/LLLL` z ročnim popravkom, predračun je ločen dokument; podatki izdajatelja vneseni iz PRS in **TRR popravljen** na `SI56 6100 0002 8076 803`; dodan **UPN QR »slikaj in plačaj«** za nakazila; **Revolut gateway nameščen v dev (sandbox)**, čaka na Jakov ključ; COD 5,90 € potrjen za produkcijo; podjetje ni zavezanec za DDV.

---

## 1. Potrjeno trenutno stanje (5. 7. 2026)

### Deluje in je preverjeno (E2E test s testnim naročilom #369)

- **Celoten nakupni tok:** izdelek → košarica → blagajna → naročilo → stran »naročilo prejeto«. Preverjen z DUBI 42, plačilo po predračunu, skupni znesek pravilen (izdelek 7,99 € + dostava 3,90 €).
- **Katalog:** DUBI 42, DUBI 420, SMOKEY/CHILLY/FRUTTY kot variabilni izdelki 1 g / 5 g s cenami in slikami. Homepage carousel (tag `homepage-carousel`) deluje.
- **Dostava:** cona Slovenija (Pošta Slovenije), potrjeno (OWNER-M07): navadna 2,90 €, s sledenjem 3,90 €, s podpisom 3,90 €, po povzetju 5,90 €, paket 7,50 €; brezplačna navadna nad 42 €. Nastavljeno programsko prek `scripts/wp-configure-shipping-dev.php` (ZVIJ-07).
- **Plačila (dev):** predračun/nakazilo (BACS, TRR zdaj nastavljen — kupec vidi IBAN + sklic), po povzetju (COD, 5,90 € potrjeno). **UPN QR »slikaj in plačaj«** (zvij-core `includes/zvij-payment-qr.php`): za neplačana BACS naročila se na strani »naročilo prejeto«, v pregledu naročila in v predračunskem emailu prikaže standardni slovenski UPN QR (prejemnik, znesek, sklic `SI00 {naročilo}`). QR sestavi `datalinx/php-upn-qr-generator` (vendorano), izris prek GD (Imagick nima delegatov); generiran PNG dekodirno preverjen. **Revolut kartice:** uradni `revolut-gateway-for-woocommerce` nameščen + aktiviran v dev, način sandbox; metode skrite na blagajni dokler ni vpisan API ključ (glej `docs/REVOLUT_SETUP.md`).
- **Pravne strani:** Politika zasebnosti, Vračila in reklamacije, Pogoji poslovanja (18+, cene z DDV, dostava, CBD disclaimer) — objavljene in povezane s checkoutom (obvezno soglasje).
- **Član Zvij.si:** obrazec (homepage/footer), soglasje, kupon 10 % (enkraten, 30 dni, vezan na email), welcome email (dev pregled v adminu), checkout opt-in, ob nakupu se član označi kot `customer`. MailerLite sinhronizacija je pripravljena, čaka na API ključ.
- **Operativni admin (novo, zvij-core 0.3.0):**
  - meni **Zvij.si** → operativni pregled: prihodek danes/7/30 dni, št. naročil, povprečno naročilo, člani, naročila za obdelavo, top izdelki, opozorila o zalogi, novi člani;
  - **natisljiv odpremni dokument** (naslovnica za paket + seznam za pakiranje s SKU in checkboxi, COD znesek) — gumb na seznamu naročil, na naročilu in na dashboardu;
  - **status naročila »Pripravljeno za odpremo«** (`wc-zvij-ready`) + bulk akcija; šteje kot plačan status v statistiki.
- **Checkout UX:** klasični (shortcode) cart/checkout/moj račun, stilizirani v brand smeri (dvokolonska blagajna s povzetkom naročila, mobilna postavitev). Prehod z block checkouta je bil nujen, ker block checkout ne podpira obstoječih hookov (opt-in, member status).
- **Jezik:** nameščen sl_SI za WordPress in WooCommerce (checkout, emaili, statusi v slovenščini).
- **Očiščen javni katalog:** »Sample paket« in »Zvij setup paket« (DEV placeholderja) umaknjena v draft. Javno je 5 realnih izdelkov.
- **Izdaja računov (ZVIJ-08, zvij-core):** email kupcu vsebuje računsko glavo z izdajateljem in opombo o DDV; WooCommerce izpiše postavke in znesek. HTML + plain-text. **Podatki izdajatelja vneseni iz PRS:** ZVIJ.si, spletna prodaja, d.o.o., Rjava cesta 26A, 1260 Ljubljana-Polje, info@zvij.si, matična 9378294000, davčna 68449763, TRR `SI56 6100 0002 8076 803`; DDV opomba po 94. členu ZDDV-1 (ni zavezanec).
  - **Zaporedno številčenje `ZZ/MM/LLLL`:** št. računa dobijo **samo plačana naročila** (processing/pripravljeno/completed) in tečejo po vrsti brez lukenj; števec je v opciji `zvij_invoice_next_seq` (ročni popravek v WooCommerce → Nastavitve → Splošno, npr. po računu izven sistema), letni reset. Št. se dodeli ob prehodu v plačan status in shrani na naročilo (idempotentno).
  - **Predračun ločen:** neplačana naročila (on-hold/nakazilo) se izpišejo kot »Predračun« s št. naročila in **ne** porabijo zaporedne št. računa.

### Znane omejitve dev okolja

- **Email dostava:** dev nima SMTP — emaili se ne dostavijo (vsebina welcome emaila je vidna v adminu). Za produkcijo obvezen SMTP ali transakcijski ponudnik.
- Testno naročilo #369 (test-narocilo@example.com) je ostalo v bazi kot demo za dashboard — pred preklopom na produkcijo počistiti.
- Draft izdelki (tulci, grinderji, vžigalniki, rolce, kiti) čakajo na cene/fotografije/nabavo — pravilno so skriti.

---

## 2. Gap analiza — kaj še manjka za lansiranje

### MUST LAUNCH (blokira odprtje trgovine)

| # | Vrzel | Kdo | Status |
|---|-------|-----|--------|
| 1 | Produkcijsko plačilo s kartico (Revolut) — namestitev vtičnika, sandbox test, produkcijski ključi | Jaka (ključi) + agent (integracija) | 🔧 gateway nameščen v dev (sandbox), čaka Jakov **sandbox ključ** → test → produkcija (`docs/REVOLUT_SETUP.md`) |
| 2 | SMTP / transakcijski email (potrditve naročil, welcome email) | agent + Jaka | ✅ deluje (9. 7.): WP Mail SMTP prek `mail.zvij.si` 587/TLS; avtentikacija (235) in pošiljanje potrjena z dev. Za produkcijo uredi še SPF/DKIM/DMARC (#4) za dostavljivost. (`docs/SMTP_SETUP.md`) |
| 3 | MailerLite: račun, skupina »Člani Zvij.si«, API ključ + group ID v env | Jaka + agent | ✅ urejeno (11. 7.): račun (zvijace@zvij.si, po trialu Free do 250 naročnikov), skupina »Zvij.si Člani« (ID 192694849668908922), ključ + group ID v WP options (autoload=no, ne v gitu). **E2E potrjeno:** prijava prek obrazca → subscriber active v skupini; testni podatki počiščeni. TODO pred prvo kampanjo: avtentikacija domene zvij.si v MailerLite (DNS zapisi). Unsubscribe webhook endpoint v zvij-core še ne obstaja — post-launch |
| 4 | zvijace@zvij.si + SPF/DKIM/DMARC | Jaka (cPanel/DNS) | ✅ urejeno (11. 7.): nabiralnik zvijace@zvij.si obstaja, SPF + DKIM (selector `default`) + DMARC (`p=none; rua=mailto:zvijace@zvij.si`) v DNS pri Neoservu. Mail-tester **10/10, »properly authenticated«**. TODO čez 2–4 tedne čistih DMARC poročil: dvigni politiko na `p=quarantine` |
| 5 | Realne fotografije: grinder, tulci, kit flat-layi, hero | Jaka (fotografiranje) | ✅ večinoma urejeno (8. 7.): produktne fotografije iz `~/apps/zvijsi/produkti` uvožene za DUBI 42/420, SMOKEY/CHILLY/FRUTTY, grinder in vse tri tulce (featured + galerije, optimizirane v JPEG); hero na domači strani je zdaj realna fotografija tulcev. Še manjka: pravi kit flat-layi (Black/Silver/Gold/Throwie še AI), vžigalniki in rolce (čaka nabava). Posterji z vpečenim besedilom (crn/srebrn/zlat-tulec.png) so rezervirani za promo bloke, ne za produktne galerije. |
| 6 | Podatki podjetja: TRR za predračun, naslov trgovine, davčni status (DDV zavezanec?) | Jaka | ✅ urejeno (9. 7.): vsi podatki iz PRS vneseni, TRR `SI56 6100 0002 8076 803`, ni zavezanec za DDV |
| 7 | Potrditev cen dostave in praga brezplačne dostave | Jaka (odločitev) | ✅ potrjeno (OWNER-M07, ZVIJ-07): navadna 2,90 / sledenje 3,90 / podpis 3,90 / povzetje 5,90 / paket 7,50; brezplačna nad 42 € |
| 8 | Izdaja računov (Woo email z računom zadošča za start; pravi računovodski sistem kasneje) | odločitev | ✅ urejeno (ZVIJ-08) — račun v email; podatki izdajatelja vneseni; **zaporedno št. `ZZ/MM/LLLL`** (ročni popravek, letni reset), predračun ločen; UPN QR za nakazila |
| 9 | Migracija dev → zvij.si (backup, DNS, search-replace, test) | agent + Jaka potrditev | ✅ runbook pripravljen (10. 7.): `docs/PROD_MIGRATION_RUNBOOK.md` — faze, ukazi, SEO permalinki odločitev, rollback. Izvedba čaka na predpogoje (#1–#4) |
| 10 | Pravni pregled pogojev/zasebnosti (osnutki so vpisani) | Jaka | osnutek pripravljen |

### LAHKO POČAKA (po prvem lansiranju)

> **Dobroimetje — implementirano 11. 7. 2026 (zvij-core 0.6.0, na Jakovo zahtevo pred lansiranjem):** ledger tabela `wp_zvij_credit_ledger` (earn/redeem/refund/adjust, revizijska sled), pripis ob plačanem naročilu po zneskih z izdelkov/variacij (meta `_zvij_dobroimetje_eur` ali razčlenjeno iz javnega napisa), storno ob preklicu v obe smeri, unovčenje na blagajni (checkbox → negativni fee do vrednosti izdelkov, dostava se plača; samo prijavljeni člani — gost bi lahko porabil tuje stanje), stanje + zgodovina v Moj račun, obvestilo v emailu in na »naročilo prejeto«, KPI obveznosti v Zvij.si adminu. **E2E potrjeno:** nakup → pripis 1,25 € → poraba pri drugem naročilu (total 9,64 = 7,99 + 2,90 − 1,25) → preklica obeh naročil pravilno vrneta/stornirata, končno stanje 0. Referral del ostaja post-launch.

- Kiti kot kupljivi bundle produkti (zdaj showcase; blokirano na nabavo komponent in cene)
- Reload email avtomatika, segmentacija ponovnih nakupov
- Zapuščene košarice, GA4/Plausible analitika in campaign attribution
- Slovenski permalinki (`/izdelek/`, `/kosarica/`) — zdaj `/product/`, `/cart/` (delujejo)
- CBD kapljice, širši katalog
- Mailpit v docker-compose za lokalni email test

---

## 3. Kaj mora narediti Jaka (ročno, po prioriteti)

1. ~~Fotografiraj grinder in tulce~~ **Urejeno (8. 7.)** — fotografije uvožene v katalog. Ostane: foto vžigalnikov in rolc ob prihodu ter realni kit flat-layi za Black/Silver/Gold/Throwie.
2. **Odpri MailerLite** račun in ustvari skupino »Člani Zvij.si«; API ključ pošlji varno (env, ne Git).
3. **Ustvari zvijace@zvij.si** in uredi SPF/DKIM/DMARC DNS zapise.
4. ~~Odloči: prag brezplačne dostave, cena dostave, DDV status, TRR~~ **Urejeno (9. 7.):** dostava/prag potrjeni (ZVIJ-07), ni zavezanec za DDV, TRR vnesen.
5. **Revolut:** ~~potrdi ponudnika~~ (Revolut) — vpiši **sandbox API ključ** v WooCommerce → Plačila → Revolut, naredi testni nakup, nato produkcijski ključ. Koraki: `docs/REVOLUT_SETUP.md`.
6. **Preveri pravna besedila** (pogoji, zasebnost, vračila) — vpisani so razumni osnutki, niso pravno pregledani.

## 4. Agent-ready backlog (naslednje naloge za Codex/agenta)

1. **SMTP integracija** — ko Jaka izbere ponudnika: WP Mail SMTP ali wp_mail phpmailer konfiguracija prek env; test potrditvenih emailov.
2. ~~Revolut gateway — namestitev v dev~~ **Urejeno (9. 7.):** uradni vtičnik nameščen + aktiviran, sandbox način. Ostane: testni nakup po Jakovem sandbox ključu.
3. ~~MailerLite aktivacija~~ **Urejeno (11. 7.)** — ključi vpisani, sinhronizacija E2E testirana. **Unsubscribe webhook implementiran (zvij-core 0.5.0):** REST `zvij/v1/mailerlite-webhook` s HMAC preverbo; webhook registriran v MailerLite (dogodka subscriber.unsubscribed + spam_reported), E2E potrjeno (odjava → lokalni status `unsubscribed`). Ob migraciji ponovno registrirati webhook z zvij.si URL (v runbooku). **Domena avtenticirana (11. 7.):** MailerLite DKIM (CNAME litesrv._domainkey), SPF združen z include:_spf.mlsend.com (en sam v=spf1 zapis), verifikacijski TXT — MailerLite potrdil, mail-tester za navadno pošto še vedno 10/10. Kampanje so pripravljene za pošiljanje.
4. **Fotografije v katalog** — ko so posnete: nadomesti AI flat-laye, product gallery za grinder/tulce, hero kompozicija.
5. **Kit kot kupljiv produkt** — ko so komponente in cene: grouped/bundle pristop, Black kit prvi.
6. ~~Analitika~~ **Urejeno (11. 7.): Plausible CE (samohostan)** — `https://analitika.zvij.si` (stack v `/home/jaka/apps/plausible`, port 8100, ClickHouse omejen na 1 GB; DNS + HTTPS cert urejena). Admin: zorkojaka@gmail.com (začetno geslo `/home/jaka/apps/plausible/.admin-initial-password` — zamenjaj ob prvi prijavi; registracije invite-only). Strani dev.inteligent.si + zvij.si s cilji view_item/add_to_cart/begin_checkout/purchase. Tema pošilja dogodke (`assets/analytics.js`), brez piškotkov → brez privolitve. **E2E potrjeno** (nakup → vsi 4 dogodki v dashboardu). Opomba: CE ne sešteva zneskov (revenue goals so EE funkcija) — purchase šteje konverzije, prihodek kaže Zvij.si admin pregled. Tema revenue payload vseeno pošilja (neškodljivo, aktivira se ob morebitnem prehodu na EE/cloud).
7. ~~Produkcijska migracija — runbook~~ **Urejeno (10. 7.):** `docs/PROD_MIGRATION_RUNBOOK.md` + pripravljena `docker-compose.prod.yml` (projekt zvij-prod, port 8099, WP_DEBUG off, zahteva .env) in `scripts/deploy-prod.sh` (zahteva prod .env, `ZVIJ_ALLOW_PUBLIC_FAIL=1` za deploy pred DNS preklopom). Izvedba čaka na predpogoje (Faza 0).
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

1. ~~Prag brezplačne dostave: vpisan predlog 40 €~~ **Odločeno (OWNER-M07, ZVIJ-07): 42 €**, brezplačna velja za navadno pošiljko; sledenje/podpis/povzetje se doplačajo.
2. ~~Cena dostave in ponudnik~~ **Odločeno (OWNER-M07/Q02): Pošta Slovenije**; navadna 2,90 / sledenje 3,90 / podpis 3,90 / povzetje 5,90 / paket 7,50 €.
3. ~~Po povzetju: obdržati na produkciji?~~ **Odločeno (9. 7.): da, 5,90 €.** Enostavno prilagodljivo prek dostavne metode »Po povzetju« (WooCommerce → Nastavitve → Dostava → Slovenija).
4. ~~DDV: ali je Zvij.si d.o.o. zavezanec?~~ **Odločeno (9. 7.): NI zavezanec.** DDV se ne obračunava; račun navaja 1. odst. 94. člena ZDDV-1.
5. ~~Računi: Woo email dovolj za start ali takoj povezava z računovodskim sistemom?~~ **Odločeno (ZVIJ-08): Woo email z računom zadošča za start** — implementirano v zvij-core 0.4.0. Računovodski sistem kasneje. Jaka naj vpiše podatke podjetja (#6: naslov trgovine, TRR prek `zvij_invoice_trr`, davčna prek `zvij_invoice_tax_id`), da je izdajatelj na računu popoln.
6. Silver kit v prvi val ali kasneje (odvisno od nabave grinder/vžigalnik)?

---

## Tehnične opombe za naslednjega agenta

- WP-CLI: `docker compose run --rm wp-cli wp ...` (profil `tools`).
- zvij-core 0.4.0: `includes/zvij-orders.php` (status + tiskanje), `includes/zvij-dashboard.php` (operativni pregled), `includes/zvij-invoice.php` (računska glava v email kupcu; hook `woocommerce_email_before_order_table`). Tiskanje: `admin-post.php?action=zvij_print_order&order_id=N` z noncem (`zvij_order_print_url()`).
- Račun (ZVIJ-08): prilagodljivo prek opcij `zvij_invoice_seller_name`, `zvij_invoice_seller_email`, `zvij_invoice_reg_no`, `zvij_invoice_trr`, `zvij_invoice_tax_id`, `zvij_invoice_vat_note` in istoimenskih filtrov; naslov izdajatelja se bere iz `woocommerce_store_*`. Statusi z računom prek `zvij_invoice_statuses` (samo plačani), predračun prek `zvij_proforma_statuses`. Zaporedni števec: opciji `zvij_invoice_next_seq` + `zvij_invoice_seq_year`, letni reset prek filtra `zvij_invoice_reset_yearly`; št. na naročilu v meta `_zvij_invoice_number`.
- UPN QR (`includes/zvij-payment-qr.php`): knjižnica v `vendor/` (datalinx/php-upn-qr-generator + bacon/bacon-qr-code, commitano, ker na hostu ni composerja). PNG prek GD (`zvij_upnqr_render_png`), shranjen v `uploads/zvij-upnqr/` (gitignore). Velja za BACS + on-hold/pending prek `zvij_upnqr_applies` (filter). Imagick v okolju nima format-delegatov, zato NE uporabljamo knjižničnega `generateQrCode()`.
- Revolut: uradni `revolut-gateway-for-woocommerce` (v `wordpress_data` volumnu, provisionira `scripts/wp-install-dev.sh`). Nastavitve: `woocommerce_revolut_settings` (`mode`=sandbox, `api_key_sandbox`), gatewayi `revolut_cc` / `revolut_pay` / `revolut_payment_request`. Navodila: `docs/REVOLUT_SETUP.md`.
- Cart/checkout/account so **klasični shortcodi** (ne blocki) — namerno, zaradi zvij-core checkout hookov in popolnega brand stylinga. Ne vračaj na block checkout brez migracije hookov na Store API.
- Status `wc-zvij-ready` je vključen v `woocommerce_order_is_paid_statuses`.
- Dostava/plačila/pravne strani so bile nastavljene programatično; ponovljivo prek WP-CLI (glej git history tega dokumenta za ukaze).
