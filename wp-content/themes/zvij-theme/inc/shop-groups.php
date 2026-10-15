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

/** Rank categories in SQL before pagination; leave explicit customer sorting alone. */
add_filter('posts_clauses', function (array $clauses, $query): array {
    if (! $query->is_main_query() || ! is_shop()
        || ! in_array($query->get('orderby'), ['menu_order title', 'menu_order'], true)) {
        return $clauses;
    }
    global $wpdb;
    $rank = 'CASE';
    foreach ([['cbd-cbg-vrsicki'], ['dubi-filtri'], ['rizle', 'rolce']] as $priority => $slugs) {
        $placeholders = implode(', ', array_fill(0, count($slugs), '%s'));
        $rank .= $wpdb->prepare(
            " WHEN EXISTS (SELECT 1 FROM {$wpdb->term_relationships} AS zv_rel
                INNER JOIN {$wpdb->term_taxonomy} AS zv_tax ON zv_tax.term_taxonomy_id = zv_rel.term_taxonomy_id
                INNER JOIN {$wpdb->terms} AS zv_term ON zv_term.term_id = zv_tax.term_id
                WHERE zv_rel.object_id = {$wpdb->posts}.ID AND zv_tax.taxonomy = 'product_cat'
                AND zv_term.slug IN ($placeholders)) THEN %d",
            ...array_merge($slugs, [$priority])
        );
    }
    $rank .= ' ELSE 3 END ASC';
    $secondary = trim((string) ($clauses['orderby'] ?? ''));
    $clauses['orderby'] = $rank . ($secondary !== '' ? ', ' . $secondary : '') . ", {$wpdb->posts}.ID ASC";
    return $clauses;
}, 20, 2);
