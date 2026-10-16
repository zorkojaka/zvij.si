<?php
/** Jaka's approved grinder card selling price: EUR 7.50. Development only. */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
if (wp_get_environment_type() !== 'development') { throw new RuntimeException('Development catalog only'); }
$post = get_page_by_path('grinder-kartica-xxx', OBJECT, 'product');
$product = $post ? wc_get_product($post->ID) : false;
if (! $product || count((array) $product->get_meta('_zvij_grinder_media_v1')) !== 3) {
    throw new RuntimeException('Reviewed card product with three photographs not found');
}
if ($product->get_manage_stock()) { throw new RuntimeException('Quantity tracking now enabled; preserve real stock settings'); }
$product->set_regular_price('7.50');
$product->set_sale_price('');
$product->set_date_on_sale_from(null);
$product->set_date_on_sale_to(null);
$product->set_stock_status('instock');
$product->set_status('publish');
$product->save();
wc_delete_product_transients($product->get_id());
printf("#%d %s: %s EUR, %s, %s\n%s\n", $product->get_id(), $product->get_name(), $product->get_price(), $product->get_status(), $product->get_stock_status(), get_permalink($product->get_id()));
