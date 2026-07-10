# Produkcijska migracija: dev.inteligent.si → zvij.si

Datum priprave: 10. 7. 2026 · Status: **runbook pripravljen, izvedba čaka na predpogoje**

Ta dokument je izvedbeni načrt za preklop trgovine z dev okolja na produkcijski
`zvij.si`. Do izvedbe se live `zvij.si` **ne dotika** — vse spodaj je priprava
in dokumentacija. Runbook izvaja agent + Jaka (koraki, označeni z **[JAKA]**,
zahtevajo dostope, ki jih ima samo on).

## Ugotovljeno stanje (10. 7. 2026)

- Live `zvij.si` → `152.89.234.190` (obstoječi ponudnik / cPanel; tam teče tudi `mail.zvij.si`).
- Dev `dev.inteligent.si` → `178.104.24.47` (Hetzner, Docker Compose projekt `zvij-dev`, WP na `127.0.0.1:8098` za nginx proxyjem).
- Migracija = na Hetznerju postaviti ločen produkcijski compose projekt in preusmeriti DNS `zvij.si` na `178.104.24.47`.
- **SEO past:** live permalinki so slovenski (`/izdelek/...`), dev uporablja `/product/...`. Pred preklopom je treba odločiti: slovenski permalinki na produkciji ali 301 mapa starih URL-jev (glej Fazo 1, korak 6).

---

## Faza 0 — Predpogoji (brez teh se cutover ne začne)

Iz release checklista (`RELEASE_PLAN.md` §5):

- [ ] Revolut: sandbox test opravljen, **produkcijski ključ** vpisan in preverjen
- [ ] SMTP: potrditveni email prispe v pravi nabiralnik; **[JAKA]** SPF/DKIM/DMARC za `zvij.si` urejeni
- [ ] MailerLite ključ vpisan ali zavestno odloženo na po-lansiranje
- [ ] Cene, zaloge in pravna besedila potrjeni
- [ ] **[JAKA]** dostop do DNS upravljanja za `zvij.si` (kje se ureja? cPanel/registrar — preveri vnaprej!)
- [ ] **[JAKA]** dostop do obstoječega gostovanja za polni backup

## Faza 1 — Priprava (T−7 do T−1, brez vpliva na live)

1. **[JAKA] Znižaj DNS TTL** za `zvij.si` A zapis na 300 s (vsaj 24 h pred preklopom), da sta preklop in morebitni rollback hitra.
2. **[JAKA] Polni backup live strani**: cPanel full backup (datoteke + baza + e-pošta) in prenos izven strežnika. Brez potrjenega backupa ni preklopa.
3. **Produkcijski compose projekt** (agent, v repo):
   - nov `docker-compose.prod.yml` (še ne obstaja): projekt `zvij-prod`, ločeni containerji (`zvij-prod-mariadb`, `zvij-prod-wordpress`), port `127.0.0.1:8099`, ločen volume;
   - `WP_ENVIRONMENT_TYPE=production`, `WP_DEBUG=false`, `WORDPRESS_URL=https://zvij.si`;
   - ločen `.env` v `/var/www/zvij.si-app/.env` (nove DB gesla, ne dev in ne stare produkcijske vrednosti);
   - `scripts/deploy-prod.sh` po vzoru `deploy-dev.sh` (lock, fiksne poti, health check).
4. **Nginx vhost** za `zvij.si` + `www.zvij.si` (agent pripravi, **[JAKA]**/sudo namesti): najprej HTTP z ACME webroot (`/var/www/zvij.si-app/public`), proxy na `127.0.0.1:8099`. Ne dotikaj se drugih vhostov.
5. **Generalka na dev**: celoten postopek izvoza/uvoza (Faza 2, koraki 2–5) izvedi v `zvij-prod` stack **pred** preklopom DNS in stran preveri prek `curl --resolve zvij.si:443:127.0.0.1` oz. začasnega hosts vnosa.
6. **Permalinki/SEO odločitev [JAKA]**:
   - opcija A (priporočeno): na produkciji vklopi slovenske osnove (`/izdelek/`, `/kosarica/` …) — stari live URL-ji izdelkov, ki obstajajo tudi v novem katalogu, ostanejo živi;
   - opcija B: obdrži `/product/` in dodaj nginx 301 mapo za znane live URL-je (seznam v `LIVE_CONTENT_AUDIT.md`);
   - v obeh primerih: stare URL-je, ki nimajo naslednika (ukinjeni izdelki), 301 na `/trgovina/`.

## Faza 2 — Cutover (T-0, izven prometnih ur)

> Ocenjen izpad: 0 (stara stran teče do preklopa DNS; naročila z zadnjih ur pred preklopom na stari strani ni — trgovina na stari strani je tako ali tako neaktivna).

