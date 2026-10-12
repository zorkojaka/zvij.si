# Povabila in kristali

Aktualno pravilo od 12. 10. 2026. Implementacija: `zvij-invitations.php` in
`zvij-invitation-admin.php`. Ta zapis nadomesti fiksno nagrado iz starega
`REFERRAL_SPEC.md` za okolja, kjer so nova povabila omogocena.

## Kaj dobi uporabnik

V Mojem racunu ima osebno javno povezavo, lokalno ustvarjeno QR-kodo,
prenos QR-kode, kopiranje povezave, deljenje prek sistema telefona ter
WhatsApp, Facebook, Telegram in X.

Ko prijatelj prek povezave ustvari nov WooCommerce racun, se shrani njegov
neposredni povabitelj. Za vsak prijateljev placan nakup povabitelj prejme
dodatnih 10 % kristalov, ki so prijatelju dejansko pripisani za ta nakup.
100 prijateljevih kristalov pomeni 10 dodatnih kristalov za povabitelja.
Prijatelju ostanejo vsi njegovi kristali. Sam nastanek racuna ne pripise bonusa.

Bonus je `intdiv(kristali_iz_nakupa, 10)`: zaokrozi se navzdol na cel kristal.
Bonusov iz prijateljevih povabil ne vstevamo. Ni vecstopenjskega nagrajevanja.
Zgodovinskih fiksnih nagrad ne prerasacunavamo in jim ne dodajamo nove nagrade.
Popust s staro Zvij kodo za prvo narocilo ostaja locen obstojeci mehanizem.

## Identiteta in privolitev

Javna povezava vsebuje nov nakljucni token. Ne vsebuje osebne Zvij kode, ki
lahko gostu omogoci unovcenje kristalov.
Povabilo se prenese prek podpisanega HttpOnly, SameSite=Lax piskotka za 30 dni;
na HTTPS je Secure. Prvo veljavno povabilo ostane. Podpis, starost in lastnik
se preverijo ob registraciji. Nevezanih ali neveljavnih povezav ne pripisujemo.
Registriranega prijatelja ni mogoce prenesti k drugemu povabitelju.

Nov trgovinski racun ustvari zapis za zvestobo s statusom `account`,
`consent_at = NULL` in `provider_status = not_subscribed`. Ne prijavi ga na
marketing in ne klice MailerLite. Izrecna prijava prek obstojecega obrazca
ostaja locena ter zabelezi datum, IP hash in razlicico privolitve.

## Sledljivost in storno

- `zvij_invitation_links`: javni token in lastnik; en token na clana.
- `zvij_invitations`: lastnik, ID prijateljevega racuna, email, vir, datum;
  unikatna prijateljev racun in email.
- Ledger: `referral_bonus` in `referral_reversal`, prejemnik, prijatelj,
  narocilo, znesek, datum in opis osnove.
- Unikaten nullable `event_key` v ledgerju preprecuje podvojen bonus ali
  storno ob ponovljenem statusnem hooku. Obstojeci ledger ostane ohranjen.
- Narocila pred registracijo povabila ne ustvarjajo naknadnih bonusov.
- Samopovabilo in neujemanje kupcevega racuna se zavrneta.
- Bonus se pripise po obstojecevem pripisu kristalov (hook 36 po hooku 30).
  Manjkajoc dejanski ledger pripis ne zadosca, tudi ce ima narocilo meta podatek.
- Preklic, neuspeh placila ali status `refunded` enkrat obrne cel bonus.
  Delna vracila sledijo obstojecemu sistemu kristalov; ta nagrado kupca
  trenutno stornira sele ob celotnem preklicu/vracilu narocila.

## Admin

Zvij.si > Povabila in kristali: obdobje 7/30 dni ali ves cas, pregled
povabiteljev, stevilo prijateljev in kupcev, pripisani/stornirani/neto bonusi,
trenutno stanje, seznam prijateljev izbranega clana, viri povezav in
paginiran dnevnik vseh virov kristalov. Dnevnik se filtrira po clanu in vrsti.
Vir povezave je oznaka v deljeni povezavi, ne neodvisna potrditev druzbenega omrezja.
Osebni podatki so dostopni samo z `manage_woocommerce`.

## Okolja in preverjanje

Razvoj in produkcija trenutno delita datoteke. Funkcionalnost je privzeto
omogocena samo pri `WP_ENVIRONMENT_TYPE=development`. V produkciji ostane
staro pravilo in stara razlicica sheme; nova registracija se ne vklopi.
Poznejsi izrecni prehod omogoci opcija `zvij_invitations_enabled=yes`.
Instalacija novih tabel takrat vklopi WooCommerce registracijo v Mojem racunu.

Integration test (samo dev):

```sh
docker exec zvij-dev-wordpress php /var/www/html/scripts/test-invitations.php
```

Test uporablja transakcijo in preveri rollback clanov, uporabnikov, povezav,
ledgerja in objav. Ne posilja emailov ali zunanjih HTTP zahtev.
Preverjanje v brskalniku dodatno dekodira QR, preizkusi clipboard in povezave
za deljenje ter preveri racun pri sirinah 1440 in 390 px.
