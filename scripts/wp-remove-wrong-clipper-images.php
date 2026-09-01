<?php
/**
 * Odstrani napačne fotografije s Clipper Black (Jaka, 1. 9. 2026).
 *
 * Na slikah je bil kovinski Clipper v darilni pločevinki (Knistermann
 * »METAL MATT ALL BLACK«, spletna šifra 11208), Jaka pa ima soft touch
 * različico s proforme — CLIP-FZ-239, 48/display, 0,84 €/kos. Izdelek bi
 * torej kazal dražji model, kot bi ga kupec dobil.
 *
 * Ob tem popravi še SKU na dejansko kupljeni artikel.
 */

if (! defined('ABSPATH')) {
    exit;
}

$product = wc_get_product(211);
if (! $product) {
    echo "izdelka #211 ni\n";
    return;
}

$attachments = array_filter(array_merge([ $product->get_image_id() ], $product->get_gallery_image_ids()));

$product->set_image_id('');
$product->set_gallery_image_ids([]);
if ($product->get_sku() !== 'CLIP-FZ-239') {
    printf("SKU: %s → CLIP-FZ-239\n", $product->get_sku());
    $product->set_sku('CLIP-FZ-239');
}
$product->save();

foreach ($attachments as $id) {
    $file = basename((string) get_attached_file($id));
    wp_delete_attachment((int) $id, true);
    printf("izbrisana priponka #%d %s\n", $id, $file);
}

echo "Clipper Black je zdaj brez slike — poženi se scripts/wp-make-placeholders-dev.php\n";
