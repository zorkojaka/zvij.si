<?php
/**
 * Jakova odločitev (1. 9. 2026):
 * - JollySafe: cena 4,20 € in zaloga 5 kosov na barvo;
 * - Throwie kit se skrije, dokler ni vrečk na zalogi (definicija ostane).
 */

if (! defined('ABSPATH')) {
    exit;
}

foreach ([243, 244, 245] as $id) {
    $product = wc_get_product($id);
    if (! $product) {
        continue;
    }
    $old = $product->get_price();
    $product->set_regular_price('4.20');
    $product->set_manage_stock(true);
    $product->set_stock_quantity(5);
    $product->set_backorders('no');
    $product->save();
    printf("#%d %-32s %s → 4,20 €, zaloga 5\n", $id, $product->get_name(), $old);
}

$kits = get_option('zvij_kits', []);
foreach ($kits as $i => $kit) {
    if (($kit['key'] ?? '') !== 'throwie') {
        continue;
    }
    $kits[$i]['hidden'] = true;
    update_option('zvij_kits', $kits);
    echo "Throwie kit označen kot skrit (definicija ohranjena)\n";
}
