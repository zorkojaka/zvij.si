<?php
/**
 * Velikosti škatel za Ziggi izdelke — po pakiranju z računa ZIGGI.
 * Popust za celo škatlo je enoten (opcija zvij_box_discount).
 */

if (! defined('ABSPATH')) {
    exit;
}

update_option('zvij_box_discount', 15);

$boxes = [
    435 => 26, // AP6945 Original Classic Slim
    436 => 26, // AP7186 Hemp Classic Slim
    437 => 26, // AP7176 Natural Classic Slim
    438 => 26, // AP7731 Mystery Mix
    439 => 26, // AP7423 Mycelium Mystique
    440 => 26, // AP7413 Rocket's Odyssey
    441 => 22, // AP7115 Wide Extra (pkt 22 kos)
    442 => 16, // AP7751 Original Roll + Tips + Tray
    443 => 16, // AP7753 Natural Roll + Tips + Tray
    444 => 14, // AP7105 Original Double
];

printf("popust za celo škatlo: %s %%\n\n", number_format_i18n(zvij_box_discount()));
printf("%-42s %6s %6s %8s %9s %8s\n", 'Izdelek', 'kos/šk', 'cena', 'šk/kos', 'škatla', 'prihr.');

foreach ($boxes as $id => $qty) {
    $product = wc_get_product($id);
    if (! $product) {
        echo "preskocim #{$id}\n";
        continue;
    }
    $product->update_meta_data('_zvij_box_qty', (string) $qty);
    $product->save();

    $regular = (float) $product->get_regular_price();
    $unit    = zvij_box_unit_price($product);
    printf(
        "%-42s %6d %6.2f %8.2f %9.2f %8.2f\n",
        mb_substr($product->get_name(), 0, 41), $qty, $regular, $unit, $unit * $qty, ($regular - $unit) * $qty
    );
}
