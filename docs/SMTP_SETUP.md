# SMTP dostava emailov (MUST LAUNCH #2)

Vtičnik **WP Mail SMTP** je nameščen in aktiviran (provisionira `scripts/wp-install-dev.sh`).
Poskrbi, da WordPress/WooCommerce emaili (potrditev naročila, račun ZVIJ-08,
welcome email člana) dejansko odidejo prek poštnega strežnika `mail.zvij.si`.

## Nastavljeno programsko

| Nastavitev | Vrednost |
|---|---|
| Mailer | SMTP |
| Host | `mail.zvij.si` |
| Pošiljatelj (From) | `zvijace@zvij.si` — »Zvij.si« (Force From vklopljen) |
| Uporabnik | `zvijace@zvij.si` |
| Avtentikacija | da |
| **Port / šifriranje (dev)** | **587 / TLS (STARTTLS)** |

### Zakaj 587/TLS na dev, ne 465/SSL

Predlagane cPanel nastavitve so 465/SSL, a **omrežje dev okolja blokira odhodni
port 465 (in 25)** — 587 je odprt in doseže isti strežnik. Povezljivost na
`mail.zvij.si:587` je preverjena (Exim odgovori).

**Produkcija (zvij.si):** ker bo WordPress na istem gostovanju kot poštni strežnik,
465/SSL tam deluje. Lahko pa se pusti tudi 587/TLS — oboje je pravilno.

## Kaj mora narediti Jaka (edini korak)

1. WP admin → **WP Mail SMTP → Nastavitve**.
2. V polje **SMTP Password** vpiši geslo poštnega predala `zvijace@zvij.si` in
   **Shrani**. (Geslo ostane v bazi, ne v Gitu ne v Git zgodovini.)
3. Zavihek **Email Test** → pošlji testni email nase → preveri, da prispe.

Če test na 587/TLS ne uspe, poskusi še 465/SSL (na produkciji), sicer preveri
geslo predala.

## Opomba za produkcijo
- Za dobro dostavljivost naj bodo urejeni **SPF/DKIM/DMARC** DNS zapisi za zvij.si
  (MUST LAUNCH #4). Ker pošiljamo prek `mail.zvij.si` (lastni strežnik domene),
  SPF/DKIM praviloma poravnata za DMARC.
