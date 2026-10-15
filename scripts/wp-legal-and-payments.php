<?php
/**
 * Pravna besedila in plačila pred lansiranjem (Jaka, 15. 10. 2026). Idempotentno.
 *
 * - Ime firme povsod »Zvij.si d.o.o.«.
 * - Nismo zavezanci za DDV: cene so končne, DDV ni obračunan (94. člen ZDDV-1).
 * - CBD/CBG vršički se prodajajo izključno kot vršički za čaj (LEGAL_CBD_COPY_RULES.md).
 * - Plačilo samo z nakazilom na TRR (UPN QR). Revolut in povzetje izklopljena.
 * - Pogoji/vračila usklajeni z ZVPot-1 (14-dnevni odstop, 2 meseca za obvestilo
 *   o napaki, odgovornost 1 leto) in ZIsRPS (izjava o izvajalcu IRPS).
 *
 * Zagon: docker compose --profile tools run --rm wp-cli wp eval-file scripts/wp-legal-and-payments.php
 */

if (! defined('ABSPATH')) {
    exit;
}

$name = 'Zvij.si d.o.o.';
$address = 'Rjava cesta 26A, 1260 Ljubljana-Polje';
$ids = 'Matična številka: 9378294000 · Davčna številka: 68449763';
$vat = 'Nismo zavezanci za DDV — DDV ni obračunan na podlagi 1. odstavka 94. člena ZDDV-1.';
$mail = 'info@zvij.si';

/** 1. Ime firme v nastavitvah. */
update_option('zvij_invoice_seller_name', $name);
$accounts = get_option('woocommerce_bacs_accounts', []);
if (is_array($accounts)) {
    foreach ($accounts as &$account) {
        $account['account_name'] = $name;
    }
    unset($account);
    update_option('woocommerce_bacs_accounts', $accounts);
}

/** 2. Plačila: samo nakazilo na TRR z UPN QR. */
$bacs = (array) get_option('woocommerce_bacs_settings', []);
$bacs['enabled'] = 'yes';
$bacs['title'] = 'Nakazilo na TRR (UPN QR)';
$bacs['description'] = 'Po oddaji naročila dobiš UPN QR kodo: skeniraš jo z mobilno banko in vsi podatki se izpolnijo sami. Paket odpremimo, ko je plačilo vidno na našem računu.';
$bacs['instructions'] = 'Nakazilo na TRR ' . $name . '. Za sklic uporabi številko naročila. Paket odpremimo, ko je plačilo vidno na našem računu.';
update_option('woocommerce_bacs_settings', $bacs);

// Prek objekta prehoda: nekateri (npr. Revolut wallet) nimajo shranjene
// opcije in so privzeto vklopljeni, zato golo urejanje opcije ne zadošča.
foreach (WC()->payment_gateways()->payment_gateways() as $id => $gateway) {
    if ($id !== 'bacs' && $gateway->enabled === 'yes') {
        $gateway->update_option('enabled', 'no');
    }
}

/** Dostava »Po povzetju« brez povzetja ne pomeni nič. */
foreach (WC_Shipping_Zones::get_zones() as $zone_data) {
    $zone = new WC_Shipping_Zone($zone_data['id']);
    foreach ($zone->get_shipping_methods() as $method) {
        if ($method->get_title() === 'Po povzetju' && $method->is_enabled()) {
            global $wpdb;
            $wpdb->update("{$wpdb->prefix}woocommerce_shipping_zone_methods", ['is_enabled' => 0], ['instance_id' => $method->instance_id]);
        }
    }
}
WC_Cache_Helper::get_transient_version('shipping', true);

/** 3. Strani. */
$pages = [];

