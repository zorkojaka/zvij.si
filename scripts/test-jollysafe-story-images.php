<?php
/** Read-only checks: existing variation, preserved commerce, corrected gallery and story. */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
function image_check(bool $ok, string $message): void {
    if (! $ok) { throw new RuntimeException($message); }
    echo "PASS $message\n";
}
$parent = wc_get_product(243);
$before = $parent->get_meta('_zvij_jollysafe_gold_before_v2');
$media = $parent->get_meta('_zvij_jollysafe_gold_media_v2');
image_check($parent->is_type('variable') && count($parent->get_children()) === 3, 'One existing JollySafe with three variations');
image_check($parent->get_variation_attributes() === $before['attributes'] && $parent->get_default_attributes() === $before['defaults'], 'Color options and default unchanged');
image_check((int) $parent->get_image_id() === (int) $before['image'], 'Black parent photograph unchanged');
image_check(! array_intersect([585, 393, 394], $parent->get_gallery_image_ids()), 'Incorrect gold and mixed-color photographs removed from visible gallery');
foreach ($before['variations'] as $id => $fields) {
    $v = wc_get_product($id);
    foreach ($fields as $field => $value) {
        if ($field === 'cost') { image_check($v->get_meta('_zvij_cost') === $value, "Variation $id cost preserved"); continue; }
        if ($field === 'image_id' && (int) $v->get_meta('_zvij_merged_from') === 245) {
            image_check((int) $v->get_image_id('edit') === (int) $media['main'], 'Gold variation uses corrected tapered photograph');
        } else { image_check($v->{'get_' . $field}('edit') === $value, "Variation $id $field preserved"); }
    }
}
foreach ($media as $id) { image_check(is_file((string) get_attached_file($id)) && in_array((int) $id, $parent->get_gallery_image_ids(), true), "Corrected photo $id exists and is in merged gallery"); }
image_check(str_contains(zvij_kit_flatlay_url('gold'), 'gold-kit-real-v2.jpg'), 'Gold kit uses corrected tube photograph');
image_check(str_contains(zvij_kit_flatlay_url('silver'), 'silver-kit-real-v1.jpg'), 'Silver kit photograph unchanged');
$post = get_post(86);
$story_before = get_post_meta(86, '_zvij_story_before_products_v2', true);
image_check(sha1($post->post_content) === $story_before['content_hash'] && $post->post_title === $story_before['title'] && $post->post_status === $story_before['status'], 'Story text, title and publication status unchanged');
image_check((int) get_post_thumbnail_id(86) === (int) get_post_meta(86, '_zvij_story_products_image_v2', true), 'Story uses new CBD/CBG and DUBI cover');
