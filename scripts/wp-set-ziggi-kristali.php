<?php
/**
 * Kristali na Ziggi izdelkih (Jaka, 1. 9. 2026: »vsak kos da kristale«).
 *
 * Vrednost sledi ostalemu katalogu — približno 10 % cene nazaj, torej
 * kristali ≈ cena v evrih. Kristali se množijo s količino (glej
 * zvij_credit_order_earnable), zato 3 kosi dajo trikrat toliko.
 *
 * Zakaj ne 20 kristalov na Ziggi Double, kot je bil prvi predlog: 20
 * kristalov je 2,00 €, nabavna cena Double je 1,90 €, škatelna cena pa
 * 3,36 € — nakup cele škatle bi bil 0,54 € izgube na kos. Pri teh
 * vrednostih ostane tudi najslabši primer (škatelna cena IN kristali)
 * pri 30 % marže.
 */

if (! defined('ABSPATH')) {
    exit;
}

$kristali = [
    435 => 2, // Original Classic Slim   2,30 €
    436 => 2, // Hemp Classic Slim       2,30 €
    437 => 2, // Natural Classic Slim    2,30 €
    438 => 3, // Mystery Mix SE          2,50 €
    439 => 3, // Mycelium Mystique SE    2,50 €
    440 => 3, // Rocket's Odyssey SE     2,50 €
    441 => 3, // Wide Extra              2,50 €
    442 => 3, // Original Roll + Tips    2,90 €
    443 => 3, // Natural Roll + Tips     2,90 €
    444 => 4, // Original Double         4,20 €
];

foreach ($kristali as $id => $kr) {
    $product = wc_get_product($id);
    if (! $product) {
        continue;
    }
    $product->update_meta_data('_zvij_kristali', (string) $kr);
    $product->save();
    printf("%-42s %.2f € → %d kristali/kos  (pri 3: %d, pri celi škatli: %d)\n",
        mb_substr($product->get_name(), 0, 41), (float) $product->get_regular_price(),
        $kr, $kr * 3, $kr * zvij_box_qty($product));
}
