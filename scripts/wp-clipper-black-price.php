<?php
/** Run via wp eval-file on development; update only Clipper Black's selling price. */
defined('ABSPATH') || exit;
if (wp_get_environment_type() !== 'development') {
    throw new RuntimeException('Development catalog only');
}
$post = get_page_by_path('clipper-black', OBJECT, 'product');
$product = $post ? wc_get_product($post->ID) : false;
if (! $product || $product->get_name() !== 'Clipper Black') {
    throw new RuntimeException('Clipper Black not found');
}
$product->set_regular_price('2.90');
$product->set_sale_price('');
$product->set_date_on_sale_from(null);
$product->set_date_on_sale_to(null);
$product->save();
wc_delete_product_transients($product->get_id());
printf("Clipper Black #%d: %s EUR\n", $product->get_id(), $product->get_price());