$pages[(int) get_option('woocommerce_terms_page_id')] = <<<HTML
<h2>Pogoji poslovanja</h2>
<p>Spletno trgovino zvij.si upravlja <strong>{$name}</strong>, {$address}. {$ids}. {$vat}</p>
<p>Z oddajo naročila potrjuješ, da si prebral te pogoje in se z njimi strinjaš.</p>
<h3>Starostna omejitev</h3>
<p>Nakup je dovoljen samo polnoletnim osebam (18+). Ob vstopu na stran in pri oddaji naročila moraš potrditi, da si star vsaj 18 let. Če utemeljeno sumimo, da kupec ni polnoleten, naročilo zavrnemo in kupnino vrnemo.</p>
<h3>Izdelki</h3>
<p>Prodajamo pribor (filtre, rizle, vžigalnike, grinderje, dodatke) in posušene konopljine vršičke CBD/CBG, ki so namenjeni izključno pripravi čaja. Vršički niso namenjeni kajenju, niso zdravilo in ne nadomeščajo zdravljenja. Niso primerni za mladoletne, nosečnice in doječe matere. Fotografije so simbolične; manjša odstopanja v barvi in videzu so možna.</p>
<h3>Cene</h3>
<p>Vse cene so v evrih in so končne. {$vat} Velja cena v trenutku oddaje naročila. Strošek dostave je prikazan na blagajni pred oddajo naročila.</p>
<h3>Plačilo</h3>
<p>Plačaš z nakazilom na naš transakcijski račun. Po oddaji naročila prejmeš podatke za plačilo in UPN QR kodo, ki jo skeniraš z mobilno banko. Kot sklic uporabi številko naročila. Če plačilo ne prispe v 8 dneh, naročilo prekličemo.</p>
<h3>Dostava</h3>
<p>Pošiljamo po Sloveniji s Pošto Slovenije. Paket odpremimo v 1–2 delovnih dneh po tem, ko je plačilo vidno na našem računu. Pakiranje je diskretno, brez zunanjih oznak vsebine.</p>
<h3>Sklenitev pogodbe in račun</h3>
<p>Pogodba je sklenjena, ko prejmeš e-mail s potrditvijo naročila. Pogodba in račun sta v slovenskem jeziku; naročilo hranimo v elektronski obliki. Račun prejmeš po e-pošti.</p>
<h3>Odstop od pogodbe in reklamacije</h3>
<p>Kot potrošnik lahko v 14 dneh odstopiš od pogodbe brez navedbe razloga. Postopek, izjeme in reklamacije so opisani na strani <a href="/refund_returns/">Vračila in reklamacije</a>.</p>
<h3>Varstvo osebnih podatkov</h3>
<p>Osebne podatke obdelujemo skladno s <a href="/privacy-policy/">Politiko zasebnosti</a>.</p>
<h3>Pritožbe in spori</h3>
<p>Pritožbo pošlji na {$mail}. Prejem potrdimo v 5 delovnih dneh in te obvestimo o nadaljnjem postopku. Spore poskušamo rešiti sporazumno. Ponudnik ne priznava nobenega izvajalca izvensodnega reševanja potrošniških sporov kot pristojnega za reševanje potrošniškega spora, ki bi ga potrošnik lahko sprožil skladno z Zakonom o izvensodnem reševanju potrošniških sporov (ZIsRPS). Če spora ne rešimo sporazumno, je pristojno stvarno pristojno sodišče v Ljubljani.</p>
HTML;

$pages[(int) get_option('woocommerce_refund_returns_page_id', 11) ?: 11] = <<<HTML
<h2>Vračila in reklamacije</h2>
<h3>Odstop od pogodbe (14 dni)</h3>
<p>Kot potrošnik lahko v 14 dneh od prevzema paketa odstopiš od pogodbe brez navedbe razloga. Odstop nam sporoči na {$mail} (zadošča kratko sporočilo s številko naročila), izdelke pa nam vrni najkasneje v 14 dneh od sporočila na naslov {$name}, {$address}. Neposredne stroške vračila kriješ sam.</p>
<p>Z izdelki ravnaj tako, kot bi jih v trgovini — za zmanjšanje vrednosti zaradi uporabe odgovarjaš ti.</p>
<h3>Izjeme</h3>
<p>Odstop ni mogoč pri zapečatenih izdelkih, ki jih iz zdravstvenih ali higienskih razlogov ni mogoče vrniti, če je bil pečat po dobavi odstranjen — na primer odprta pakiranja vršičkov za čaj ali filtrov.</p>
<h3>Vračilo kupnine</h3>
<p>Kupnino, vključno s stroški osnovne dostave, vrnemo najkasneje v 14 dneh od prejema sporočila o odstopu, na tvoj bančni račun. Vračilo lahko zadržimo, dokler izdelkov ne prejmemo nazaj.</p>
<h3>Napake in reklamacije</h3>
<p>Za skladnost izdelkov odgovarjamo, če se napaka pokaže v enem letu od dobave. Napako nam sporoči v dveh mesecih od dneva, ko si jo odkril, na {$mail}, s številko naročila in fotografijo. Če paket prispe poškodovan ali vsebuje napačne izdelke, nam piši čim prej — napako odpravimo, izdelek zamenjamo ali vrnemo kupnino, na naše stroške.</p>
HTML;

