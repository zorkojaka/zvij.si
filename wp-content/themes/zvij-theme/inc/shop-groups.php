<?php
/** Shop navigation groups without changing product taxonomy or kit assignments. */
if (! defined('ABSPATH')) {
    exit;
}

function zvij_shop_groups(): array {
    return [
        'ziggi' => ['label' => 'Ziggi rizle', 'categories' => ['rizle', 'rolce']],
        'cbd' => ['label' => 'CBD', 'categories' => ['cbd-cbg-vrsicki']],
        'pripomocki' => ['label' => 'Pripomočki', 'categories' => ['setup-dodatki', 'grinderji', 'vzigalniki', 'dubi-filtri']],
    ];
}

add_filter('query_vars', function (array $vars): array {
    $vars[] = 'zvij_group';
    return $vars;
});

function zvij_current_shop_group(): string {
    $key = get_query_var('zvij_group', '');
    return is_string($key) && isset(zvij_shop_groups()[$key]) ? $key : '';
}

add_action('woocommerce_product_query', function ($query): void {
    if (! $query->is_main_query() || ! is_shop()) {
        return;
    }
    $key = zvij_current_shop_group();
    if ($key === '') {
        return;
    }
    $existing = $query->get('tax_query');
    $tax_query = ['relation' => 'AND'];
    if (is_array($existing) && $existing !== []) {
        $tax_query[] = $existing;
    }
    $tax_query[] = [
        'taxonomy' => 'product_cat',
        'field' => 'slug',
        'terms' => zvij_shop_groups()[$key]['categories'],
        'operator' => 'IN',
    ];
    $query->set('tax_query', $tax_query);
});
