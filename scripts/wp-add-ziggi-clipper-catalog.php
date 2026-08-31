<?php
/**
 * Doda Ziggi rizle/rolce in Clipper plin v katalog ter uskladi cene in zaloge
 * vzigalnikov po racunih (Ziggi SI, avgust 2026; Knistermann proforma
 * 2026143695 z dne 12. 8. 2026 — isti vzigalniki, locena dostava nevarnega
 * blaga, zato zaloga NI podvojena).
 *
 * Idempotentno: izdelki se iscejo po SKU, obstojeci se posodobijo.
 * Zagon: docker compose --profile tools run --rm wp-cli wp eval-file scripts/wp-add-ziggi-clipper-catalog.php
 */

if (! defined('ABSPATH')) {
    exit;
}

/** Novi izdelki: SKU => podatki. Zaloge so kosi z racunov. */
$new_products = [
    'AP6945' => [
        'name'  => 'Ziggi Original Classic Slim',
        'price' => 2.30,
        'stock' => 26,
        'cat'   => 'Rizle',
        'short' => 'King Size Slim zavitek s pokrovčkom in filter konicami.',
        'desc'  => 'Ziggi Original v klasični Slim izvedbi: tanek papir, zavitek s pokrovčkom in filter konice v istem paketu. Osnovna izbira za urejen setup — vzameš enega in ne razmišljaš več.',
    ],
    'AP7186' => [
        'name'  => 'Ziggi Hemp Classic Slim',
        'price' => 2.30,
        'stock' => 26,
        'cat'   => 'Rizle',
        'short' => 'Konopljin papir King Size Slim s pokrovčkom in filter konicami.',
        'desc'  => 'Ziggi Hemp: papir iz konopljinih vlaken, bolj naraven odtenek in oprijem kot Original. Zavitek s pokrovčkom in filter konice v paketu.',
    ],
    'AP7176' => [
        'name'  => 'Ziggi Natural Classic Slim',
        'price' => 2.30,
        'stock' => 26,
        'cat'   => 'Rizle',
        'short' => 'Nebeljen papir King Size Slim s pokrovčkom in filter konicami.',
        'desc'  => 'Ziggi Natural: nebeljen papir za tiste, ki imajo raje čim manj obdelave. Zavitek s pokrovčkom in filter konice v paketu.',
    ],
    'AP7731' => [
        'name'  => 'Ziggi Mystery Mix Special Edition',
        'price' => 2.50,
        'stock' => 26,
        'cat'   => 'Rizle',
        'short' => 'Posebna izdaja Ziggi Original — Mystery Mix.',
        'desc'  => 'Ziggi Original Special Edition Mystery Mix: enak Slim format in vsebina kot Original, druga grafika. Zbirateljska serija — ko poide, poide.',
    ],
    'AP7423' => [
        'name'  => 'Ziggi Mycelium Mystique Special Edition',
        'price' => 2.50,
        'stock' => 26,
        'cat'   => 'Rizle',
        'short' => 'Posebna izdaja Ziggi Original — Mycelium Mystique.',
        'desc'  => 'Ziggi Original Special Edition Mycelium Mystique: enak Slim format in vsebina kot Original, druga grafika. Zbirateljska serija — ko poide, poide.',
    ],
    'AP7413' => [
        'name'  => 'Ziggi Rocket\'s Odyssey Special Edition',
        'price' => 2.50,
        'stock' => 26,
        'cat'   => 'Rizle',
        'short' => 'Posebna izdaja Ziggi Original — Rocket\'s Odyssey.',
        'desc'  => 'Ziggi Original Special Edition Rocket\'s Odyssey: enak Slim format in vsebina kot Original, druga grafika. Zbirateljska serija — ko poide, poide.',
    ],
    'AP7115' => [
        'name'  => 'Ziggi Wide Extra',
        'price' => 2.50,
        'stock' => 44,
        'cat'   => 'Rizle',
        'short' => 'Širši zavitek Ziggi Original — Wide Extra, s pokrovčkom in konicami.',
        'desc'  => 'Ziggi Original Wide Extra: širši format papirja za tiste, ki jim je Slim pretesen. Zavitek s pokrovčkom in filter konice v paketu.',
    ],
    'AP7751' => [
        'name'  => 'Ziggi Original Roll + Tips + Tray',
        'price' => 2.90,
        'stock' => 16,
        'cat'   => 'Rolce',
        'short' => 'Zvitek papirja, filter konice in podlaga v enem paketu.',
        'desc'  => 'Ziggi Original Roll: papir v zvitku, ki ga odviješ na svojo mero, plus filter konice in podlaga (tray) v isti embalaži. Za tiste, ki ne marajo fiksne dolžine.',
    ],
    'AP7753' => [
        'name'  => 'Ziggi Natural Roll + Tips + Tray',
        'price' => 2.90,
        'stock' => 16,
        'cat'   => 'Rolce',
        'short' => 'Nebeljen zvitek papirja, filter konice in podlaga v enem paketu.',
        'desc'  => 'Ziggi Natural Roll: nebeljen papir v zvitku na svojo mero, plus filter konice in podlaga (tray) v isti embalaži.',
    ],
    'AP7105' => [
        'name'  => 'Ziggi Original Double',
        'price' => 4.20,
        'stock' => 14,
        'cat'   => 'Rizle',
        'short' => 'Dvojni zavitek Ziggi Original Classic Slim.',
        'desc'  => 'Ziggi Original Double: dvojna vsebina v eni embalaži. Enak Slim papir kot Original, samo dlje traja — smiselno, če veš, da porabiš.',
    ],
    'FZG-Z-44' => [
        'name'  => 'Clipper plin za polnjenje, 16 ml',
        'price' => 2.50,
        'stock' => 25,
        'cat'   => 'Vžigalniki',
        'short' => 'Univerzalni izobutan za polnjenje Clipper vžigalnikov, 16 ml.',
        'desc'  => 'Pločevinka 16 ml z nastavki za polnjenje Clipper vžigalnikov. En Clipper napolniš večkrat, namesto da kupiš novega — setup vzdržuješ, ne menjaš.',
    ],
];

