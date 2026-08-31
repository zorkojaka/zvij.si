# Zvij.si — Release plan in gap analiza

Datum: 5. 7. 2026 · Zadnja posodobitev: 16. 7. 2026
Veja: `chore/docker-wordpress-dev` · Dev: https://dev.inteligent.si

Ta dokument je operativni vir resnice za pot do prve prodajne verzije. Nadgrajuje handoff dokument (1. 7. 2026); zaklenjene odločitve iz handoffa ostajajo v veljavi.

> **Posodobitev 31. 8. 2026:** **katalog Ziggi + uskladitev cen vžigalnikov.** Dodanih 10 Ziggi izdelkov po računu ZIGGI (rizle: Original/Hemp/Natural Classic Slim 2,30 €, tri Special Edition + Wide Extra 2,50 €, Original Double 4,20 €; rolce: Original/Natural Roll + Tips + Tray 2,90 €) in **Clipper plin 16 ml 2,50 €** (FZG-Z-44). Marže 55–60 %, skladno s ciljem 0,55. Popravljeni DEV placeholderji: **Clipper Black 24,00 → 3,33 €** (soft touch, marža 74,8 %), **Clipper Gold 24,00 → 14,20 €** (kovinski, 59,4 %). V osnutek umaknjena »Ziggi Rolls« (placeholder, nadomeščen s pravimi izdelki) in **Clipper Silver** (ni na nobenem računu, cena ni potrjena). Prvič **vklopljeno vodenje zalog** — 246 Ziggi + 48 črnih + 12 zlatih Clipperjev + 25 plinov, vrednost vodene zaloge 1.028,74 € po MPC. Ponovljivo prek `scripts/wp-add-ziggi-clipper-catalog.php`. Cenik `Zvij_cenik_in_marze.xlsx` posodobljen (nove MPC, nova vrstica FZG-Z-44, nov list »Nabava Knistermann 2« za proformo 2026143695). **Odprto:** vseh 11 novih izdelkov ima placeholder sliko; cene grinderjev v trgovini (14,90 €) se ne ujemajo s cenikom (POLI-110 34,90 €, GRI-M-03 8,90 €); Clipper Silver čaka na nabavo in ceno. Revolut ključ (#1) ostaja edini tehnični blokator, produkcijsko okolje še ni postavljeno.
>
> **Posodobitev 16. 7. 2026:** **kristali — produkcijska preverba profila** (zvij-core 0.9.1) — E2E s playwrightom potrjeno, da prijavljen član v Moj račun vidi stanje kristalov (+ € protivrednost), zadnjih 5 transakcij, rok trajanja in svojo Zvij kodo. Popravljen hrošč mobilnega prikaza: responsivna tabela zgodovine ni imela `data-title` atributov → na telefonu so bile oznake Datum/Opis/Kristali prazne (samo `:`); zdaj pravilno. Preverjena pokritost meta `_zvij_kristali`: vseh 8 reload izdelkov/variacij iz strategije se ujema (DUBI 42=13, DUBI 420=75, vršički po tabeli); 20 dodatkov (pribor) po specifikaciji NE daje kristalov — če jih Jaka želi tudi na priboru, vpiše vrednosti ob potrjevanju DEV cen. Počiščeni testni podatki: E2E član + osirotela ledger vrstica `reload-test@example.com` (13 kristalov brez obstoječega člana, ostanek reload testov) — ledger je zdaj prazen, KPI obveznosti 0 €. Revolut sandbox ključ (#1) ostaja edini tehnični blokator.
>
> **Posodobitev 13. 7. 2026 (4):** **Mailpit** — dev emaili se odslej zajemajo lokalno (`zvij-dev-mailpit`, UI `127.0.0.1:8101`) namesto pošiljanja prek mail.zvij.si; preklop `scripts/wp-mail-mode.php mailpit|live|status`. V runbook dodan obvezen korak: ob migraciji preklopi prod na `live`. S tem je backlog »lahko počaka« tehnično prazen — vse preostalo čaka Jako (Revolut ključ, fotke, pravni pregled).
>
> **Posodobitev 13. 7. 2026 (3):** **zapuščene košarice** (zvij-core 0.9.0) — samo za člane s privolitvijo: košarica prijavljenega člana ali gosta-člana, ki na blagajni vpiše email, se zajame v tabelo `wp_zvij_carts`; po 6 urah brez spremembe (opcija `zvij_cart_reminder_hours`) urni cron pošlje EN opomnik (vsebina košarice, znesek, kristali, UTM linki za Plausible atribucijo `utm_campaign=zapuscena-kosarica`, odjavni link). Varovalke: max en email na člana na 7 dni, vmesni nakup = rešena košarica (KPI »rešenih« na dashboardu), odjava se spoštuje. E2E: 15 preverb logike + zajem na pravi blagajni s playwrightom potrjen; testni podatki počiščeni.
>
> **Posodobitev 13. 7. 2026 (2):** **slovenski permalinki** — odprta odločitev razrešena po priporočilu (opcija A iz runbooka): `/izdelek/`, `/kategorija/`, `/oznaka/`, `/kosarica/`, `/blagajna/`, `/moj-racun/` + slovenski endpointi (`narocila`, `narocilo-prejeto`, `odjava` …). Poravnano z živo stranjo, ki že uporablja `/izdelek/` → obstoječi live URL-ji izdelkov ostanejo živi brez redirect map. Stari dev `/product/...` se 301-preusmeri na `/izdelek/...`. Ponovljivo prek `scripts/wp-configure-permalinks.php` (+ `wp rewrite flush --hard`) — pognati tudi ob produkcijski migraciji. E2E potrjen celoten nakupni tok na novih URL-jih (trgovina → izdelek → košarica → blagajna → naročilo prejeto z UPN QR → moj račun); testno naročilo pobrisano.
>
> **Posodobitev 13. 7. 2026:** **MailerLite segmentacija nakupov** (zvij-core 0.8.1) — ob plačanem naročilu člana se v MailerLite posodobijo polja `customer_status`, `last_order_date` in `total_orders` → kampanje lahko ciljajo ponovne kupce (npr. `total_orders >= 2`). Ob tem odkrito in popravljeno: MailerLite je custom polja iz prijave doslej **tiho ignoriral**, ker v računu niso obstajala (in `source` je rezervirano ime → preimenovano v `signup_source`); zdaj se polja ustvarijo prek API samodejno. E2E s pravim MailerLite računom potrjeno (9 preverb), testni subscriber pobrisan. Reload/email sklop je s tem cel. Revolut sandbox ključ (#1) ostaja edini tehnični blokator.
>
> **Posodobitev 12. 7. 2026:** reload sklop zaključen — **»Ponovi naročilo«** (zvij-core 0.7.1: gumb v Moj račun + blok zadnjega naročila) in **reload opomniki** (zvij-core 0.8.0: polje »Reload opomnik (dni)« na izdelku, dnevni cron, email z bližnjico na ponovitev in odjavnim linkom, KPI na dashboardu). Opomniki so **dormant** — pošiljanje se začne šele, ko Jaka vpiše intervale na izdelke; samo člani s privolitvijo. E2E potrjeno (7 scenarijev), podrobnosti v `docs/REPEAT_ORDER_SPEC.md`. Revolut sandbox ključ (#1) ostaja edini tehnični blokator.
>
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

- **Email dostava (posodobljeno 13. 7.):** dev privzeto pošilja v **Mailpit** (`127.0.0.1:8101`) — nič ne gre v prave nabiralnike, vsa sporočila so vidna v UI. Preklop na živi mail.zvij.si: `scripts/wp-mail-mode.php live`. **Ob produkcijski migraciji obvezno preklopiti na `live`** (opcija `wp_mail_smtp` se prenese z bazo — glej runbook).
- Testno naročilo #369 (test-narocilo@example.com) je ostalo v bazi kot demo za dashboard — pred preklopom na produkcijo počistiti.
- **Katalog aktiviran (11. 7.):** 25 javnih izdelkov — dodatnih 15 (Clipper Silver, grinderji, rolce, fajr, throwie) objavljenih z **DEV cenami** za pravi občutek strani; cene pred produkcijo potrdi Jaka. Kit sestavljalnik s tem popoln (vseh 5 komponent kupljivih). Neaktivni ostajajo samo še dev placeholderji, paketni osnutki in »later« izdelki (tray, Champ).

---

## 2. Gap analiza — kaj še manjka za lansiranje

### MUST LAUNCH (blokira odprtje trgovine)

| # | Vrzel | Kdo | Status |
|---|-------|-----|--------|
| 1 | Produkcijsko plačilo s kartico (Revolut) — namestitev vtičnika, sandbox test, produkcijski ključi | Jaka (ključi) + agent (integracija) | 🔧 gateway nameščen v dev (sandbox), čaka Jakov **sandbox ključ** → test → produkcija (`docs/REVOLUT_SETUP.md`) |
| 2 | SMTP / transakcijski email (potrditve naročil, welcome email) | agent + Jaka | ✅ deluje (9. 7.): WP Mail SMTP prek `mail.zvij.si` 587/TLS; avtentikacija (235) in pošiljanje potrjena z dev. Za produkcijo uredi še SPF/DKIM/DMARC (#4) za dostavljivost. (`docs/SMTP_SETUP.md`) |
| 3 | MailerLite: račun, skupina »Člani Zvij.si«, API ključ + group ID v env | Jaka + agent | ✅ urejeno (11. 7.): račun (zvijace@zvij.si, po trialu Free do 250 naročnikov), skupina »Zvij.si Člani« (ID 192694849668908922), ključ + group ID v WP options (autoload=no, ne v gitu). **E2E potrjeno:** prijava prek obrazca → subscriber active v skupini; testni podatki počiščeni. TODO pred prvo kampanjo: avtentikacija domene zvij.si v MailerLite (DNS zapisi). Unsubscribe webhook endpoint v zvij-core še ne obstaja — post-launch |
| 4 | zvijace@zvij.si + SPF/DKIM/DMARC | Jaka (cPanel/DNS) | ✅ urejeno (11. 7.): nabiralnik zvijace@zvij.si obstaja, SPF + DKIM (selector `default`) + DMARC (`p=none; rua=mailto:zvijace@zvij.si`) v DNS pri Neoservu. Mail-tester **10/10, »properly authenticated«**. TODO čez 2–4 tedne čistih DMARC poročil: dvigni politiko na `p=quarantine` |
| 5 | Realne fotografije: grinder, tulci, kit flat-layi, hero | Jaka (fotografiranje) | ✅ večinoma urejeno (8. 7.): produktne fotografije iz `~/apps/zvijsi/produkti` uvožene za DUBI 42/420, SMOKEY/CHILLY/FRUTTY, grinder in vse tri tulce (featured + galerije, optimizirane v JPEG); hero na domači strani je zdaj realna fotografija tulcev. Še manjka: pravi kit flat-layi (Black/Silver/Gold/Throwie še AI), vžigalniki in rolce (čaka nabava). Posterji z vpečenim besedilom (crn/srebrn/zlat-tulec.png) so rezervirani za promo bloke, ne za produktne galerije. **Nov vir resnice za slike: `docs/SLIKE_PRODUKTOV.md`** (11. 7.) — popis realnih/manjkajočih slik s slogovnim vodičem za AI generiranje in ukazi za umestitev. |
| 6 | Podatki podjetja: TRR za predračun, naslov trgovine, davčni status (DDV zavezanec?) | Jaka | ✅ urejeno (9. 7.): vsi podatki iz PRS vneseni, TRR `SI56 6100 0002 8076 803`, ni zavezanec za DDV |
| 7 | Potrditev cen dostave in praga brezplačne dostave | Jaka (odločitev) | ✅ potrjeno (OWNER-M07, ZVIJ-07): navadna 2,90 / sledenje 3,90 / podpis 3,90 / povzetje 5,90 / paket 7,50; brezplačna nad 42 € |
| 8 | Izdaja računov (Woo email z računom zadošča za start; pravi računovodski sistem kasneje) | odločitev | ✅ urejeno (ZVIJ-08) — račun v email; podatki izdajatelja vneseni; **zaporedno št. `ZZ/MM/LLLL`** (ročni popravek, letni reset), predračun ločen; UPN QR za nakazila |
| 9 | Migracija dev → zvij.si (backup, DNS, search-replace, test) | agent + Jaka potrditev | ✅ runbook pripravljen (10. 7.): `docs/PROD_MIGRATION_RUNBOOK.md` — faze, ukazi, SEO permalinki odločitev, rollback. Izvedba čaka na predpogoje (#1–#4) |
| 10 | Pravni pregled pogojev/zasebnosti (osnutki so vpisani) | Jaka | osnutek pripravljen |

### LAHKO POČAKA (po prvem lansiranju)

> **Kristali (dobroimetje) — implementirano 11. 7. 2026, nadgrajeno v 0.7.0 na Jakovo specifikacijo:** valuta so **kristali, 10 kristalov = 1 €**; poleg spodnjega (zdaj v kristalih) še: **Zvij koda** (`ZK-XXXXXX`, referral: prijatelj 10 % na prvo naročilo, lastnik 30 kristalov ob plačilu, samo-priporočanje in ponovni nakupi zavrnjeni, storno ob preklicu), **poraba za goste** (gost s svojo Zvij kodo + ujemajočim emailom unovči kristale), **rok trajanja** (12 mesecev brez aktivnosti, dnevni cron, `expire` vrstice). Kartice zdaj javno kažejo »Član prejme X kristalov«. E2E: vsi 4 scenariji (pripis 13, poraba gosta prek kode −13, referral +30/storno −30, potek 50 → 0). Prejšnji opis (0.6.0): ledger tabela `wp_zvij_credit_ledger` (earn/redeem/refund/adjust, revizijska sled), pripis ob plačanem naročilu po zneskih z izdelkov/variacij (meta `_zvij_dobroimetje_eur` ali razčlenjeno iz javnega napisa), storno ob preklicu v obe smeri, unovčenje na blagajni (checkbox → negativni fee do vrednosti izdelkov, dostava se plača; samo prijavljeni člani — gost bi lahko porabil tuje stanje), stanje + zgodovina v Moj račun, obvestilo v emailu in na »naročilo prejeto«, KPI obveznosti v Zvij.si adminu. **E2E potrjeno:** nakup → pripis 1,25 € → poraba pri drugem naročilu (total 9,64 = 7,99 + 2,90 − 1,25) → preklica obeh naročil pravilno vrneta/stornirata, končno stanje 0. Referral del ostaja post-launch.

- ~~Kiti kot kupljivi bundle~~ **Urejeno (11. 7.): interaktivni sestavljalnik na /kiti/** — kartice Black/Silver/Gold preklapljajo komponente, sličice s checkboxi (privzeto vse izbrano), skupna cena, »Dodaj kit v košarico« (AJAX doda izbrane izdelke). Komponente v draftu (grinderji, rolce, Clipper Silver) so prikazane kot »Kmalu« in jih ni mogoče izbrati — ob potrjeni nabavi jih samo objaviš in takoj postanejo kupljive. E2E: preklop barve, delna izbira, 2 izdelka v košarici.
- ~~Reload email avtomatika~~ **Urejeno (12. 7., zvij-core 0.8.0): reload opomniki implementirani, a dormant** — pošiljanje se aktivira šele, ko Jaka vpiše »Reload opomnik (dni)« na izdelke (admin → izdelek → Splošno). Samo člani s privolitvijo, dnevni cron, odjavni link, KPI na dashboardu; E2E potrjeno (glej `docs/REPEAT_ORDER_SPEC.md`). »Ponovi naročilo« v Moj račun že od 0.7.1. **Segmentacija ponovnih nakupov urejena (13. 7., zvij-core 0.8.1):** MailerLite polja `customer_status` / `last_order_date` / `total_orders` se posodobijo ob vsakem plačanem naročilu člana (+ samodejno ustvarjanje custom polj prek API; `source` je rezervirano ime → `signup_source`). Kampanjo na segment sestavi Jaka v MailerLite (npr. `total_orders >= 2` ali `last_order_date` starejši od 30 dni).
- ~~Zapuščene košarice~~ **Urejeno (13. 7., zvij-core 0.9.0):** opomnik po 6 h (nastavljivo), samo člani s privolitvijo, en email / 7 dni, UTM atribucija v Plausible, KPI na dashboardu. ~~GA4/Plausible analitika~~ (urejeno 11. 7.); campaign attribution pokrivajo UTM parametri v opomnikih + Plausible
- ~~Slovenski permalinki~~ **Urejeno (13. 7.):** `/izdelek/`, `/kategorija/`, `/oznaka/`, `/kosarica/`, `/blagajna/`, `/moj-racun/` + slovenski endpointi; poravnano z živo stranjo (`/izdelek/` že na live). Ponovljivo: `scripts/wp-configure-permalinks.php` + `wp rewrite flush --hard` (pognati tudi ob migraciji). Stari `/product/...` → 301 na `/izdelek/...`. E2E nakupni tok potrjen.
- CBD kapljice, širši katalog
- ~~Mailpit v docker-compose~~ **Urejeno (13. 7.):** servis `zvij-dev-mailpit`, UI na `127.0.0.1:8101`; dev SMTP privzeto preklopljen na Mailpit (nič več pravih pošiljanj z dev-a), preklop nazaj na mail.zvij.si prek `scripts/wp-mail-mode.php` (`mailpit|live|status`, žive nastavitve shranjene)

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
