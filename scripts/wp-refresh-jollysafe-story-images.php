<?php
/** Reviewed images for the existing gold variation and Zvij.si story; development only. */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
if (wp_get_environment_type() !== 'development') { throw new RuntimeException('Development only'); }
require_once ABSPATH . 'wp-admin/includes/image.php';
$directory = get_template_directory() . '/assets/images/jollysafe-story-v2/';
$parent = wc_get_product(243);
$story = get_post(86);
if (! $parent || ! $parent->is_type('variable') || $parent->get_slug() !== 'zvij-si-jollysafe'
    || ! $story || $story->post_type !== 'post' || $story->post_name !== 'zgodba-zvij-si') {
    throw new RuntimeException('Expected merged JollySafe and story not found');
}
$gold = null;
foreach ($parent->get_children() as $id) {
    $variation = wc_get_product($id);
    if ((int) $variation->get_meta('_zvij_merged_from') === 245) { $gold = $variation; }
}
$media = array_filter((array) $parent->get_meta('_zvij_jollysafe_gold_media_v2'));
if (! $gold || $gold->get_attribute('barva') !== 'Zlata'
    || ! in_array((int) $gold->get_image_id('edit'), [585, (int) ($media['main'] ?? 0)], true)) {
    throw new RuntimeException('Gold variation changed since source review');
}
$story_image = (int) get_post_meta(86, '_zvij_story_products_image_v2', true);
if (! in_array((int) get_post_thumbnail_id(86), [665, $story_image], true)) {
    throw new RuntimeException('Story featured image changed since source review');
}
foreach (['jollysafe-gold-main-v2.jpg', 'jollysafe-gold-gallery-v2.jpg', 'zgodba-zvij-si-products-v2.jpg'] as $file) {
    $size = is_file($directory . $file) ? getimagesize($directory . $file) : false;
    if (! $size || min($size[0], $size[1]) < 1000) { throw new RuntimeException("Invalid photograph $file"); }
}
if (! is_file(get_template_directory() . '/assets/images/kits/gold-kit-real-v2.jpg')) {
    throw new RuntimeException('Corrected gold kit photograph missing');
}

if (! $parent->meta_exists('_zvij_jollysafe_gold_before_v2')) {
    $variations = [];
    foreach ($parent->get_children() as $id) {
        $v = wc_get_product($id);
        $variations[$id] = [];
        foreach (['parent_id', 'attributes', 'sku', 'price', 'regular_price', 'sale_price', 'manage_stock', 'stock_quantity', 'stock_status', 'status', 'image_id'] as $field) {
            $variations[$id][$field] = $v->{'get_' . $field}('edit');
        }
        $variations[$id]['cost'] = $v->get_meta('_zvij_cost');
    }
    $parent->update_meta_data('_zvij_jollysafe_gold_before_v2', [
        'gallery' => $parent->get_gallery_image_ids(), 'image' => $parent->get_image_id(),
        'attributes' => $parent->get_variation_attributes(), 'defaults' => $parent->get_default_attributes(),
        'variations' => $variations,
    ]);
    $parent->save_meta_data();
}
if (! metadata_exists('post', 86, '_zvij_story_before_products_v2')) {
    update_post_meta(86, '_zvij_story_before_products_v2', [
        'thumbnail' => get_post_thumbnail_id(86), 'content_hash' => sha1($story->post_content),
        'title' => $story->post_title, 'status' => $story->post_status,
    ]);
}

function refresh_reviewed_photo(string $file, int $owner, string $alt): int {
    $upload = wp_upload_bits(basename($file), null, file_get_contents($file));
    if ($upload['error']) { throw new RuntimeException($upload['error']); }
    $id = wp_insert_attachment(['post_mime_type' => 'image/jpeg', 'post_title' => $alt, 'post_parent' => $owner, 'post_status' => 'inherit'], $upload['file'], $owner, true);
    if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
    update_post_meta($id, '_wp_attachment_image_alt', $alt);
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
    return (int) $id;
}
foreach (['main' => 'jollysafe-gold-main-v2.jpg', 'gallery' => 'jollysafe-gold-gallery-v2.jpg'] as $role => $file) {
    if (empty($media[$role]) || ! is_file((string) get_attached_file($media[$role]))) {
        $media[$role] = refresh_reviewed_photo($directory . $file, 243, 'Zvij.si JollySafe - zlata barva, zozen kovinski tulec');
        $parent->update_meta_data('_zvij_jollysafe_gold_media_v2', $media);
        $parent->save_meta_data();
    }
}
$gold->set_image_id($media['main']);
$gold->save();
// Remove only known incorrect gold/all-color photos; retain every black and silver view.
$gallery = array_values(array_unique(array_merge(array_diff($parent->get_gallery_image_ids(), [585, 393, 394]), array_values($media))));
$parent->set_gallery_image_ids($gallery);
$parent->save();
wc_delete_product_transients($gold->get_id());
wc_delete_product_transients(243);
if (! $story_image || ! is_file((string) get_attached_file($story_image))) {
    $story_image = refresh_reviewed_photo($directory . 'zgodba-zvij-si-products-v2.jpg', 86, 'Zgodba Zvij.si: Smokey, Frutty in Chilly ter DUBI filtri');
    update_post_meta(86, '_zvij_story_products_image_v2', $story_image);
}
set_post_thumbnail(86, $story_image);
printf("JollySafe #%d, existing gold variation #%d: photo %d, gallery photo %d\nStory #86: photo %d\n", 243, $gold->get_id(), $media['main'], $media['gallery'], $story_image);
