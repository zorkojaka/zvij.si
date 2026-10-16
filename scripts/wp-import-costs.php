<?php
/**
 * Nabavne cene (bruto na kos) iz Zvij_cenik_in_marze.xlsx, list »Cenik«, stolpec
 * »NABAVNA / enoto (bruto)« = redna neto cena pakiranja × (1 − popust) / kosov × (1 + DDV dobavitelja).
 * Stanje tabele 16. 10. 2026. Ne prepiše cene, ki je že vpisana na izdelku (vir resnice je potem WP).
 *
 * Vršički, JollySafe in Grinder Zvij.si črn: Jakove cene 16. 10. 2026 (ni v tabeli).
 * Vršički po variacijah: paket 5 g je pet paketkov po 1 g, zato 5 × cena 1 g.
 *
 * Zagon (dev ali prod): docker exec -u 33 <wordpress> php /var/www/html/scripts/wp-import-costs.php
 */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'zvij.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';

$costs = [
    69  => 44.68, // DUBI 420
    71  => 4.70,  // DUBI 42
    213 => 5.76,  // Clipper Gold
    211 => 0.84,  // Clipper Black
    445 => 0.62,  // Clipper plin 16 ml
    216 => 22.55, // zlat grinder 4-delni
    217 => 3.51,  // mali srebrn grinder 2-delni
    618 => 2.25,  // grinder kartica (V-Syndicate)
    435 => 0.96,  // Ziggi Original Classic Slim
    436 => 0.91,  // Ziggi Hemp
    437 => 1.01,  // Ziggi Natural
    438 => 1.01,  // Ziggi Mystery Mix
    439 => 1.01,  // Ziggi Mycelium Mystique
    440 => 1.01,  // Ziggi Rocket's Odyssey
    441 => 1.10,  // Ziggi Wide Extra
    442 => 1.15,  // Ziggi Original Roll
    443 => 1.20,  // Ziggi Natural Roll
    444 => 1.90,  // Ziggi Original Double
    310 => 1.25,  // CHILLY CBG 1 g
    311 => 6.25,  // CHILLY CBG 5 g
    308 => 1.50,  // SMOKEY CBD 1 g
    309 => 7.50,  // SMOKEY CBD 5 g
    312 => 1.00,  // FRUTTY CBD 1 g
    313 => 5.00,  // FRUTTY CBD 5 g
    243 => 2.50,  // JollySafe Black
    244 => 2.50,  // JollySafe Silver
    245 => 2.50,  // JollySafe Gold
    218 => 6.00,  // Grinder Zvij.si črn
];

foreach ($costs as $id => $cost) {
    $product = wc_get_product($id);
    if (! $product) {
        echo "preskocim #{$id}: ni izdelka\n";
        continue;
    }
    if ($product->get_meta('_zvij_cost') !== '') {
        echo "pustim #{$id} {$product->get_name()}: že vpisano " . $product->get_meta('_zvij_cost') . "\n";
        continue;
    }
    $product->update_meta_data('_zvij_cost', wc_format_decimal($cost, 2));
    // Stara napačna vrednost (0) ne sme zavajati.
    $product->delete_meta_data('_zvij_supplier_purchase_price');
    $product->save();
    printf("#%d %s: nabava %.2f €, cena %s €\n", $id, $product->get_name(), $cost, $product->get_price());
}