$updates = [
    18 => static fn (string $c): string => preg_replace('~<p><strong>[^<]*d\.o\.o\.</strong><br>.*?</p>~s', "<p><strong>{$name}</strong><br>{$address}<br>{$ids}<br>{$vat}</p>", $c),
    323 => static fn (string $c): string => preg_replace('~<p>[^<]*d\.o\.o\.,[^<]*</p>~', "<p>{$name}, {$address}. {$vat}</p>", $c),
    3 => static fn (string $c): string => preg_replace(
        '~Upravljavec osebnih podatkov je [^(]*\(~',
        "Upravljavec osebnih podatkov je {$name}, {$address}, {$mail} (",
        str_replace('ponudniku plačilnih storitev (za obdelavo plačila)', 'banki (za obdelavo plačila z nakazilom)', $c)
    ),
    399 => static fn (string $c): string => str_replace(
        [
            'Posušeni konopljini vršički za čaj (CBD čaj). Za mirnejši ritual, ko želiš manj THC in bolj jasno glavo. Niso namenjeni kajenju.',
            'Na voljo je nakazilo po predračunu (dobiš UPN QR kodo — skeniraš jo z mobilno banko in vsi podatki se izpolnijo sami) in plačilo po povzetju ob prevzemu. Kartično plačilo dodajamo kmalu.',
            'Dostava običajno traja 1–2 delovna dneva.',
        ],
        [
            'Posušeni konopljini vršički CBD/CBG, namenjeni pripravi čaja. Žličko prelij z vročo vodo in pusti stati 10–15 minut. Niso namenjeni kajenju in niso zdravilo.',
            'Z nakazilom na TRR. Po oddaji naročila dobiš UPN QR kodo — skeniraš jo z mobilno banko in vsi podatki se izpolnijo sami. Paket odpremimo, ko je plačilo vidno na našem računu.',
            'Paket odpremimo v 1–2 delovnih dneh po prejemu plačila.',
        ],
        $c
    ),
    16 => static fn (string $c): string => '<p>SMOKEY CBD, CHILLY CBG in FRUTTY CBD so posušeni konopljini vršički, namenjeni pripravi čaja.</p><p>Osnovno pakiranje je 1 g. Paket 5 g vsebuje pet posameznih pakiranj po 1 g.</p><p>Žličko vršičkov prelij z vročo vodo in pusti stati 10–15 minut. Vršički niso namenjeni kajenju in niso zdravilo. Samo za polnoletne; niso primerni za nosečnice in doječe matere.</p>',
];

foreach ($updates as $id => $fn) {
    $post = get_post($id);
    if ($post) {
        $pages[$id] = $fn($post->post_content);
    }
}

foreach ($pages as $id => $content) {
    if ($id && get_post($id) && get_post($id)->post_content !== $content) {
        wp_update_post(['ID' => $id, 'post_content' => $content]);
        WP_CLI::log("Stran {$id} posodobljena.");
    }
}

$cbd_page = get_post(16);
if ($cbd_page) {
    wp_update_post(['ID' => 16, 'post_excerpt' => 'SMOKEY CBD, CHILLY CBG in FRUTTY CBD — vršički za čaj v pakiranjih po 1 g in 5 g.']);
}

/** 4. Vršički: izključno za čaj. */
$flowers = [
    72 => ['CHILLY', 'CBG'],
    74 => ['SMOKEY', 'CBD'],
    76 => ['FRUTTY', 'CBD'],
];
foreach ($flowers as $id => [$line, $type]) {
    if (! get_post($id)) {
        continue;
    }
    wp_update_post([
        'ID'           => $id,
        'post_excerpt' => "{$line} {$type} vršički za čaj, v pakiranju po 1 g ali 5 g.",
        'post_content' => "<p>{$line} so posušeni konopljini {$type} vršički, namenjeni pripravi čaja.</p>"
            . '<p><strong>Priprava:</strong> žličko vršičkov prelij z vročo vodo in pusti stati 10–15 minut. Za polnejši okus dodaj kanček mleka ali masla.</p>'
            . '<p>Paket 5 g vsebuje pet posameznih pakiranj po 1 g.</p>'
            . '<p>Ni namenjeno kajenju. Ni zdravilo. Samo za polnoletne; ni primerno za nosečnice in doječe matere. Hrani zaprto, na suhem in izven dosega otrok.</p>',
    ]);
    WP_CLI::log("Izdelek {$id} posodobljen.");
}

wp_cache_flush();
WP_CLI::success('Pravna besedila in plačila posodobljena.');