1. **Zamrzni spremembe na dev** (nič več urejanja vsebine do konca preklopa).
2. **Izvoz dev baze**:
   ```bash
   cd /home/jaka/apps/zvijsi/zvij.si
   docker compose exec mariadb sh -c 'mariadb-dump -u root -p"$MARIADB_ROOT_PASSWORD" zvij_dev' > /tmp/zvij-migracija.sql
   ```
3. **Uvoz v prod DB** (v `zvij-prod` projektu) in **search-replace**:
   ```bash
   PROD="docker compose --project-name zvij-prod --env-file /var/www/zvij.si-app/.env -f docker-compose.prod.yml"
   $PROD run --rm wp-cli \
     wp search-replace 'https://dev.inteligent.si' 'https://zvij.si' --all-tables --precise --report-changed-only
   $PROD run --rm wp-cli wp cache flush
   ```
   (search-replace prek WP-CLI pravilno obdela serializirane vrednosti — ne uporabljaj sed po SQL dumpu.)

   > **POZOR:** vse `docker compose` ukaze za prod poganjaj z `--env-file /var/www/zvij.si-app/.env` — brez tega compose tiho pobere dev `.env` iz mape repozitorija.
4. **Uploads sync**: prekopiraj `wp-content/uploads` iz `zvij-dev` volume v `zvij-prod` volume (docker cp ali rsync med mount potmi volumov).
5. **Produkcijske nastavitve po uvozu** (WP-CLI):
   - izbriši testno naročilo #369 in testne člane (`test-narocilo@example.com`),
   - preveri, da so draft izdelki še draft, javnih je 5 realnih izdelkov,
   - Revolut: preklopi na produkcijski ključ, izklopi sandbox način,
   - WooCommerce → naslov trgovine/emaili kažejo na `zvij.si` domeno,
   - `wp option get home` in `siteurl` → `https://zvij.si`.
6. **Lokalni smoke** (pred DNS preklopom): `curl -i --resolve zvij.si:443:127.0.0.1 https://zvij.si/` oz. prek HTTP porta `curl -i -H "Host: zvij.si" http://127.0.0.1:8099/` — domov, trgovina, izdelek, košarica.
7. **[JAKA] DNS preklop**: `zvij.si` (in `www`) A zapis → `178.104.24.47`. **Pozor:** `mail.zvij.si` / MX zapisi morajo ostati na starem gostovanju — preveri, da se spreminja SAMO A zapis za web!
8. **SSL**: ko DNS propagira (TTL 300 s), `sudo certbot certonly --webroot -w /var/www/zvij.si-app/public -d zvij.si -d www.zvij.si …`, nato HTTPS vhost + redirect HTTP→HTTPS in `www`→apex, `sudo nginx -t && sudo systemctl reload nginx`.
9. **Smoke testi po preklopu**:
   ```bash
   curl -I https://zvij.si/                      # 200
   curl -I http://zvij.si/                       # 301 na https
   curl -I https://www.zvij.si/                  # 301 na apex
   curl -sL https://zvij.si/trgovina/ | grep -i "vršički"
   curl -sL https://zvij.si | grep -i "http://" || echo OK-brez-mixed-contenta
   ```
   - emaili: testna prijava člana → welcome email prispe,
   - **pravi testni nakup** (majhen znesek, prava kartica) od izdelka do potrditve + račun v emailu,
   - UPN QR na strani »naročilo prejeto« se izriše.

## Faza 3 — Po preklopu (T+1 dan)

- [ ] Ponovno preveri SPF/DKIM/DMARC poravnanost (mail-tester.com) zdaj, ko web in mail nista več na istem strežniku.
- [ ] Vklopi dnevni backup prod baze (cron: `mariadb-dump` + rotacija, izven strežnika).
- [ ] Preveri Search Console/analitiko (če je vklopljena) za 404 iz starih URL-jev; dopolni 301 mapo.
- [ ] Stari hosting: pusti aktiven vsaj 30 dni (rollback + mail), potem **[JAKA]** odloči o ukinitvi web dela.
- [ ] Mehko lansiranje po checklistu (`RELEASE_PLAN.md` §5).

## Rollback

Kadarkoli pred iztekom starega gostovanja:

1. **[JAKA]** DNS `zvij.si` A zapis nazaj na `152.89.234.190` (TTL 300 s → efektivno v minutah).
2. Stara stran ni bila spreminjana, zato ni obnove podatkov.
3. `zvij-prod` stack na Hetznerju pusti pri miru (analiza), nginx vhost lahko ostane.
4. Naročila, ki so medtem nastala na novi strani, izvozi (WooCommerce → izvoz naročil) in obdelaj ročno.

## Trde meje

- Spreminja se SAMO A zapis za `zvij.si`/`www` — MX, `mail.zvij.si`, TXT (SPF/DKIM) ostanejo.
- `zvij-prod` ne sme deliti baze, volumov ali `.env` z `zvij-dev`.
- Brez potrjenega backupa live strani ni preklopa.
- Dev okolje po preklopu ostane živo za nadaljnji razvoj (deploy flow nespremenjen).
