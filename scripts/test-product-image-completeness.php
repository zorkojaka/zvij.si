<?php
/** Read-only published catalog audit, including imported image provenance. */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
function photo_check(bool $ok, string $message): void {
    if (! $ok) { throw new RuntimeException($message); }
    echo "PASS $message\n";
}
foreach (wc_get_products(['status' => 'publish', 'limit' => -1]) as $product) {
    $ids = array_unique(array_filter(array_merge([$product->get_image_id()], $product->get_gallery_image_ids())));
    $hashes = [];
    foreach ($ids as $id) {
        $file = (string) get_attached_file($id);
        photo_check(is_file($file) && ! str_contains($file, 'placeholder'), "#{$product->get_id()} photo $id exists and is not a placeholder");
        $hashes[] = sha1_file($file);
    }
    photo_check(count(array_unique($hashes)) >= 2, "#{$product->get_id()} has at least two distinct image files");
    $before = $product->get_meta('_zvij_missing_media_v1_before');
    if (! $before) { continue; }
    foreach (['name', 'price', 'regular_price', 'sale_price', 'status', 'stock_status', 'manage_stock', 'stock_quantity'] as $field) {
        photo_check($product->{'get_' . $field}() === $before[$field], "#{$product->get_id()} $field preserved");
    }
    foreach ((array) $product->get_meta('_zvij_missing_media_v1') as $id) {
        photo_check((bool) get_post_meta($id, '_zvij_image_source_url', true), "Imported photo $id records source");
        $size = wp_get_attachment_metadata($id);
        photo_check($size['width'] === $size['height'], "Imported photo $id square");
    }
}
photo_check(str_contains(wc_get_product(445)->get_name(), '16 ml'), 'Clipper remains confirmed 16 ml, not 100 ml');
