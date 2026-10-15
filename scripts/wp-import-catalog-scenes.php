<?php
/** Import reviewed square catalog scenes on development, preserving originals. */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
if (wp_get_environment_type() !== 'development') {
    throw new RuntimeException('Development catalog only');
}
require_once ABSPATH . 'wp-admin/includes/image.php';
$sources = [435 => 496, 436 => 530, 437 => 504, 438 => 517, 441 => 508, 442 => 516, 443 => 500, 444 => 512,
    218 => 385, 211 => 486, 213 => 340, 243 => 388, 244 => 392, 245 => 393,
    72 => 377, 74 => 375, 76 => 380, 71 => 382, 69 => 383];
$directory = get_template_directory() . '/assets/images/catalog-v2/';

// Validate the whole set before modifying any product or importing media.
foreach ($sources as $id => $original) {
    $product = wc_get_product($id);
    $imported = $product ? (int) $product->get_meta('_zvij_catalog_scene_v2') : 0;
    if (! $product || ! in_array((int) $product->get_image_id(), [$original, $imported], true)) {
        throw new RuntimeException("Product #$id changed since the source review");
    }
    $paths = [$directory . $id . '.jpg'];
    if (in_array($id, [72, 74, 76], true)) { $paths[] = $directory . $id . '-5g.jpg'; }
    foreach ($paths as $path) {
        $size = is_file($path) ? getimagesize($path) : false;
        if (! $size || $size[0] !== $size[1] || $size[0] < 1000) {
            throw new RuntimeException("Missing or invalid square scene: $path");
        }
    }
}

function zvij_import_catalog_scene(string $file, WC_Product $product): int {
    $upload = wp_upload_bits(basename($file), null, file_get_contents($file));
    if ($upload['error']) { throw new RuntimeException($upload['error']); }
    $id = wp_insert_attachment([
        'post_mime_type' => 'image/jpeg', 'post_title' => $product->get_name() . ' - produktna fotografija',
        'post_status' => 'inherit', 'post_parent' => $product->get_id(),
    ], $upload['file'], $product->get_id(), true);
    if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
    update_post_meta($id, '_wp_attachment_image_alt', $product->get_name());
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
    return (int) $id;
}

foreach ($sources as $id => $original) {
    $product = wc_get_product($id);
    $new_id = (int) $product->get_meta('_zvij_catalog_scene_v2');
    if (! $new_id || ! wp_get_attachment_url($new_id)) {
        $new_id = zvij_import_catalog_scene($directory . $id . '.jpg', $product);
    }
    if (! $product->get_meta('_zvij_before_catalog_scene_v2')) {
        $product->update_meta_data('_zvij_before_catalog_scene_v2', $original);
    }
    // A placeholder original is replaced outright, not kept in the gallery.
    $keep = str_contains((string) get_attached_file($original), 'zvij-placeholderji') ? [] : [$original];
    $gallery = array_unique(array_merge($keep, array_diff($product->get_gallery_image_ids(), $keep ? [] : [$original])));
    $product->set_gallery_image_ids(array_values(array_diff($gallery, [$new_id])));
    $product->set_image_id($new_id);
    $product->update_meta_data('_zvij_catalog_scene_v2', $new_id);
    if (in_array($id, [72, 74, 76], true)) {
        $pack_id = (int) $product->get_meta('_zvij_pack_5_image_id');
        if (! $pack_id || ! wp_get_attachment_url($pack_id)) {
            $pack_id = zvij_import_catalog_scene($directory . $id . '-5g.jpg', $product);
        }
        $product->update_meta_data('_zvij_pack_5_image_id', $pack_id);
    }
    $product->save();
    wc_delete_product_transients($id);
    printf("#%d %s: scene %d; original %d retained\n", $id, $product->get_name(), $new_id, $original);
}
