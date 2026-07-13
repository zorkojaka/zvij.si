<?php
/**
 * Zvij.si operational dashboard: orders needing action, sales KPIs, top products,
 * low stock, members. Built for launch-scale order volumes (in-PHP aggregation).
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function (): void {
    add_menu_page(
        'Zvij.si',
        'Zvij.si',
        'manage_woocommerce',
        'zvij-dashboard',
        'zvij_dashboard_page',
        'dashicons-store',
        3
    );
});

/** Paid orders created in the last N days. */
function zvij_dashboard_orders(int $days): array {
    return wc_get_orders([
        'limit' => -1,
        'status' => zvij_paid_statuses(),
        'date_created' => '>=' . strtotime('-' . $days . ' days midnight'),
    ]);
}

function zvij_dashboard_sum(array $orders): float {
    return array_sum(array_map(static fn (WC_Order $o): float => (float) $o->get_total(), $orders));
}

function zvij_dashboard_page(): void {
    if (! current_user_can('manage_woocommerce')) {
        return;
    }

    $orders_30 = zvij_dashboard_orders(30);
    $orders_7 = array_values(array_filter($orders_30, static fn (WC_Order $o): bool => $o->get_date_created() && $o->get_date_created()->getTimestamp() >= strtotime('-7 days midnight')));
    $orders_today = array_values(array_filter($orders_30, static fn (WC_Order $o): bool => $o->get_date_created() && $o->get_date_created()->getTimestamp() >= strtotime('today midnight')));

    $rev_30 = zvij_dashboard_sum($orders_30);
    $aov_30 = count($orders_30) > 0 ? $rev_30 / count($orders_30) : 0.0;

    // Orders waiting for action.
    $needs_action = wc_get_orders([
        'limit' => 30,
        'status' => ['processing', 'on-hold', ZVIJ_ORDER_STATUS_READY],
        'orderby' => 'date',
        'order' => 'ASC',
    ]);

    // Top products in the last 30 days.
    $product_totals = [];
    foreach ($orders_30 as $order) {
        foreach ($order->get_items() as $item) {
            $key = $item->get_name();
            if (! isset($product_totals[$key])) {
                $product_totals[$key] = ['qty' => 0, 'total' => 0.0];
            }
            $product_totals[$key]['qty'] += $item->get_quantity();
            $product_totals[$key]['total'] += (float) $item->get_total();
        }
    }
    uasort($product_totals, static fn (array $a, array $b): int => $b['total'] <=> $a['total']);
    $product_totals = array_slice($product_totals, 0, 8, true);

    // Low stock: managed-stock products under the notification threshold.
    $low_stock = [];
    foreach (wc_get_products(['limit' => -1, 'status' => 'publish']) as $p) {
        $candidates = $p->is_type('variable') ? array_map('wc_get_product', $p->get_children()) : [$p];
        foreach ($candidates as $c) {
            if (! $c || ! $c->managing_stock()) {
                continue;
            }
            $threshold = (int) get_option('woocommerce_notify_low_stock_amount', 2);
            if ((int) $c->get_stock_quantity() <= $threshold) {
                $low_stock[] = $c;
            }
        }
        if ($p->get_stock_status() === 'outofstock') {
            $low_stock[] = $p;
        }
    }

    // Members.
    global $wpdb;
    $members_table = zvij_membership_table();
    $members_total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$members_table} WHERE status = 'subscribed'");
    $members_customers = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$members_table} WHERE status = 'subscribed' AND customer_status = 'customer'");
    $members_recent = $wpdb->get_results("SELECT email, name, source, created_at FROM {$members_table} ORDER BY created_at DESC LIMIT 6", ARRAY_A);
    ?>
    <div class="wrap zvij-dash">
      <h1>Zvij.si — operativni pregled</h1>
      <style>
        .zvij-dash .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin: 16px 0 24px; max-width: 1100px; }
        .zvij-dash .kpi { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 14px 16px; }
        .zvij-dash .kpi b { display: block; font-size: 22px; margin-top: 4px; }
        .zvij-dash .kpi span { color: #646970; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        .zvij-dash .cols { display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; max-width: 1100px; align-items: start; }
        .zvij-dash .panel { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 16px; margin-bottom: 20px; }
        .zvij-dash .panel h2 { margin: 0 0 10px; font-size: 14px; }
        .zvij-dash table { width: 100%; border-collapse: collapse; }
        .zvij-dash th, .zvij-dash td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #f0f0f1; font-size: 13px; }
        .zvij-dash .status-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; background: #f0f0f1; }
        .zvij-dash .status-processing { background: #c8e6c9; }
        .zvij-dash .status-on-hold { background: #ffe0b2; }
        .zvij-dash .status-zvij-ready { background: #bbdefb; }
        @media (max-width: 1100px) { .zvij-dash .cols { grid-template-columns: 1fr; } }
      </style>

      <div class="kpis">
        <div class="kpi"><span>Danes</span><b><?php echo wp_kses_post(wc_price(zvij_dashboard_sum($orders_today))); ?></b><?php echo count($orders_today); ?> naročil</div>
        <div class="kpi"><span>Zadnjih 7 dni</span><b><?php echo wp_kses_post(wc_price(zvij_dashboard_sum($orders_7))); ?></b><?php echo count($orders_7); ?> naročil</div>
        <div class="kpi"><span>Zadnjih 30 dni</span><b><?php echo wp_kses_post(wc_price($rev_30)); ?></b><?php echo count($orders_30); ?> naročil</div>
        <div class="kpi"><span>Povprečno naročilo (30 d)</span><b><?php echo wp_kses_post(wc_price($aov_30)); ?></b></div>
        <div class="kpi"><span>Člani Zvij.si</span><b><?php echo (int) $members_total; ?></b><?php echo (int) $members_customers; ?> jih je že kupilo</div>
        <?php if (function_exists('zvij_credit_total_outstanding')) : ?>
          <div class="kpi"><span>Dobroimetje (obveznost)</span><b><?php echo wp_kses_post(wc_price(zvij_credit_total_outstanding())); ?></b>neizkoriščeno stanje članov</div>
        <?php endif; ?>
        <?php if (function_exists('zvij_reload_pending_orders')) : ?>
          <div class="kpi"><span>Reload opomniki</span><b><?php echo count(zvij_reload_pending_orders()); ?></b>čakajočih (interval nastaviš na izdelku)</div>
        <?php endif; ?>
        <?php if (function_exists('zvij_cart_stats')) : $cart_stats = zvij_cart_stats(); ?>
          <div class="kpi"><span>Zapuščene košarice</span><b><?php echo (int) $cart_stats['active'] + (int) $cart_stats['reminded']; ?></b><?php echo (int) $cart_stats['reminded']; ?> opomnjenih · <?php echo (int) $cart_stats['recovered']; ?> rešenih</div>
        <?php endif; ?>
      </div>

      <div class="cols">
        <div>
          <div class="panel">
            <h2>Naročila za obdelavo</h2>
            <?php if ($needs_action === []) : ?>
              <p>Trenutno ni odprtih naročil. 🎉</p>
            <?php else : ?>
              <table>
                <thead><tr><th>Naročilo</th><th>Kupec</th><th>Znesek</th><th>Status</th><th>Odprema</th></tr></thead>
                <tbody>
                <?php foreach ($needs_action as $o) : ?>
                  <tr>
                    <td><a href="<?php echo esc_url($o->get_edit_order_url()); ?>">#<?php echo esc_html($o->get_order_number()); ?></a><br><small><?php echo esc_html($o->get_date_created() ? $o->get_date_created()->date_i18n('j. n. Y H:i') : ''); ?></small></td>
                    <td><?php echo esc_html($o->get_formatted_billing_full_name()); ?></td>
                    <td><?php echo wp_kses_post(wc_price((float) $o->get_total())); ?></td>
                    <td><span class="status-badge status-<?php echo esc_attr($o->get_status()); ?>"><?php echo esc_html(wc_get_order_status_name($o->get_status())); ?></span></td>
                    <td><a class="button button-small" target="_blank" href="<?php echo esc_url(zvij_order_print_url($o->get_id())); ?>">Natisni</a></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>

          <div class="panel">
            <h2>Najprodajanejši izdelki (30 dni)</h2>
            <?php if ($product_totals === []) : ?>
              <p>Še ni prodaje v zadnjih 30 dneh.</p>
            <?php else : ?>
              <table>
                <thead><tr><th>Izdelek</th><th>Kosov</th><th>Prihodek</th></tr></thead>
                <tbody>
                <?php foreach ($product_totals as $name => $row) : ?>
                  <tr><td><?php echo esc_html($name); ?></td><td><?php echo (int) $row['qty']; ?></td><td><?php echo wp_kses_post(wc_price($row['total'])); ?></td></tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>

        <div>
          <div class="panel">
            <h2>Zaloga — opozorila</h2>
            <?php if ($low_stock === []) : ?>
              <p>Ni izdelkov z nizko zalogo (upravljanje zalog je vklopljeno samo na izdelkih, kjer je nastavljeno).</p>
            <?php else : ?>
              <ul>
                <?php foreach ($low_stock as $p) : ?>
                  <li><a href="<?php echo esc_url(get_edit_post_link($p->get_parent_id() ?: $p->get_id())); ?>"><?php echo esc_html($p->get_name()); ?></a> — <?php echo $p->get_stock_status() === 'outofstock' ? 'ni na zalogi' : 'zaloga: ' . (int) $p->get_stock_quantity(); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>

          <div class="panel">
            <h2>Novi člani</h2>
            <?php if (! $members_recent) : ?>
              <p>Še ni prijav.</p>
            <?php else : ?>
              <table>
                <thead><tr><th>Email</th><th>Vir</th><th>Kdaj</th></tr></thead>
                <tbody>
                <?php foreach ($members_recent as $m) : ?>
                  <tr><td><?php echo esc_html($m['email']); ?></td><td><?php echo esc_html($m['source']); ?></td><td><?php echo esc_html(mysql2date('j. n. Y', $m['created_at'])); ?></td></tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
            <p><a href="<?php echo esc_url(admin_url('options-general.php?page=zvij-membership-email')); ?>">Nastavitve članskega emaila →</a></p>
          </div>
        </div>
      </div>
    </div>
    <?php
}
