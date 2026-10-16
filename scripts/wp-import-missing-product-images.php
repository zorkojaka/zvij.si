<?php
/** Import reviewed online-source images on development; never change commercial data. */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
if (wp_get_environment_type() !== 'development') { throw new RuntimeException('Development catalog only'); }
require_once ABSPATH . 'wp-admin/includes/image.php';
$directory = get_template_directory() . '/assets/images/missing-products-v1/';
$manifest = json_decode(file_get_contents($directory . 'manifest.json'), true, 512, JSON_THROW_ON_ERROR);

foreach ($manifest as $id => $definition) {
    $product = wc_get_product($id);
    $imported = $product ? array_filter((array) $product->get_meta('_zvij_missing_media_v1')) : [];
    $allowed = array_merge([$definition['previous_featured']], array_values($imported));
    if (! $product || $product->get_slug() !== $definition['slug'] || ! in_array((int) $product->get_image_id(), $allowed, true)) {
        throw new RuntimeException("Product #$id changed since source review");
    }
    foreach ($definition['images'] as $image) {
        $file = $directory . $image['file'];
        $size = is_file($file) ? getimagesize($file) : false;
        if (! $size || $size[0] !== $size[1] || $size[0] < 600) { throw new RuntimeException("Invalid square photo $file"); }
    }
}

foreach ($manifest as $id => $definition) {
    $product = wc_get_product($id);
    if (! $product->meta_exists('_zvij_missing_media_v1_before')) {
        $product->update_meta_data('_zvij_missing_media_v1_before', [
            'featured' => $product->get_image_id(), 'gallery' => $product->get_gallery_image_ids(),
            'name' => $product->get_name(), 'price' => $product->get_price(),
            'regular_price' => $product->get_regular_price(), 'sale_price' => $product->get_sale_price(),
            'status' => $product->get_status(), 'stock_status' => $product->get_stock_status(),
            'manage_stock' => $product->get_manage_stock(), 'stock_quantity' => $product->get_stock_quantity(),
        ]);
    }
    $imported = array_filter((array) $product->get_meta('_zvij_missing_media_v1'));
    $gallery = $product->get_gallery_image_ids();
    foreach ($definition['images'] as $image) {
        $file = $directory . $image['file'];
        $attachment = (int) ($imported[$image['file']] ?? 0);
        if (! $attachment || ! is_file((string) get_attached_file($attachment))) {
            $upload = wp_upload_bits('catalog-missing-v1-' . $image['file'], null, file_get_contents($file));
            if ($upload['error']) { throw new RuntimeException($upload['error']); }
            $attachment = wp_insert_attachment([
                'post_mime_type' => wp_check_filetype($file)['type'],
                'post_title' => $product->get_name() . ' - ' . pathinfo($image['file'], PATHINFO_FILENAME),
                'post_status' => 'inherit', 'post_parent' => $id,
            ], $upload['file'], $id, true);
            if (is_wp_error($attachment)) { throw new RuntimeException($attachment->get_error_message()); }
            update_post_meta($attachment, '_wp_attachment_image_alt', $product->get_name());
            update_post_meta($attachment, '_zvij_image_source_url', $image['source']);
            update_post_meta($attachment, '_zvij_image_treatment', $image['treatment']);
            wp_update_attachment_metadata($attachment, wp_generate_attachment_metadata($attachment, $upload['file']));
            $imported[$image['file']] = (int) $attachment;
            // Save progress so an interrupted metadata import cannot duplicate completed media.
            $product->update_meta_data('_zvij_missing_media_v1', $imported);
            $product->save_meta_data();
        }
        if ($image['role'] === 'featured') { $product->set_image_id($attachment); }
        else { $gallery[] = $attachment; }
    }
    $gallery = array_filter($gallery, static function ($attachment) use ($product) {
        return (int) $attachment !== (int) $product->get_image_id()
            && ! str_contains((string) get_attached_file($attachment), 'placeholder');
    });
    $product->set_gallery_image_ids(array_values(array_unique(array_map('intval', $gallery))));
    $product->update_meta_data('_zvij_missing_media_v1', $imported);
    $product->save();
    wc_delete_product_transients($id);
    printf("#%d %s: featured %d; gallery %s\n", $id, $product->get_name(), $product->get_image_id(), implode(',', $product->get_gallery_image_ids()));
}
