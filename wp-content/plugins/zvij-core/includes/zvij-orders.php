<?php
/**
 * Order operations: "Pripravljeno za odpremo" status + printable packing/address document.
 */

if (! defined('ABSPATH')) {
    exit;
}

const ZVIJ_ORDER_STATUS_READY = 'zvij-ready';

add_action('init', function (): void {
    register_post_status('wc-' . ZVIJ_ORDER_STATUS_READY, [
        'label' => 'Pripravljeno za odpremo',
        'public' => false,
        'internal' => false,
        'exclude_from_search' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        // translators: %s: order count.
        'label_count' => _n_noop('Pripravljeno za odpremo <span class="count">(%s)</span>', 'Pripravljeno za odpremo <span class="count">(%s)</span>', 'zvij-core'),
    ]);
});

add_filter('wc_order_statuses', function (array $statuses): array {
    $out = [];
    foreach ($statuses as $key => $label) {
        $out[$key] = $label;
        if ($key === 'wc-processing') {
            $out['wc-' . ZVIJ_ORDER_STATUS_READY] = 'Pripravljeno za odpremo';
        }
    }
    if (! isset($out['wc-' . ZVIJ_ORDER_STATUS_READY])) {
        $out['wc-' . ZVIJ_ORDER_STATUS_READY] = 'Pripravljeno za odpremo';
    }
    return $out;
});

// Ready-to-ship orders count as paid in reports and stock/coupon logic.
add_filter('woocommerce_order_is_paid_statuses', function (array $statuses): array {
    $statuses[] = ZVIJ_ORDER_STATUS_READY;
    return $statuses;
});

add_filter('woocommerce_reports_order_statuses', function ($statuses) {
    if (is_array($statuses) && ! in_array(ZVIJ_ORDER_STATUS_READY, $statuses, true)) {
        $statuses[] = ZVIJ_ORDER_STATUS_READY;
    }
    return $statuses;
});

/** Statuses that count as revenue for the Zvij dashboard. */
function zvij_paid_statuses(): array {
    return ['processing', ZVIJ_ORDER_STATUS_READY, 'completed'];
}

/** Nonce-protected URL of the printable packing document for one order. */
function zvij_order_print_url(int $order_id): string {
    return wp_nonce_url(
        admin_url('admin-post.php?action=zvij_print_order&order_id=' . $order_id),
        'zvij_print_order_' . $order_id
    );
}

// Bulk action: mark selected orders ready to ship (works on HPOS and legacy list tables).
foreach (['bulk_actions-woocommerce_page_wc-orders', 'bulk_actions-edit-shop_order'] as $bulk_hook) {
    add_filter($bulk_hook, function (array $actions): array {
        $actions['zvij_mark_ready'] = 'Označi: pripravljeno za odpremo';
        return $actions;
    });
}

foreach (['handle_bulk_actions-woocommerce_page_wc-orders', 'handle_bulk_actions-edit-shop_order'] as $handle_hook) {
    add_filter($handle_hook, function (string $redirect, string $action, array $ids): string {
        if ($action !== 'zvij_mark_ready') {
            return $redirect;
        }
        foreach ($ids as $id) {
            $order = wc_get_order((int) $id);
            if ($order instanceof WC_Order) {
                $order->update_status(ZVIJ_ORDER_STATUS_READY, 'Zvij: pripravljeno za odpremo (bulk).');
            }
        }
        return add_query_arg('zvij_marked_ready', count($ids), $redirect);
    }, 10, 3);
}

// Row action button (printer) in the orders list.
add_filter('woocommerce_admin_order_actions', function (array $actions, WC_Order $order): array {
    $actions['zvij_print'] = [
        'url' => zvij_order_print_url($order->get_id()),
        'name' => 'Natisni odpremni dokument',
        'action' => 'zvij-print',
    ];
    return $actions;
}, 10, 2);

add_action('admin_head', function (): void {
    echo '<style>.wc-action-button-zvij-print::after{content:"\f193" !important;font-family:dashicons !important;}</style>';
});

