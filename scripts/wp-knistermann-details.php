<?php
/**
 * Podrobnosti Knistermannovih izdelkov, prepisane z dobaviteljevih strani
 * (prijavljena B2B seja, 1. 9. 2026). Ujemanje artiklov je preverjeno po
 * številki artikla, ne po imenu.
 *
 * Ob tem poravna SKU Clipper Gold s številko artikla z računa (bila je
 * spletna šifra 7656, artikel je CLIP-FZ-418).
 */

if (! defined('ABSPATH')) {
    exit;
}

$products = [
    211 => [ // CLIP-FZ-239 — Clipper groß, 48er Display, SOFT TOUCH BLACK - Black Cap
        'sku'   => 'CLIP-FZ-239',
        'short' => 'Clipper large, soft touch črn — ponovno polnljiv, z zamenljivim kresilnim kamnom.',
        'desc'  => 'Clipper large v mat črni soft touch izvedbi z črnim pokrovčkom. Kresilni vžigalnik, napolnjen z izobutanom.'
                 . "\n\n" . 'Kar ga loči od poceni vžigalnikov: <strong>izvlečljiv kresilni vložek</strong> — kamen lahko sam nastaviš ali zamenjaš, ko se obrabi, in vžigalnik <strong>ponovno napolniš</strong> (plin najdeš pri nas). Namesto da ga zavržeš, ga vzdržuješ.'
                 . "\n\n" . 'Varovalo za otroke. Certificiran po standardu ISO 9994.',
    ],
    213 => [ // CLIP-FZ-418 — Clipper groß, 12er Display, METALL GOLD
        'sku'   => 'CLIP-FZ-418',
        'short' => 'Kovinski zlat Clipper large v darilni pločevinki.',
        'desc'  => 'Clipper large s kovinskim ohišjem v zlati barvi. Priložena je kovinska darilna pločevinka — zato je to edini vžigalnik pri nas, ki ga lahko podariš, ne da bi karkoli dodal.'
                 . "\n\n" . 'Enak sistem kot pri črnem: <strong>izvlečljiv kresilni vložek</strong> za menjavo kamna in <strong>ponovno polnjenje</strong> z izobutanom. Kovina je težja in obstojnejša od plastike — kos setupa, ne potrošni material.'
                 . "\n\n" . 'Varovalo za otroke. Certificiran po standardu ISO 9994.',
    ],
    445 => [ // FZG-Z-44 — Clipper Isobutangas MINI, 16ml
        'sku'   => 'FZG-Z-44',
        'short' => 'Izobutan za polnjenje Clipper vžigalnikov — mini pločevinka 16 ml.',
        'desc'  => 'Clipper izobutan v mini pločevinki za ponovno polnjenje vžigalnikov. <strong>Vsebina: 16 ml (8,85 g)</strong>, velikost za v žep ali predal.'
                 . "\n\n" . 'En Clipper napolniš večkrat, namesto da kupiš novega — setup vzdržuješ, ne menjaš.',
    ],
    216 => [ // POLI-110 — CNC Aluminium Grinder/Pollinator 4 part, ø 52mm, GOLD
        'sku'   => 'POLI-110',
        'short' => 'Štiridelni CNC aluminijast grinder s sitom, ø 52 mm, zlat.',
        'desc'  => 'CNC obdelan aluminijast grinder v zlati barvi z vtisnjenim vzorcem. <strong>Štiridelni</strong>: mlinček, sito ter privijačen predalček za shranjevanje.'
                 . "\n\n" . '<strong>Premer ok. 52 mm, višina 34 mm.</strong> CNC frezani diamantni zobci, <strong>magnetno zapiranje</strong> (drži trdno in se ne odpira sam) in nylonski drsni obroč, da se vrti gladko. Priložena je lopatka.'
                 . "\n\n" . 'Največji grinder v naši ponudbi — tak, ki ostane na mizi in ne konča v predalu.',
    ],
    217 => [ // GRI-M-03 — CNC Aluminium Mühle/Grinder, 40mm, 2-teilig
        'sku'   => 'GRI-M-03',
        'short' => 'Kompakten dvodelni CNC aluminijast grinder, ø 40 mm.',
        'desc'  => 'Dvodelni CNC obdelan aluminijast grinder s CNC frezanimi diamantnimi zobci. <strong>Premer ok. 40 mm, višina 20 mm.</strong>'
                 . "\n\n" . 'Brez sita in predalčka — samo mletje. Gre v žep ali v majhen setup, kjer za velikega ni prostora.',
    ],
];

foreach ($products as $id => $data) {
    $product = wc_get_product($id);
    if (! $product) {
        echo "preskocim #{$id}: ni izdelka\n";
        continue;
    }
    $old_sku = $product->get_sku();
    if ($old_sku !== $data['sku']) {
        printf("#%d SKU: %s → %s\n", $id, $old_sku, $data['sku']);
        $product->set_sku($data['sku']);
    }
    $product->set_short_description($data['short']);
    $product->set_description(wpautop($data['desc']));
    $product->save();
    printf("#%-4d %-40s opis %d znakov\n", $id, mb_substr($product->get_name(), 0, 39), mb_strlen(strip_tags($data['desc'])));
}
