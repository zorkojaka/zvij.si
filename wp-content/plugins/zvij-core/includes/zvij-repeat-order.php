<?php
/**
 * Ponovi naročilo (reload ritual) — glej docs/REPEAT_ORDER_SPEC.md.
 *
 * Uporabi WooCommercov vgrajeni »order again« mehanizem (URL z nonce na
 * košarici, Woo sam pravilno obnovi postavke in variacije), le da:
 * - velja tudi za plačana/aktivna naročila (processing, pripravljeno za
 *   odpremo, completed), ne samo za completed;
 * - je gumb »Ponovi naročilo« viden v seznamu naročil v Moj račun;
 * - dashboard Moj račun pokaže zadnje naročilo z bližnjico za reload.
 *
 * Opomniki (reload reminder) so namenoma post-launch — rabijo intervale po
 * izdelkih in privolitev (glej spec).
 */

if (! defined('ABSPATH')) {
    exit;
}

/** Statusi, pri katerih je ponovitev smiselna. */
function zvij_repeat_order_statuses(): array {
    $statuses = ['completed', 'processing'];
    if (defined('ZVIJ_ORDER_STATUS_READY')) {
        $statuses[] = ZVIJ_ORDER_STATUS_READY;
    }
    return apply_filters('zvij_repeat_order_statuses', $statuses);
}

add_filter('woocommerce_valid_order_statuses_for_order_again', 'zvij_repeat_order_statuses');

function zvij_repeat_order_url(WC_Order $order): string {
    return wp_nonce_url(
        add_query_arg('order_again', $order->get_id(), wc_get_cart_url()),
        'woocommerce-order_again'
    );
}

/** Gumb v seznamu naročil (Moj račun → Naročila). */
add_filter('woocommerce_my_account_my_orders_actions', function (array $actions, WC_Order $order): array {
    if (in_array($order->get_status(), zvij_repeat_order_statuses(), true)) {
        $actions['zvij_repeat'] = [
            'url' => zvij_repeat_order_url($order),
            'name' => __('Ponovi naročilo', 'zvij-core'),
        ];
    }
    return $actions;
}, 10, 2);

/** Blok zadnjega naročila na dashboardu Moj račun (nad kristali). */
add_action('woocommerce_account_dashboard', function (): void {
    $user_id = get_current_user_id();
    if (! $user_id) {
        return;
    }

    $orders = wc_get_orders([
        'customer_id' => $user_id,
        'status' => zvij_repeat_order_statuses(),
        'limit' => 1,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    $order = $orders[0] ?? null;
    if (! $order instanceof WC_Order) {
        return;
    }

    $item_names = [];
    foreach ($order->get_items() as $item) {
        $item_names[] = $item->get_name();
    }
    ?>
    <section class="zvij-repeat-order">
      <h3><?php esc_html_e('Reload zadnjega naročila', 'zvij-core'); ?></h3>
      <p>
        <?php echo esc_html(sprintf(
            __('Naročilo #%1$s (%2$s) — %3$s', 'zvij-core'),
            $order->get_order_number(),
            wc_format_datetime($order->get_date_created(), 'j. n. Y'),
            implode(', ', array_slice($item_names, 0, 4)) . (count($item_names) > 4 ? ' …' : '')
        )); ?>
      </p>
      <p>
        <a class="button" href="<?php echo esc_url(zvij_repeat_order_url($order)); ?>"><?php esc_html_e('Ponovi naročilo', 'zvij-core'); ?></a>
      </p>
    </section>
    <?php
}, 8);
