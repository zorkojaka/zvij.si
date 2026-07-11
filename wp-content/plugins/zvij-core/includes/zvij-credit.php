<?php
/**
 * Zvij.si dobroimetje (store credit) — glej docs/DOBROIMETJE_STRATEGY.md.
 *
 * Mehanika:
 * - Pripis: ko naročilo preide v plačan status (isti kriterij kot računi,
 *   zvij_invoice_statuses), član (vrstica v zvij_members po billing emailu)
 *   prejme vsoto zneskov dobroimetja po postavkah. Znesek na izdelku/variaciji
 *   določa meta `_zvij_dobroimetje_eur`; če je ni, se prebere iz javnega
 *   napisa `_zvij_dobroimetje_note` ("Član prejme X € za naslednji reload.").
 * - Poraba: prijavljen član na blagajni obkljuka "Uporabi dobroimetje" →
 *   negativni fee do višine vrednosti izdelkov (dostava se vedno plača).
 * - Vse spremembe so vrstice v ledger tabeli (revizijska sled); stanje je
 *   vsota. Storno ob preklicu/vračilu naročila v obe smeri.
 * - Samo store credit: brez izplačil, brez prenosa med člani.
 */

if (! defined('ABSPATH')) {
    exit;
}

const ZVIJ_CREDIT_LEDGER_VERSION_OPTION = 'zvij_credit_db_version';

function zvij_credit_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'zvij_credit_ledger';
}

function zvij_credit_install(): void {
    global $wpdb;

    if ((string) get_option(ZVIJ_CREDIT_LEDGER_VERSION_OPTION, '') === ZVIJ_CORE_VERSION) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset = $wpdb->get_charset_collate();
    $table = zvij_credit_table();

    dbDelta(
        "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            member_email varchar(190) NOT NULL,
            order_id bigint(20) unsigned NULL,
            amount decimal(10,2) NOT NULL,
            type varchar(30) NOT NULL DEFAULT 'adjust',
            note varchar(190) NOT NULL DEFAULT '',
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY member_email (member_email),
            KEY order_id (order_id)
        ) {$charset};"
    );

    update_option(ZVIJ_CREDIT_LEDGER_VERSION_OPTION, ZVIJ_CORE_VERSION, false);
}
add_action('plugins_loaded', 'zvij_credit_install', 11);

function zvij_credit_format(float $amount): string {
    return number_format($amount, 2, ',', '.');
}

function zvij_credit_balance(string $email): float {
    global $wpdb;
    $email = sanitize_email($email);
    if ($email === '') {
        return 0.0;
    }
    $table = zvij_credit_table();
    return (float) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount), 0) FROM {$table} WHERE member_email = %s", $email));
}

function zvij_credit_add(string $email, float $amount, string $type, ?int $order_id = null, string $note = ''): bool {
    global $wpdb;
    $email = sanitize_email($email);
    if ($email === '' || abs($amount) < 0.005) {
        return false;
    }

    return (bool) $wpdb->insert(zvij_credit_table(), [
        'member_email' => $email,
        'order_id' => $order_id,
        'amount' => round($amount, 2),
        'type' => sanitize_key($type),
        'note' => sanitize_text_field($note),
        'created_at' => current_time('mysql'),
    ]);
}

function zvij_credit_recent(string $email, int $limit = 5): array {
    global $wpdb;
    $table = zvij_credit_table();
    return (array) $wpdb->get_results(
        $wpdb->prepare("SELECT amount, type, order_id, note, created_at FROM {$table} WHERE member_email = %s ORDER BY id DESC LIMIT %d", sanitize_email($email), $limit),
        ARRAY_A
    );
}

/**
 * Znesek dobroimetja za en kos izdelka/variacije: meta `_zvij_dobroimetje_eur`
 * (variacija ima prednost), sicer razčlenjen iz javnega napisa.
 */
function zvij_credit_product_amount(WC_Product $product): float {
    foreach ([$product->get_id(), $product->get_parent_id()] as $id) {
        if (! $id) {
            continue;
        }
        $meta = get_post_meta($id, '_zvij_dobroimetje_eur', true);
        if ($meta !== '' && is_numeric(str_replace(',', '.', (string) $meta))) {
            return (float) str_replace(',', '.', (string) $meta);
        }
        $note = (string) get_post_meta($id, '_zvij_dobroimetje_note', true);
        if ($note !== '' && preg_match('/([0-9]+(?:[.,][0-9]+)?)\s*€/u', $note, $m)) {
            return (float) str_replace(',', '.', $m[1]);
        }
    }

    return 0.0;
}

function zvij_credit_order_earnable(WC_Order $order): float {
    $total = 0.0;
    foreach ($order->get_items() as $item) {
        if (! $item instanceof WC_Order_Item_Product) {
            continue;
        }
        $product = $item->get_product();
        if (! $product instanceof WC_Product) {
            continue;
        }
        $total += zvij_credit_product_amount($product) * max(1, (int) $item->get_quantity());
    }

    return round($total, 2);
}

