<?php
/** Development-only, repeatable photo import and color-matched kit defaults. */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
if (wp_get_environment_type() !== 'development') { throw new RuntimeException('Development catalog only'); }
require_once ABSPATH . 'wp-admin/includes/image.php';
$directory = get_template_directory() . '/assets/images/grinders-real-v1/';
$definitions = [
    'silver' => ['slug' => 'srebrn-grinder-40-mm', 'source' => 532],
    'gold' => ['slug' => 'zlat-grinder-52-mm', 'source' => 531],
    'card' => ['slug' => 'grinder-kartica-xxx', 'source' => 0],
];
$defaults = [
    'black' => ['tube' => 'black-metal-joint-tube', 'lighter' => 'clipper-black', 'grinder' => 'zvij-mini-grinder-5-cm', 'paper' => 'ziggi-wide-extra', 'dubi' => 'dubi-42-aktivnih-ogljikovih-filtrov'],
    'silver' => ['tube' => 'silver-metal-joint-tube', 'lighter' => 'clipper-black', 'grinder' => 'srebrn-grinder-40-mm', 'paper' => 'ziggi-original-classic-slim', 'dubi' => 'dubi-42-aktivnih-ogljikovih-filtrov'],
    'gold' => ['tube' => 'gold-metal-joint-tube', 'lighter' => 'clipper-gold', 'grinder' => 'zlat-grinder-52-mm', 'paper' => 'ziggi-natural-classic-slim', 'dubi' => 'dubi-42-aktivnih-ogljikovih-filtrov'],
];
$notes = ['black' => 'Črn Clipper, grinder in tulec.', 'silver' => 'Srebrn grinder in tulec, črn Clipper.', 'gold' => 'Zlat Clipper, grinder in tulec.'];

foreach ($definitions as $key => $definition) {
    for ($i = 1; $i <= 3; $i++) {
        $file = $directory . "$key-$i.jpg";
        $size = is_file($file) ? getimagesize($file) : false;
        if (! $size || $size[0] !== $size[1] || $size[0] < 1000) { throw new RuntimeException("Invalid photo $file"); }
    }
    if ($key !== 'card') {
        $post = get_page_by_path($definition['slug'], OBJECT, 'product');
        $product = $post ? wc_get_product($post->ID) : false;
        $imported = $product ? (array) $product->get_meta('_zvij_grinder_media_v1') : [];
        if (! $product || ! in_array((int) $product->get_image_id(), array_merge([$definition['source']], array_map('intval', $imported)), true)) {
            throw new RuntimeException("$key grinder changed since photo review");
        }
    }
}
$kits = (array) get_option('zvij_kits', []);
$kit_keys = array_column($kits, 'key');
foreach ($defaults as $key => $roles) {
    if (! in_array($key, $kit_keys, true) || ! is_file(get_template_directory() . "/assets/images/kits/$key-kit-real-v1.jpg")) {
        throw new RuntimeException("Missing kit or photo $key");
    }
    foreach ($roles as $slug) {
        $post = get_page_by_path($slug, OBJECT, 'product');
        $product = $post ? wc_get_product($post->ID) : false;
        if (! $product || $product->get_status() !== 'publish' || ! $product->is_purchasable() || ! $product->is_in_stock()) {
            throw new RuntimeException("Kit component is not available: $slug");
        }
    }
}

function zvij_import_grinder_photo(string $file, WC_Product $product): int {
    $upload = wp_upload_bits('grinder-real-v1-' . basename($file), null, file_get_contents($file));
    if ($upload['error']) { throw new RuntimeException($upload['error']); }
    $id = wp_insert_attachment(['post_mime_type' => 'image/jpeg', 'post_title' => $product->get_name() . ' - ' . basename($file, '.jpg'), 'post_status' => 'inherit'], $upload['file'], $product->get_id(), true);
    if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
    update_post_meta($id, '_wp_attachment_image_alt', $product->get_name());
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
    return (int) $id;
}

foreach ($definitions as $key => $definition) {
    $post = get_page_by_path($definition['slug'], OBJECT, 'product');
    if (! $post && $key === 'card') {
        $matches = get_posts(['post_type' => 'product', 'post_status' => ['publish', 'draft', 'private', 'pending'], 's' => 'grinder kartica', 'posts_per_page' => 2]);
        if (count($matches) > 1) { throw new RuntimeException('Multiple grinder cards already exist'); }
        $post = $matches[0] ?? null;
    }
    $product = $post ? wc_get_product($post->ID) : new WC_Product_Simple();
    if (! $post) {
        $product->set_name('Kovinska grinder kartica XXX');
        $product->set_slug($definition['slug']);
        $product->set_status('draft');
        $product->set_stock_status('outofstock');
        $product->set_short_description('Tanka kovinska grinder kartica s tremi polji za drobljenje in zaščitnim ovitkom.');
        $product->set_description('Kovinska kartica s tremi izsekanimi polji v obliki križcev. Priložen je zaščitni ovitek z vzorcem lesa. Ravna oblika zavzame malo prostora.');
        $term = get_term_by('slug', 'grinderji', 'product_cat');
        if ($term) { $product->set_category_ids([(int) $term->term_id]); }
        $product->save();
    }
    $ids = array_map('intval', (array) $product->get_meta('_zvij_grinder_media_v1'));
    if (count($ids) !== 3 || count(array_filter($ids, 'wp_get_attachment_url')) !== 3) {
        $ids = [];
        for ($i = 1; $i <= 3; $i++) { $ids[] = zvij_import_grinder_photo($directory . "$key-$i.jpg", $product); }
    }
    if (! $product->meta_exists('_zvij_before_grinder_media_v1')) {
        $product->update_meta_data('_zvij_before_grinder_media_v1', (int) $product->get_image_id());
    }
    $old_gallery = array_filter($product->get_gallery_image_ids(), static fn ($id) => ! str_contains((string) wp_get_attachment_url($id), 'placeholder'));
    $product->set_image_id($ids[0]);
    $product->set_gallery_image_ids(array_values(array_diff(array_unique(array_merge(array_slice($ids, 1), $old_gallery)), [$ids[0]])));
    $product->update_meta_data('_zvij_grinder_media_v1', $ids);
    $product->save();
    wc_delete_product_transients($product->get_id());
    printf("%s #%d: 3 photos; status %s; price %s\n", $product->get_name(), $product->get_id(), $product->get_status(), $product->get_price());
}

if (! get_option('zvij_kits_before_real_photos_v1')) { update_option('zvij_kits_before_real_photos_v1', $kits, false); }
foreach ($kits as &$kit) {
    $key = $kit['key'] ?? '';
    if (! isset($defaults[$key])) { continue; }
    foreach ($kit['items'] as &$item) {
        if (isset($defaults[$key][$item['role'] ?? ''])) { $item['slug'] = $defaults[$key][$item['role']]; }
    }
    unset($item);
    $kit['style_note'] = $notes[$key];
    $kit['addons'] = ['frutty-cbd-vrsicki', 'smokey-cbd-vrsicki', 'chilly-cbg-vrsicki'];
}
unset($kit);
update_option('zvij_kits', $kits);
echo "Three kit defaults updated; hidden kits, prices and stock preserved.\n";
