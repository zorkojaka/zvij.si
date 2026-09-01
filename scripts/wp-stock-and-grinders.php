<?php
/**
 * Vnos potrjenih zalog in objava grinderjev (Jakova potrditev 1. 9. 2026).
 *
 * - DUBI 420 = 5 paketov, DUBI 42 = 50 (dejansko stanje; račun Knistermann
 *   je bil 15 oz. 50 — 10 velikih je že porabljenih/prodanih).
 * - Zlat in mali srebrn grinder sta na zalogi → nazaj v objavo, s cenami in
 *   količinami iz cenika (POLI-110 in GRI-M-03).
 * - Clipper Silver ostane osnutek — tega Jaka nima.
 *
 * Zagon: docker compose --profile tools run --rm wp-cli wp eval-file scripts/wp-stock-and-grinders.php
 */

if (! defined('ABSPATH')) {
    exit;
}

/** Potrjene zaloge na obstoječih izdelkih. */
$stock = [
    69 => 5,   // DUBI 420 aktivnih ogljikovih filtrov
    71 => 50,  // DUBI 42 aktivnih ogljikovih filtrov
];

foreach ($stock as $id => $qty) {
    $product = wc_get_product($id);
    if (! $product) {
        echo "preskocim #{$id}: ni izdelka\n";
        continue;
    }
    $product->set_manage_stock(true);
    $product->set_stock_quantity($qty);
    $product->set_backorders('no');
    $product->save();
    printf("zaloga #%d %s = %d\n", $id, $product->get_name(), $qty);
}

/**
 * Grinderja nazaj v objavo. Cene in količine iz cenika:
 * POLI-110 — CNC 4-delni ø 52 mm, zlat, 1 kos, MPC 34,90 €
 * GRI-M-03 — CNC 2-delni 40 mm, mali, 3 kosi, MPC 8,90 €
 */
$grinders = [
    216 => [
        'sku'   => 'POLI-110',
        'name'  => 'Zlat grinder, 4-delni (ø 52 mm)',
        'price' => 34.90,
        'stock' => 1,
        'short' => 'Štiridelni aluminijast grinder, ø 52 mm, v zlati izvedbi.',
        'desc'  => 'CNC obdelan aluminijast grinder s štirimi deli: mlinček, sito in spodnji prekat. Velik premer 52 mm in prava teža — tak, ki ostane na mizi in ne konča v predalu. Zlata izvedba za topel setup.',
    ],
    217 => [
        'sku'   => 'GRI-M-03',
        'name'  => 'Mali srebrn grinder, 2-delni (40 mm)',
        'price' => 8.90,
        'stock' => 3,
        'short' => 'Kompakten dvodelni aluminijast grinder, 40 mm.',
        'desc'  => 'Manjši dvodelni CNC grinder iz aluminija, premer 40 mm. Brez sita in prekata — samo mletje, v žepu ali v Throwie vrečki. Srebrna, nevtralna izvedba.',
    ],
];

foreach ($grinders as $id => $data) {
    $product = wc_get_product($id);
    if (! $product) {
        echo "preskocim #{$id}: ni izdelka\n";
        continue;
    }
    $product->set_name($data['name']);
    $product->set_sku($data['sku']);
    $product->set_regular_price((string) $data['price']);
    $product->set_short_description($data['short']);
    $product->set_description($data['desc']);
    $product->set_manage_stock(true);
    $product->set_stock_quantity($data['stock']);
    $product->set_backorders('no');
    $product->set_catalog_visibility('visible');
    $product->set_status('publish');
    $product->save();
    printf("objavljen #%d %s — %.2f EUR, zaloga %d (SKU %s)\n", $id, $data['name'], $data['price'], $data['stock'], $data['sku']);
}