/**
 * Pripis ob prehodu v plačan status. Idempotentno prek order meta.
 * Pogoj: billing email ima vrstico med člani (kadar se član prijavi šele
 * na blagajni, vrstica ob prehodu v plačan status že obstaja).
 */
function zvij_credit_earn_on_paid($order_id, $from, $to, $order): void {
    if (! $order instanceof WC_Order || ! function_exists('zvij_invoice_statuses')) {
        return;
    }
    if (! in_array($to, zvij_invoice_statuses(), true)) {
        return;
    }
    if ($order->get_meta('_zvij_credit_earned') !== '') {
        return;
    }

    $email = sanitize_email($order->get_billing_email());
    if ($email === '' || ! zvij_membership_find_by_email($email)) {
        return;
    }

    $amount = zvij_credit_order_earnable($order);
    if ($amount <= 0) {
        return;
    }

    zvij_credit_add($email, $amount, 'earn', (int) $order->get_id(), 'Pripis ob naročilu #' . $order->get_id());
    $order->update_meta_data('_zvij_credit_earned', wc_format_decimal($amount, 2));
    $order->save();
    $order->add_order_note(sprintf('Dobroimetje: članu pripisano %s €.', wc_format_decimal($amount, 2)));
}
add_action('woocommerce_order_status_changed', 'zvij_credit_earn_on_paid', 30, 4);

/**
 * Storno ob preklicu/vračilu: obrne pripis in vrne porabljeno dobroimetje.
 */
function zvij_credit_reverse_on_cancel($order_id, $from, $to, $order): void {
    if (! $order instanceof WC_Order) {
        return;
    }
    if (! in_array($to, ['cancelled', 'refunded', 'failed'], true)) {
        return;
    }

    $email = sanitize_email($order->get_billing_email());

    $earned = (float) $order->get_meta('_zvij_credit_earned');
    if ($earned > 0 && $order->get_meta('_zvij_credit_earn_reversed') === '') {
        zvij_credit_add($email, -$earned, 'adjust', (int) $order->get_id(), 'Storno pripisa, naročilo #' . $order->get_id() . ' → ' . $to);
        $order->update_meta_data('_zvij_credit_earn_reversed', '1');
    }

    $redeemed = (float) $order->get_meta('_zvij_credit_redeemed');
    if ($redeemed > 0 && $order->get_meta('_zvij_credit_redeem_restored') === '') {
        zvij_credit_add($email, $redeemed, 'refund', (int) $order->get_id(), 'Vračilo porabe, naročilo #' . $order->get_id() . ' → ' . $to);
        $order->update_meta_data('_zvij_credit_redeem_restored', '1');
    }

    $order->save();
}
add_action('woocommerce_order_status_changed', 'zvij_credit_reverse_on_cancel', 30, 4);

/**
 * Unovčenje je omejeno na prijavljene uporabnike, ker je dobroimetje vezano
 * na email — gost bi lahko z vpisom tujega emaila porabil tuje stanje.
 */
function zvij_credit_checkout_email(): string {
    if (! is_user_logged_in()) {
        return '';
    }
    $user = wp_get_current_user();
    return sanitize_email((string) $user->user_email);
}

function zvij_credit_available_for_checkout(): float {
    $email = zvij_credit_checkout_email();
    if ($email === '' || ! zvij_membership_find_by_email($email)) {
        return 0.0;
    }
    return max(0.0, zvij_credit_balance($email));
}

/** Checkbox nad načini plačila. */
add_action('woocommerce_review_order_before_payment', function (): void {
    if (! WC()->cart || WC()->cart->is_empty()) {
        return;
    }

    $available = zvij_credit_available_for_checkout();
    if ($available <= 0) {
        return;
    }

    $checked = WC()->session && WC()->session->get('zvij_use_credit') ? ' checked' : '';
    ?>
    <div class="zvij-credit-toggle">
      <label>
        <input type="checkbox" name="zvij_use_credit" value="1"<?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
        <?php echo esc_html(sprintf(__('Uporabi dobroimetje (na voljo %s €)', 'zvij-core'), zvij_credit_format($available))); ?>
      </label>
    </div>
    <script>
      jQuery(function ($) {
        $(document.body).on('change', 'input[name="zvij_use_credit"]', function () {
          $(document.body).trigger('update_checkout');
        });
      });
    </script>
    <?php
});

/** Ob AJAX osvežitvi blagajne preberi checkbox iz serializiranih podatkov. */
add_action('woocommerce_checkout_update_order_review', function ($post_data): void {
    if (! WC()->session) {
        return;
    }
    parse_str((string) $post_data, $data);
    WC()->session->set('zvij_use_credit', ! empty($data['zvij_use_credit']));
});

