<?php
/**
 * Zamenja interne planerske zapiske z javnim besedilom in dopolni prazne
 * strani. Idempotentno (wp eval-file).
 *
 * Ozadje: del kataloga je bil ustvarjen kot delovni seznam komponent za kite
 * (»Draft komponenta: …«, »TBD placeholder«, »Ne mešati z RAW dodatki«).
 * Ti zapiski so bili na javno objavljenih izdelkih vidni kupcu.
 */

if (! defined('ABSPATH')) {
    exit;
}

$copy = [
    219 => [
        'short' => 'Ultra tanke King Size Slim rizle.',
        'desc'  => 'IRIE XTRA Light: zelo tanek papir v King Size Slim formatu. Osnovna, poceni izbira, ko rabiš samo rizle in nič drugega.',
    ],
    220 => [
        'short' => 'Črne XXL rizle za tih, umirjen setup.',
        'desc'  => 'JaJa Noir: črn papir v XXL formatu. Manj opazen kot bel papir in lepo sede k črnemu setupu — brez kričečih logotipov.',
    ],
    221 => [
        'short' => 'Zlati papirčki s filter konicami v enem paketu.',
        'desc'  => 'SmK Gold: papirčki in filter konice v istem zavitku. Zlata izvedba za tiste, ki gradijo setup v topli barvni shemi.',
    ],
    225 => [
        'short' => 'Zlate rolce — papir v zvitku na svojo mero.',
        'desc'  => 'SmK Gold Rolls: papir v zvitku, ki ga odviješ na dolžino, kakršno rabiš. Zlata izvedba, brez fiksnega formata.',
    ],
    224 => [
        'short' => 'Nebeljene rjave rolce, papir v zvitku.',
        'desc'  => 'Smoking Brown: nebeljen rjav papir v zvitku. Za tiste, ki imajo raje čim manj obdelave in naraven videz.',
    ],
    223 => [
        'short' => 'Srebrne rolce — tanek papir v zvitku.',
        'desc'  => 'Smoking Silver: tanek papir v zvitku, srebrna izvedba. Nevtralna izbira, ki se ujame s čistim, hladnim setupom.',
    ],
    222 => [
        'short' => 'Črne rolce — papir v zvitku za temen setup.',
        'desc'  => 'Smoking Black: črn papir v zvitku, ki ga odviješ na svojo mero. Osnova temnega, umirjenega setupa.',
    ],
];

foreach ($copy as $id => $data) {
    $product = wc_get_product($id);
    if (! $product) {
        echo "preskocim #{$id}: ni izdelka\n";
        continue;
    }
    $product->set_short_description($data['short']);
    $product->set_description($data['desc']);
    $product->save();
    echo "besedilo popravljeno #{$id} — " . $product->get_name() . "\n";
}

/**
 * Grinderja Gold/Silver sta bila objavljena kot »TBD placeholder« z DEV ceno —
 * po lastnem opisu ujemajočega modela še ni. Objavljen izdelek brez zaloge in
 * brez nabave je nedostavljivo naročilo, zato gresta v osnutek.
 */
foreach ([216 => 'zlat grinder', 217 => 'srebrn grinder'] as $id => $what) {
    $product = wc_get_product($id);
    if (! $product) {
        continue;
    }
    $product->set_short_description('Ujemajoč ' . $what . ' za kit — v pripravi.');
    $product->set_description('Model za to barvo setupa še izbiramo. Ko bo na zalogi, ga najdeš tukaj.');
    if ($product->get_status() !== 'draft') {
        $product->set_status('draft');
    }
    $product->save();
    echo "osnutek #{$id} — " . $product->get_name() . " (ni nabave, DEV cena)\n";
}

/** Prazna oz. interna besedila strani. */
$pages = [
    'o-nas' => [
        'title'   => 'O nas',
        'content' => '<p>Zvij.si je nastal iz preproste stvari: setup, ki ga imaš rad, se ne sme razpasti, ker ti je zmanjkalo ene malenkosti.</p>
<p>Zato ne prodajamo naključnega pribora. Prodajamo stvari, ki gredo skupaj — filtre, rizle in rolce, vžigalnike, grinderje, vršičke za čaj in embalažo, ki vse to drži na enem mestu. Tvoj ritual. Tvoja mera. Tvoj setup.</p>
<h3>Zakaj članstvo</h3>
<p>Član Zvij.si ne lovi akcij. Ob nakupu zbira kristale, ki jih porabi pri naslednjem naročilu, dobi svojo Zvij kodo za prijatelje in opomnik takrat, ko mu stvari dejansko poidejo — ne prej.</p>
<h3>Kdo stoji za tem</h3>
<p>ZVIJ.si, spletna prodaja, d.o.o., Rjava cesta 26A, 1260 Ljubljana-Polje. Podjetje ni zavezanec za DDV (1. odstavek 94. člena ZDDV-1).</p>
<p>Prodaja izključno polnoletnim osebam (18+).</p>',
    ],
    'kontakt' => [
        'title'   => 'Kontakt',
        'content' => '<p>Piši nam glede naročila, izdelkov ali članstva — odgovorimo praviloma isti ali naslednji delovni dan.</p>
<p><strong>E-pošta:</strong> <a href="mailto:info@zvij.si">info@zvij.si</a></p>
<p>Če gre za obstoječe naročilo, dopiši številko naročila — tako ti lahko odgovorimo takoj, brez dodatnega spraševanja.</p>
<p><strong>ZVIJ.si, spletna prodaja, d.o.o.</strong><br>Rjava cesta 26A, 1260 Ljubljana-Polje<br>Matična številka: 9378294000 · Davčna številka: 68449763</p>',
    ],
];

foreach ($pages as $slug => $data) {
    $page = get_page_by_path($slug);
    if (! $page) {
        echo "preskocim /{$slug}/: strani ni\n";
        continue;
    }
    wp_update_post([
        'ID'           => $page->ID,
        'post_title'   => $data['title'],
        'post_content' => $data['content'],
    ]);
    echo "stran /{$slug}/ dopolnjena (" . strlen(strip_tags($data['content'])) . " znakov)\n";
}

/** Nastavitve, ki gredo z bazo v produkcijo. */
$options = [
    'blogname'                                => 'Zvij.si',
    'blogdescription'                         => 'Tvoj ritual. Tvoja mera. Tvoj setup.',
    'woocommerce_email_from_name'             => 'Zvij.si',
    'woocommerce_checkout_privacy_policy_text' => 'Tvoje osebne podatke uporabimo za obdelavo naročila, podporo pri nakupu in namene, opisane v [privacy_policy].',
    'woocommerce_registration_privacy_policy_text' => 'Tvoje osebne podatke uporabimo za podporo pri nakupu in namene, opisane v [privacy_policy].',
];
foreach ($options as $key => $value) {
    $old = get_option($key);
    if ($old === $value) {
        continue;
    }
    update_option($key, $value);
    echo "opcija {$key}: " . var_export($old, true) . " → {$value}\n";
}