// Meta box on the order edit screen: print + mark ready shortcuts.
add_action('add_meta_boxes', function (): void {
    $screen = class_exists(\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class)
        && wc_get_container()->get(\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
        ? wc_get_page_screen_id('shop-order')
        : 'shop_order';

    add_meta_box('zvij-order-ops', 'Zvij.si odprema', function ($post_or_order): void {
        $order = $post_or_order instanceof WC_Order ? $post_or_order : wc_get_order($post_or_order->ID);
        if (! $order instanceof WC_Order) {
            return;
        }
        echo '<p><a class="button button-primary" target="_blank" href="' . esc_url(zvij_order_print_url($order->get_id())) . '">Natisni odpremni dokument</a></p>';
        echo '<p>Status: <strong>' . esc_html(wc_get_order_status_name($order->get_status())) . '</strong></p>';
        echo '<p class="description">Odpremni dokument vsebuje naslovnico za paket in seznam za pakiranje.</p>';
    }, $screen, 'side', 'high');
});

// Printable packing / address document.
add_action('admin_post_zvij_print_order', function (): void {
    $order_id = (int) ($_GET['order_id'] ?? 0);
    if (! current_user_can('manage_woocommerce') || ! wp_verify_nonce((string) ($_GET['_wpnonce'] ?? ''), 'zvij_print_order_' . $order_id)) {
        wp_die('Not allowed.');
    }

    $order = wc_get_order($order_id);
    if (! $order instanceof WC_Order) {
        wp_die('Naročilo ne obstaja.');
    }

    $shipping = $order->get_address('shipping');
    if (empty($shipping['address_1'])) {
        $shipping = $order->get_address('billing');
    }
    $is_cod = $order->get_payment_method() === 'cod';

    header('Content-Type: text/html; charset=utf-8');
    ?>
<!doctype html>
<html lang="sl">
<head>
<meta charset="utf-8">
<title>Odprema #<?php echo esc_html($order->get_order_number()); ?></title>
<style>
  * { box-sizing: border-box; }
  body { font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif; color: #111; margin: 0; padding: 24px; }
  .doc { max-width: 720px; margin: 0 auto; }
  .toolbar { display: flex; gap: 8px; justify-content: flex-end; margin-bottom: 16px; }
  .toolbar button { padding: 8px 16px; font-size: 14px; cursor: pointer; }
  .label { border: 2px solid #111; padding: 20px 24px; display: grid; grid-template-columns: 1fr 1.4fr; gap: 20px; page-break-inside: avoid; }
  .label .from { font-size: 11px; color: #444; border-right: 1px solid #ccc; padding-right: 16px; }
  .label .from b { display: block; font-size: 13px; color: #111; margin-bottom: 4px; }
  .label .to { font-size: 18px; line-height: 1.45; }
  .label .to .kicker { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: #444; margin: 0 0 6px; }
  .label .to b { font-size: 22px; }
  .meta { display: flex; gap: 24px; margin: 14px 0 28px; font-size: 13px; color: #333; flex-wrap: wrap; }
  .meta b { color: #111; }
  .cod { border: 2px dashed #111; padding: 8px 14px; font-weight: 700; }
  h2 { font-size: 15px; margin: 24px 0 8px; }
  table { width: 100%; border-collapse: collapse; font-size: 14px; }
  th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #ddd; }
  th { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: #555; border-bottom: 2px solid #111; }
  td.qty, th.qty { text-align: center; width: 64px; }
  td.check, th.check { width: 40px; text-align: center; }
  .box { width: 18px; height: 18px; border: 2px solid #111; display: inline-block; }
  .note { margin-top: 20px; font-size: 13px; color: #333; border: 1px solid #ccc; padding: 10px 14px; }
  .foot { margin-top: 28px; font-size: 11px; color: #666; }
  @media print { .toolbar { display: none; } body { padding: 0; } }
</style>
</head>
<body>
<div class="doc">
  <div class="toolbar">
    <button onclick="window.print()">Natisni</button>
    <button onclick="window.close()">Zapri</button>
  </div>

  <div class="label">
    <div class="from">
      <b>Pošiljatelj</b>
      Zvij.si d.o.o.<br>
      <?php echo esc_html(get_option('woocommerce_store_address', '')); ?><br>
      <?php echo esc_html(trim(get_option('woocommerce_store_postcode', '') . ' ' . get_option('woocommerce_store_city', ''))); ?><br>
      zvijace@zvij.si
    </div>
    <div class="to">
      <p class="kicker">Prejemnik</p>
      <b><?php echo esc_html(trim(($shipping['first_name'] ?? '') . ' ' . ($shipping['last_name'] ?? ''))); ?></b><br>
      <?php if (! empty($shipping['company'])) : ?><?php echo esc_html($shipping['company']); ?><br><?php endif; ?>
      <?php echo esc_html($shipping['address_1'] ?? ''); ?><br>
      <?php if (! empty($shipping['address_2'])) : ?><?php echo esc_html($shipping['address_2']); ?><br><?php endif; ?>
      <?php echo esc_html(trim(($shipping['postcode'] ?? '') . ' ' . ($shipping['city'] ?? ''))); ?><br>
      <?php echo esc_html(WC()->countries->countries[$shipping['country'] ?? 'SI'] ?? ($shipping['country'] ?? '')); ?>
      <?php if ($order->get_billing_phone()) : ?><br>Tel: <?php echo esc_html($order->get_billing_phone()); ?><?php endif; ?>
    </div>
  </div>

  <div class="meta">
    <span>Naročilo: <b>#<?php echo esc_html($order->get_order_number()); ?></b></span>
    <span>Datum: <b><?php echo esc_html($order->get_date_created() ? $order->get_date_created()->date_i18n('j. n. Y') : ''); ?></b></span>
    <span>Plačilo: <b><?php echo esc_html($order->get_payment_method_title()); ?></b></span>
    <?php if ($is_cod) : ?><span class="cod">POVZETJE: <?php echo esc_html(wp_strip_all_tags(wc_price($order->get_total()))); ?></span><?php endif; ?>
  </div>

  <h2>Seznam za pakiranje</h2>
  <table>
    <thead>
      <tr><th class="check"></th><th>Izdelek</th><th>SKU</th><th class="qty">Kol.</th></tr>
    </thead>
    <tbody>
      <?php foreach ($order->get_items() as $item) :
          $product = $item->get_product();
          $sku = $product ? $product->get_sku() : '';
      ?>
      <tr>
        <td class="check"><span class="box"></span></td>
        <td><?php echo esc_html($item->get_name()); ?></td>
        <td><?php echo esc_html($sku !== '' ? $sku : '—'); ?></td>
        <td class="qty"><?php echo esc_html((string) $item->get_quantity()); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php if ($order->get_customer_note()) : ?>
    <div class="note"><b>Opomba kupca:</b> <?php echo esc_html($order->get_customer_note()); ?></div>
  <?php endif; ?>

  <div class="foot">Diskretno pakiranje — brez zunanjih oznak vsebine. Zvij.si</div>
</div>
</body>
</html>
    <?php
    exit;
});
