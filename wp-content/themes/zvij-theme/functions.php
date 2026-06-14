<?php
/**
 * Zvij Theme functions.
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');
    add_post_type_support('page', 'excerpt');

    register_nav_menus([
        'primary' => __('Primary menu', 'zvij-theme'),
    ]);
});

add_action('wp_enqueue_scripts', function (): void {
    wp_enqueue_style('zvij-theme-style', get_stylesheet_uri(), [], '0.3.0');
});

add_action('woocommerce_before_shop_loop_item_title', function (): void {
    global $product;

    if (! $product instanceof WC_Product) {
        return;
    }

    $terms = get_the_terms($product->get_id(), 'product_cat');
    if (empty($terms) || is_wp_error($terms)) {
        return;
    }

    echo '<span class="product-card__cat">' . esc_html($terms[0]->name) . '</span>';
}, 9);
