<?php
/**
 * Količinska lestvica in velikosti škatel za Ziggi izdelke.
 *
 * Tri stopnje (Jaka, 1. 9. 2026): 3 kosi −10 %, 10 kosov −15 %,
 * cela škatla −20 %. Velikosti škatel so po pakiranju z računa ZIGGI.
 */

if (! defined('ABSPATH')) {
    exit;
}

update_option('zvij_qty_tiers', [3 => 10, 10 => 15]);
update_option('zvij_box_discount', 20);

$boxes = [
    435 => 26, 436 => 26, 437 => 26, 438 => 26, 439 => 26,
    440 => 26, 441 => 22, 442 => 16, 443 => 16, 444 => 14,
];

foreach ($boxes as $id => $qty) {
    $product = wc_get_product($id);
    if (! $product) {
        continue;
    }
    $product->update_meta_data('_zvij_box_qty', (string) $qty);
    $product->save();
}

/** Nabavne cene na kos (bruto) z računa ZIGGI — samo za preverbo marž. */
$cost = [435=>0.955, 436=>0.911, 437=>1.010, 438=>1.010, 439=>1.010,
         440=>1.010, 441=>1.098, 442=>1.153, 443=>1.197, 444=>1.900];

$worst = 100.0;
printf("%-40s %8s %8s %8s %8s\n", 'Izdelek', '1 kos', '3 kosi', '10 kos', 'škatla');
foreach ($boxes as $id => $box) {
    $p = wc_get_product($id);
    $r = (float) $p->get_regular_price();
    $row = [];
    foreach ([1, 3, 10, $box] as $q) {
        $u = zvij_qty_unit_price($p, $q);
        $row[] = sprintf('%.2f', $u);
        if (isset($cost[$id])) {
            $worst = min($worst, ($u - $cost[$id]) / $u * 100);
        }
    }
    printf("%-40s %8s %8s %8s %8s   (škatla %d)\n", mb_substr($p->get_name(), 0, 39), ...array_merge($row, [$box]));
}
printf("\nnajnižja marža v celotni lestvici: %.1f %%\n", $worst);
