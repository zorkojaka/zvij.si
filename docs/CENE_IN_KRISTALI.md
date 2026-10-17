# Model cen in kristalov

Datum: 4. 9. 2026 · Velja za `zvij-core` 0.14.0

Ta dokument je edini opis modela. Če se koda in ta zapis razideta, je narobe koda.

## Enota

**Enota je en kos izdelka.** Njegova cena je veljavna WooCommerce cena — torej
akcijska, kadar akcija teče, sicer redna. Nikjer v naši kodi ne računamo z redno
ceno, kadar obstaja akcijska: sicer bi kupec pri treh kosih plačal več kot pri
enem, ker bi popust računal z višje osnove.

Osnovo definira ena sama funkcija: `zvij_qty_base_price()`.

## Količinski popust

Tri stopnje, prag je količina v košarici:

| Količina | Popust |
|---|---|
| 3 kosi | −10 % |
| 10 kosov | −15 % |
| cela škatla | −20 % |

Cela škatla je vrh iste lestvice, ne posebna ponudba. Prag je pakiranje
dobavitelja in se vpiše na izdelek (`_zvij_box_qty`, polje »Cela škatla
(kosov)«); ta vpis hkrati vklopi celotno lestvico. Brez njega izdelek nima
količinskega popusta.

Nastavitve: `zvij_qty_tiers` (`[3 => 10, 10 => 15]`), `zvij_box_discount` (20).

**Zakaj količinski prag in ne ločen izdelek »škatla 26 kos«:** zaloga bi bila
dvakrat za isto blago in bi se razšla, katalog bi se podvojil, popusta pa ne bi
dobil tisti, ki naroči 30 kosov namesto natanko 26.

Formula popusta obstaja na enem mestu — `zvij_qty_unit_price()`. Vse ostalo
(prikaz na izdelku, cena v košarici, spodbude) kliče njo ali izpeljanki
`zvij_qty_line_total()` in `zvij_qty_saving()`.

## Kristali

## Stopnje članstva in kristali iz marže (16. 10. 2026)

Ko je vklopljeno `zvij_tiers_enabled` (Zvij.si → Stopnje članstva), kristali niso
več delež končne cene, ampak **delež marže**: plačana cena postavke (po vseh
popustih) minus nabavna cena × količina, pomnoženo z deležem stopnje člana.
Marža pod nič da 0 kristalov. Koda: `includes/zvij-tiers.php`.

| Stopnja | Delež marže | Kako |
|---|---|---|
| 1 | 5 % | ob včlanitvi |
| 2 | 15 % | poraba ≥ 100 € v zadnjih 12 mesecih |
| 3 | 25 % | ≥ 250 € |
| 4 | 35 % | ≥ 500 € |
| 5 (VIP) | 50 % | ≥ 1000 € ali ročno |

Poraba = plačana naročila brez dostave; naročilo, ki se pravkar plačuje, se ne
šteje (stopnja pred njim). Ročna stopnja ima prednost in ima lahko lasten delež
(npr. 35–50 % za VIP). Isti seznam članov omogoča **brezplačno poštnino**:
velja za prijavljenega člana, pokrije »Navadno poštnino«, nadgradnje ostanejo
plačljive.

**Nabavna cena** je meta `_zvij_cost` (bruto na kos — nismo zavezanci za DDV),
polje »Nabavna cena« pri ceni izdelka ali variacije. Izdelek brez nje ostane na
starem pravilu (spodaj); seznam takih je na strani nastavitev. Začetne vrednosti
iz `Zvij_cenik_in_marze.xlsx`: `scripts/wp-import-costs.php`.

Strankam se marža nikoli ne prikaže: na izdelku vidijo kristale za svojo stopnjo
(gost vidi stopnjo 1), v Mojem računu pa stopnjo in koliko jim manjka do naslednje.
Kit bonus in Zvij koda delujeta kot prej.

Test: `docker exec -u 33 zvij-dev-wordpress php /var/www/html/scripts/test-tiers.php`.

### Staro pravilo (velja, ko so stopnje izklopljene ali izdelek nima nabavne cene)

**En kristal je en cent — 100 kristalov = 1 €.** Tečaj je zapisan enkrat, v
konstanti `ZVIJ_KRISTALI_PER_EUR`.

Koliko kristalov da izdelek, določa pravilo, ne ročno vpisana številka. Tri
ravni, od splošne k posebni:

1. **privzeto pravilo** — `zvij_credit_reward_percent`, 10 % cene nazaj;
2. **pravilo kategorije** — `zvij_credit_reward_by_cat`, trenutno rizle in
   rolce 5 % (tanjši izdelek);
3. **izjema na izdelku** — meta `_zvij_kristali`, absolutno število.

Kristali se množijo s količino, zato večja količina pomeni hkrati nižjo ceno in
več kristalov.

**Izjemo vpiši samo, kadar izdelek namenoma odstopa od pravila.** Sicer pusti
polje prazno, da vrednost sledi ceni. Ob uvedbi tega modela je bilo ročno
vpisanih 24 vrednosti; ostalo jih je 9, in vsaka od njih nekaj pove.

### Izjeme, ki trenutno veljajo

| Izdelek | Vpisano | Pravilo bi dalo | Zakaj |
|---|---|---|---|
| DUBI 42 | 130 | 80 | 16 % nazaj — nosilni reload izdelek |
| CHILLY 1 g | 100 | 75 | 13 % |
| CHILLY 5 g | 450 | 369 | 12 % |
| FRUTTY 1 g | 80 | 42 | 19 % — izdelek je v akciji, kristali ostajajo po redni ceni |
| FRUTTY 5 g | 350 | 249 | 14 % |

Če katera od teh izjem nima več razloga, jo izbriši in vrednost bo sledila
pravilu. Ni je treba nadomestiti z novo številko.

## Javni napis

Stavek »Član prejme X kristalov za naslednji reload.« se **izpelje** iz
vrednosti prek `zvij_credit_public_note()`. Prej je bil shranjen v meta
`_zvij_dobroimetje_note` z vpisano številko — isti podatek na dveh mestih se je
ob spremembi tečaja razšel (sistem je pripisoval 130, na strani je pisalo 13).
Meta je odstranjena; ne uvajaj je nazaj.

## Vršički po gramih (17. 10. 2026)

CBD/CBG vršički se ne prodajajo več v pakiranjih 1 g / 5 g. Kupec na strani
izdelka z drsnikom ali številko izbere grame; v košarico gre variacija »1 g«
s količino = grami. Cena na gram pade na mejah iz opcije `zvij_gram_tiers`:

| grami | popust na gram |
|---|---|
| 1–4 g | osnovna cena |
| od 5 g | −10 % |
| od 25 g | −20 % |

Izračun je isti količinski motor kot pri rizlah (`zvij-quantity-pricing.php`,
filter `zvij_qty_ladder`), koda v `zvij-core/includes/zvij-grams.php`.
Variacije »5 g« so zasebne (izklopljene), ne izbrisane. Preklop:
`scripts/wp-grams-switch.php` (idempotentno). Meje spremeniš z
`wp option update zvij_gram_tiers '{"5":10,"25":20}' --format=json`.