/** Negativni fee: do vrednosti izdelkov (dostava se vedno plača). */
add_action('woocommerce_cart_calculate_fees', function (WC_Cart $cart): void {
    if (is_admin() && ! defined('DOING_AJAX')) {
        return;
    }
    if (! WC()->session || ! WC()->session->get('zvij_use_credit')) {
        return;
    }

    $available = zvij_credit_available_for_checkout();
    if ($available <= 0) {
        return;
    }

    $cap = (float) $cart->get_cart_contents_total();
    $amount = round(min($available, $cap), 2);
    if ($amount <= 0) {
        return;
    }

    $cart->add_fee(__('Dobroimetje', 'zvij-core'), -$amount, false);
});

/** Ob oddaji naročila zabeleži porabo in počisti sejo. */
add_action('woocommerce_checkout_order_processed', function (int $order_id): void {
    $order = wc_get_order($order_id);
    if (! $order instanceof WC_Order) {
        return;
    }

    $redeemed = 0.0;
    foreach ($order->get_fees() as $fee) {
        if ($fee->get_name() === __('Dobroimetje', 'zvij-core') && (float) $fee->get_total() < 0) {
            $redeemed += -(float) $fee->get_total();
        }
    }

    if ($redeemed <= 0) {
        return;
    }

    $email = zvij_credit_checkout_email();
    if ($email === '') {
        $email = sanitize_email($order->get_billing_email());
    }

    zvij_credit_add($email, -$redeemed, 'redeem', $order_id, 'Poraba pri naročilu #' . $order_id);
    $order->update_meta_data('_zvij_credit_redeemed', wc_format_decimal($redeemed, 2));
    $order->save();
    $order->add_order_note(sprintf('Dobroimetje: porabljeno %s €.', wc_format_decimal($redeemed, 2)));

    if (WC()->session) {
        WC()->session->set('zvij_use_credit', false);
    }
}, 20);

/** Stanje in zadnje transakcije v Moj račun. */
add_action('woocommerce_account_dashboard', function (): void {
    $user = wp_get_current_user();
    $email = sanitize_email((string) $user->user_email);
    if ($email === '' || ! zvij_membership_find_by_email($email)) {
        return;
    }

    $balance = zvij_credit_balance($email);
    $recent = zvij_credit_recent($email, 5);
    ?>
    <section class="zvij-credit-account">
      <h3><?php esc_html_e('Dobroimetje', 'zvij-core'); ?></h3>
      <p class="zvij-credit-account__balance"><strong><?php echo esc_html(zvij_credit_format($balance)); ?> €</strong> <?php esc_html_e('za naslednji reload. Unovčiš ga na blagajni.', 'zvij-core'); ?></p>
      <?php if ($recent !== []) : ?>
        <table class="woocommerce-table shop_table shop_table_responsive">
          <thead><tr><th><?php esc_html_e('Datum', 'zvij-core'); ?></th><th><?php esc_html_e('Opis', 'zvij-core'); ?></th><th><?php esc_html_e('Znesek', 'zvij-core'); ?></th></tr></thead>
          <tbody>
          <?php foreach ($recent as $row) : ?>
            <tr>
              <td><?php echo esc_html(mysql2date('j. n. Y', (string) $row['created_at'])); ?></td>
              <td><?php echo esc_html((string) $row['note']); ?></td>
              <td><?php echo esc_html(($row['amount'] >= 0 ? '+' : '') . zvij_credit_format((float) $row['amount'])); ?> €</td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
    <?php
});

/** Obvestilo o pripisu v emailu kupcu in na strani »naročilo prejeto«. */
add_action('woocommerce_email_after_order_table', function ($order): void {
    if (! $order instanceof WC_Order) {
        return;
    }
    $earned = (float) $order->get_meta('_zvij_credit_earned');
    if ($earned > 0) {
        echo '<p style="margin:12px 0;">' . esc_html(sprintf(__('Dobroimetje: za ta nakup ti pripišemo %s € za naslednji reload.', 'zvij-core'), zvij_credit_format($earned))) . '</p>';
    }
}, 20);

add_action('woocommerce_thankyou', function ($order_id): void {
    $order = wc_get_order($order_id);
    if (! $order instanceof WC_Order) {
        return;
    }

    $email = sanitize_email($order->get_billing_email());
    if ($email === '' || ! zvij_membership_find_by_email($email)) {
        return;
    }

    $earnable = zvij_credit_order_earnable($order);
    if ($earnable <= 0) {
        return;
    }

    echo '<div class="zvij-credit-thankyou" style="margin:1rem 0;padding:0.9rem 1.1rem;border:1px solid rgba(199,177,148,0.58);border-radius:8px;">'
        . esc_html(sprintf(__('Član prejme %s € dobroimetja za naslednji reload — pripiše se, ko je naročilo plačano.', 'zvij-core'), zvij_credit_format($earnable)))
        . '</div>';
}, 4);

/** Skupna obveznost iz dobroimetja za operativni pregled. */
function zvij_credit_total_outstanding(): float {
    global $wpdb;
    $table = zvij_credit_table();
    return (float) $wpdb->get_var("SELECT COALESCE(SUM(amount), 0) FROM {$table}");
}