/** Popravki obstojecih izdelkov: ID => spremembe. */
$updates = [
    211 => [ // Clipper Black — soft touch, 48/display
        'price' => 3.33,
        'stock' => 48,
        'short' => 'Clipper large v mat črni soft touch izvedbi.',
        'desc'  => 'Clipper large, soft touch črn: mehak oprijem, zamenljiv kresilni sistem in ponovno polnjenje. Vžigalnik, ki ga obdržiš, ne zavržeš.',
    ],
    213 => [ // Clipper Gold — metall, 12/display
        'price' => 14.20,
        'stock' => 12,
        'short' => 'Clipper large v kovinski zlati izvedbi.',
        'desc'  => 'Clipper large Metall Gold: kovinsko ohišje, zamenljiv kresilni sistem in ponovno polnjenje. Težji in obstojnejši od plastičnega — kos setupa, ne potrošni material.',
    ],
];

/** Izdelki, ki gredo v osnutek (placeholderji brez nabave). */
$to_draft = [
    333 => 'placeholder "Ziggi Rolls" — nadomescen s pravimi Ziggi izdelki',
    227 => 'Clipper Silver — ni na nobenem racunu, cena ni potrjena',
];

$term_cache = [];
$resolve_cat = static function (string $name) use (&$term_cache) {
    if (isset($term_cache[$name])) {
        return $term_cache[$name];
    }
    $map = [];
    $needle = $map[$name] ?? $name;
    $term = get_term_by('name', $needle, 'product_cat');
    if (! $term) {
        echo "OPOZORILO: kategorija '{$needle}' ne obstaja\n";
        return null;
    }
    return $term_cache[$name] = (int) $term->term_id;
};

$created = [];

foreach ($new_products as $sku => $data) {
    $existing = wc_get_product_id_by_sku($sku);
    $product = $existing ? wc_get_product($existing) : new WC_Product_Simple();

    $product->set_name($data['name']);
    $product->set_sku($sku);
    $product->set_regular_price((string) $data['price']);
    $product->set_short_description($data['short']);
    $product->set_description($data['desc']);
    $product->set_manage_stock(true);
    $product->set_stock_quantity($data['stock']);
    $product->set_backorders('no');
    $product->set_catalog_visibility('visible');
    $product->set_status('publish');

    $cat_id = $resolve_cat($data['cat']);
    if ($cat_id) {
        $product->set_category_ids([$cat_id]);
    }

    $id = $product->save();
    $created[$sku] = $id;
    printf("%s %s (#%d) — %.2f EUR, zaloga %d, %s\n", $existing ? 'posodobljen' : 'ustvarjen', $data['name'], $id, $data['price'], $data['stock'], $data['cat']);
}

foreach ($updates as $id => $data) {
    $product = wc_get_product($id);
    if (! $product) {
        echo "preskocim #{$id}: ni izdelka\n";
        continue;
    }
    $old = $product->get_price();
    $product->set_regular_price((string) $data['price']);
    $product->set_short_description($data['short']);
    $product->set_description($data['desc']);
    $product->set_manage_stock(true);
    $product->set_stock_quantity($data['stock']);
    $product->set_backorders('no');
    $product->save();
    printf("posodobljen #%d %s — cena %s → %.2f EUR, zaloga %d\n", $id, $product->get_name(), $old, $data['price'], $data['stock']);
}

foreach ($to_draft as $id => $reason) {
    $product = wc_get_product($id);
    if (! $product) {
        echo "preskocim #{$id}: ni izdelka\n";
        continue;
    }
    if ($product->get_status() !== 'draft') {
        $product->set_status('draft');
        $product->save();
    }
    printf("osnutek #%d %s — %s\n", $id, $product->get_name(), $reason);
}

echo "\nNovi ID-ji za placeholder skripto: " . implode(', ', $created) . "\n";
