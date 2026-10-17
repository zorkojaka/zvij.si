<?php
/**
 * Vršički po gramih (17. 10. 2026): izklopi variacije »5 g« (zasebne, ne
 * izbrisane), nastavi meje cen in posodobi opise. Idempotentno.
 * Zagon: docker exec -u 33 <wordpress> php /var/www/html/scripts/wp-grams-switch.php
 */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'zvij.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';

if (get_option('zvij_gram_tiers') === false) {
    update_option('zvij_gram_tiers', [5 => 10, 25 => 20]);
}
echo 'Meje: ' . wp_json_encode(zvij_gram_tiers()) . "\n";

foreach (wc_get_products(['category' => [ZVIJ_GRAM_CATEGORY], 'type' => 'variable', 'limit' => -1, 'status' => 'publish']) as $product) {
    foreach ($product->get_children() as $child_id) {
        $v = wc_get_product($child_id);
        foreach ($v->get_attributes() as $value) {
            if (preg_match('/^5\s*g$/i', trim((string) $value)) && $v->get_status() !== 'private') {
                $v->set_status('private');
                $v->save();
                echo "  #{$child_id} 5 g → izklopljena\n";
            }
        }
    }
    WC_Product_Variable::sync($product->get_id());
    $excerpt = str_replace(', v pakiranju po 1 g ali 5 g.', ' — izberi, koliko gramov želiš. Od 5 g in od 25 g je gram cenejši.', $product->get_short_description());
    $content = str_replace('<p>Paket 5 g vsebuje pet posameznih pakiranj po 1 g.</p>', '<p>Pakirano po 1 g. Izbereš, koliko gramov želiš: od 5 g je gram 10 % cenejši, od 25 g pa 20 %.</p>', $product->get_description());
    if ($excerpt !== $product->get_short_description() || $content !== $product->get_description()) {
        $product->set_short_description($excerpt);
        $product->set_description($content);
        $product->save();
        echo "  #{$product->get_id()} opis posodobljen\n";
    }
    wc_delete_product_transients($product->get_id());
    echo $product->get_name() . " OK\n";
}
