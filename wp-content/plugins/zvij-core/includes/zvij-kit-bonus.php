<?php
/** Five percent extra crystals for at least three distinct kit products. */
defined('ABSPATH') || exit;

function zvij_kit_bonus_groups(): array {
    static $groups;
    if (isset($groups)) {
        return $groups;
    }
    $groups = [];
    foreach ((array) get_option('zvij_kits', []) as $kit) {
        if (! empty($kit['hidden'])) {
            continue;
        }
        $slugs = (array) ($kit['addons'] ?? []);
        $cats = [];
        foreach ((array) ($kit['items'] ?? []) as $item) {
            $slugs[] = $item['slug'] ?? '';
            $slugs = array_merge($slugs, (array) ($item['alt']['slug'] ?? []));
            $cats = array_merge($cats, (array) ($item['alt']['cat'] ?? []));
        }
        $ids = [];
        foreach (array_unique(array_filter($slugs)) as $slug) {
            $post = get_page_by_path($slug, OBJECT, 'product');
            if ($post && $post->post_status === 'publish') {
                $ids[] = (int) $post->ID;
            }
        }
        if ($cats !== []) {
            $ids = array_merge($ids, get_posts([
                'post_type' => 'product', 'post_status' => 'publish',
                'posts_per_page' => -1, 'fields' => 'ids',
                'tax_query' => [['taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array_unique($cats)]],
            ]));
        }
        $groups[] = array_values(array_unique(array_map('intval', $ids)));
    }
    return $groups;
}

/** Pure calculation, so quantities cannot stand in for distinct products. */
function zvij_kit_bonus_calculate(array $lines, array $groups): int {
    $present = [];
    foreach ($lines as $line) {
        if ($line['quantity'] > 0) {
            $present[(int) $line['id']] = true;
        }
    }
    $eligible = [];
    foreach ($groups as $ids) {
        if (count(array_intersect(array_keys($present), $ids)) >= 3) {
            foreach ($ids as $id) {
                $eligible[(int) $id] = true;
            }
        }
    }
    $base = 0;
    foreach ($lines as $line) {
        if (isset($eligible[(int) $line['id']])) {
            $base += max(0, (int) $line['reward']) * max(0, (int) $line['quantity']);
        }
    }
    return intdiv($base * 5, 100);
}

function zvij_kit_bonus_line(WC_Product $product, int $quantity): array {
    return [
        'id' => $product->get_parent_id() ?: $product->get_id(),
        'reward' => zvij_credit_product_kristali($product),
        'quantity' => $quantity,
    ];
}

function zvij_kit_bonus_cart(): int {
    if (! WC()->cart) {
        return 0;
    }
    $lines = [];
    foreach (WC()->cart->get_cart() as $row) {
        if ($row['data'] instanceof WC_Product) {
            $lines[] = zvij_kit_bonus_line($row['data'], (int) $row['quantity']);
        }
    }
    return zvij_kit_bonus_calculate($lines, zvij_kit_bonus_groups());
}

function zvij_kit_bonus_order(WC_Order $order): int {
    // Existing orders retain their original reward terms.
    if ($order->get_meta('_zvij_kit_bonus_version') !== '1') {
        return 0;
    }
    $lines = [];
    foreach ($order->get_items() as $item) {
        if ($item instanceof WC_Order_Item_Product && $item->get_product() instanceof WC_Product) {
            $lines[] = zvij_kit_bonus_line($item->get_product(), (int) $item->get_quantity());
        }
    }
    return zvij_kit_bonus_calculate($lines, zvij_kit_bonus_groups());
}

add_action('woocommerce_checkout_create_order', function ($order): void {
    $order->update_meta_data('_zvij_kit_bonus_version', '1');
    $order->update_meta_data('_zvij_kit_bonus_kristali', zvij_kit_bonus_order($order));
});

function zvij_kit_bonus_cart_row(): void {
    $bonus = zvij_kit_bonus_cart();
    if ($bonus > 0) {
        echo '<tr class="zv-kit-crystals"><th>' . esc_html__('Dodatni kristali za kit', 'zvij-core') . '</th><td data-title="' . esc_attr__('Dodatni kristali za kit', 'zvij-core') . '"><strong>+ ' . esc_html(zvij_kristali_izpis($bonus)) . '</strong><br><small>' . esc_html__('Za člane: 5 % več pri vsaj 3 različnih izdelkih iz kita.', 'zvij-core') . '</small></td></tr>';
    }
}
add_action('woocommerce_cart_totals_after_order_total', 'zvij_kit_bonus_cart_row');
add_action('woocommerce_review_order_after_order_total', 'zvij_kit_bonus_cart_row');

add_action('woocommerce_admin_order_totals_after_total', function ($order_id): void {
    $order = wc_get_order($order_id);
    $bonus = $order ? (int) $order->get_meta('_zvij_kit_bonus_kristali') : 0;
    if ($bonus > 0) {
        echo '<tr><td class="label">' . esc_html__('Bonus kristali za kit (vključeni v pripis):', 'zvij-core') . '</td><td width="1%"></td><td class="total">' . esc_html(zvij_kristali_izpis($bonus)) . '</td></tr>';
    }
});
