<?php
/** Read-only checks for imported grinder photos and real kit components. */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
function grinder_check(bool $ok, string $message): void {
    if (! $ok) { throw new RuntimeException($message); }
    echo "PASS $message\n";
}
foreach (['srebrn-grinder-40-mm', 'zlat-grinder-52-mm', 'grinder-kartica-xxx'] as $slug) {
    $post = get_page_by_path($slug, OBJECT, 'product');
    $product = $post ? wc_get_product($post->ID) : false;
    grinder_check((bool) $product, "$slug exists");
    $ids = array_map('intval', (array) $product->get_meta('_zvij_grinder_media_v1'));
    grinder_check(count($ids) === 3 && count(array_unique($ids)) === 3, "$slug has three distinct photographs");
    grinder_check((int) $product->get_image_id() === $ids[0], "$slug main photograph assigned");
    grinder_check(count(array_intersect(array_slice($ids, 1), $product->get_gallery_image_ids())) === 2, "$slug secondary photographs assigned");
    foreach ($ids as $id) {
        $meta = wp_get_attachment_metadata($id);
        grinder_check(isset($meta['width'], $meta['height']) && $meta['width'] === $meta['height'] && $meta['width'] >= 1000, "$slug photo $id square and full size");
    }
    if ($slug === 'grinder-kartica-xxx' && $product->get_price() === '') {
        grinder_check($product->get_status() === 'draft' && ! $product->is_purchasable(), 'Unpriced card stays draft and cannot be purchased');
    } elseif ($slug === 'grinder-kartica-xxx') {
        grinder_check((float) $product->get_price() === 7.50, 'Card uses approved EUR 7.50 price');
        grinder_check($product->get_status() === 'publish' && $product->is_purchasable() && $product->is_in_stock(), 'Priced card published and available');
    }
}
$cards = get_posts(['post_type' => 'product', 'post_status' => ['draft', 'publish', 'private', 'pending'], 'name' => 'grinder-kartica-xxx', 'fields' => 'ids']);
grinder_check(count($cards) === 1, 'One card product, no duplicate');
$expected = ['black' => [243, 211, 218, 441, 71], 'silver' => [244, 211, 217, 435, 71], 'gold' => [245, 213, 216, 437, 71]];
foreach ((array) get_option('zvij_kits', []) as $kit) {
    $key = $kit['key'] ?? '';
    if (! isset($expected[$key])) { continue; }
    $actual = [];
    foreach ($kit['items'] as $item) {
        $view = zvij_kit_item_view($item['slug']);
        $product = $view['id'] ? wc_get_product($view['id']) : false;
        grinder_check($product && $product->get_status() === 'publish' && $product->is_purchasable() && $product->is_in_stock(), "$key component {$item['role']} published and available");
        // Po združitvi v variacije (16. 10. 2026) je izdelek variacija; primerjaj z izvornim ID.
        $actual[] = (int) ($product->get_meta('_zvij_merged_from') ?: $product->get_id());
    }
    grinder_check($actual === $expected[$key], "$key defaults match photographed real products");
    $url = zvij_kit_flatlay_url($key);
    $version = $key === 'gold' ? 'v2' : 'v1';
    grinder_check(str_contains($url, "$key-kit-real-$version.jpg"), "$key uses reviewed real-product kit photograph");
    foreach ($kit['addons'] as $slug) {
        grinder_check(zvij_kit_item_view($slug)['id'] > 0, "$key optional product $slug resolves");
    }
}
grinder_check(wc_get_product(227)->get_status() === 'draft', 'Unavailable silver Clipper not silently published');
grinder_check((bool) get_option('zvij_kits_before_real_photos_v1'), 'Previous kit definition backed up');
