# Preklop zvij.si na novo trgovino

Pripravljeno 16. 10. 2026. Prod stack (`zvij-prod`, 127.0.0.1:8099) je
sinhroniziran z dev, očiščen testnih podatkov in utrjen. Stara stran pri
Neoservu do DNS preklopa ostane nedotaknjena.

## Pred preklopom — Jaka

1. **Backup stare strani** v cPanelu pri Neoservu: Backup → Download a Full Account Backup
   (datoteke + baza + e-pošta). Prenesi ga na svoj računalnik.
2. **Znižaj TTL** A zapisa `zvij.si` s 14400 na 300 sekund, vsaj 4 ure pred preklopom
   (cPanel → Zone Editor). Tako sta preklop in morebitna vrnitev hitra.

## Preklop — Jaka (cPanel → Zone Editor)

3. Spremeni **samo** A zapis `zvij.si` s `152.89.234.190` na `178.104.24.47`.
   - `www.zvij.si` je CNAME na `zvij.si` in sledi sam — ne spreminjaj.
   - `mail.zvij.si`, `webmail.zvij.si`, MX, SPF, DKIM, DMARC **ostanejo**, sicer
     e-pošta preneha delovati.

## Takoj po preklopu — agent

4. Zadnja sinhronizacija dev → prod (`bash scripts/sync-dev-to-prod.sh`), da prod
   dobi spremembe, narejene po 16. 10. Skripta se ustavi, če ima prod že naročila.
5. Ko `dig +short zvij.si` vrne `178.104.24.47`:
   ```bash
   sudo certbot certonly --webroot -w /var/www/zvij.si-app/public -d zvij.si -d www.zvij.si
   sudo cp .deploy/nginx/zvij.si.https.conf /etc/nginx/sites-available/zvij.si
   sudo nginx -t && sudo systemctl reload nginx
   ```
6. MailerLite webhook preregistriraj na `https://zvij.si/wp-json/zvij/v1/mailerlite-webhook`
   in nov secret shrani v `zvij_mailerlite_webhook_secret`.
7. Smoke testi: `https://zvij.si/` 200, `http://` → https, `www` → apex, stari naslovi
   (`/izdelek/smokey-premium-cbd/`, `/shop/`) → 301, `/scripts/` in `/data/` → 404.

## Prvi dan — Jaka

8. **Pravi testni nakup** z majhnim zneskom: naročilo → e-mail s QR → plačilo z
   mobilno banko → status »v obdelavi« → račun.
9. Preveri, da potrditveni e-maili prispejo (tudi v nabiralnik naročil).

## Vrnitev

A zapis `zvij.si` nazaj na `152.89.234.190`. Stara stran ni bila spremenjena.
Naročila z nove strani izvozi iz WooCommerce in obdelaj ročno.
