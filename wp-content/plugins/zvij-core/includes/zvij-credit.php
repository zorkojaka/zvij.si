<?php
/**
 * Zvij.si kristali (dobroimetje) — glej docs/DOBROIMETJE_STRATEGY.md.
 *
 * Valuta so KRISTALI (cela števila), menjava 10 kristalov = 1 €.
 * - Pripis: ko naročilo preide v plačan status (isti kriterij kot računi,
 *   zvij_invoice_statuses), član (vrstica v zvij_members po billing emailu)
 *   prejme vsoto kristalov po postavkah. Kristale na izdelku/variaciji določa
 *   meta `_zvij_kristali`; če je ni, se prebere € iz napisa
 *   `_zvij_dobroimetje_note` in pretvori (×10).
 * - Poraba: na blagajni checkbox "Uporabi kristale" → negativni fee do
 *   vrednosti izdelkov (dostava se vedno plača). Na voljo prijavljenim
 *   članom, gostom pa po vpisu svoje Zvij kode (glej zvij-referral.php).
 * - Ledger: vsaka sprememba je vrstica (earn/redeem/refund/adjust/referral/
 *   expire), stanje je vsota. Storno ob preklicu/vračilu v obe smeri.
 * - Rok trajanja: kristali ugasnejo po 12 mesecih brez aktivnosti (dnevni
 *   WP-cron); mesece določa opcija `zvij_kristali_expiry_months`.
 * - Samo store credit: brez izplačil, brez prenosa med člani.
 */

if (! defined('ABSPATH')) {
    exit;
}

const ZVIJ_CREDIT_LEDGER_VERSION_OPTION = 'zvij_credit_db_version';
const ZVIJ_KRISTALI_PER_EUR = 10;

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

    if (! wp_next_scheduled('zvij_kristali_expiry_daily')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'zvij_kristali_expiry_daily');
    }
}
add_action('plugins_loaded', 'zvij_credit_install', 11);

/** Sklanjanje: 1 kristal, 2 kristala, 3–4 kristali, 5+ kristalov. */
function zvij_kristali_beseda(int $n): string {
    $mod100 = abs($n) % 100;
    if ($mod100 === 1) {
        return 'kristal';
    }
    if ($mod100 === 2) {
        return 'kristala';
    }
    if ($mod100 === 3 || $mod100 === 4) {
        return 'kristali';
    }
    return 'kristalov';
}

function zvij_kristali_izpis(int $n): string {
    return $n . ' ' . zvij_kristali_beseda($n);
}

function zvij_kristali_eur(int $kristali): float {
    return round($kristali / ZVIJ_KRISTALI_PER_EUR, 2);
}

function zvij_credit_format_eur(float $amount): string {
    return number_format($amount, 2, ',', '.');
}

/** Stanje člana v kristalih (celo število). */
function zvij_credit_balance(string $email): int {
    global $wpdb;
    $email = sanitize_email($email);
    if ($email === '') {
        return 0;
    }
    $table = zvij_credit_table();
    return (int) round((float) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount), 0) FROM {$table} WHERE member_email = %s", $email)));
}

function zvij_credit_add(string $email, int $kristali, string $type, ?int $order_id = null, string $note = ''): bool {
    global $wpdb;
    $email = sanitize_email($email);
    if ($email === '' || $kristali === 0) {
        return false;
    }

    return (bool) $wpdb->insert(zvij_credit_table(), [
        'member_email' => $email,
        'order_id' => $order_id,
        'amount' => $kristali,
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
 * Kristali za en kos izdelka/variacije: meta `_zvij_kristali` (variacija ima
 * prednost), sicer € iz javnega napisa × 10.
 */
function zvij_credit_product_kristali(WC_Product $product): int {
    foreach ([$product->get_id(), $product->get_parent_id()] as $id) {
        if (! $id) {
            continue;
        }
        $meta = get_post_meta($id, '_zvij_kristali', true);
        if ($meta !== '' && is_numeric($meta)) {
            return max(0, (int) $meta);
        }
        $note = (string) get_post_meta($id, '_zvij_dobroimetje_note', true);
        if ($note !== '' && preg_match('/([0-9]+(?:[.,][0-9]+)?)\s*€/u', $note, $m)) {
            return (int) round(((float) str_replace(',', '.', $m[1])) * ZVIJ_KRISTALI_PER_EUR);
        }
    }

    return 0;
}

function zvij_credit_order_earnable(WC_Order $order): int {
    $total = 0;
    foreach ($order->get_items() as $item) {
        if (! $item instanceof WC_Order_Item_Product) {
            continue;
        }
        $product = $item->get_product();
        if (! $product instanceof WC_Product) {
            continue;
        }
        $total += zvij_credit_product_kristali($product) * max(1, (int) $item->get_quantity());
    }

    return $total;
}

/**
 * Pripis ob prehodu v plačan status. Idempotentno prek order meta.
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

    $kristali = zvij_credit_order_earnable($order);
    if ($kristali <= 0) {
        return;
    }

    zvij_credit_add($email, $kristali, 'earn', (int) $order->get_id(), 'Pripis ob naročilu #' . $order->get_id());
    $order->update_meta_data('_zvij_credit_earned', (string) $kristali);
    $order->save();
    $order->add_order_note(sprintf('Kristali: članu pripisano %s.', zvij_kristali_izpis($kristali)));
}
add_action('woocommerce_order_status_changed', 'zvij_credit_earn_on_paid', 30, 4);

/**
 * Storno ob preklicu/vračilu: obrne pripis in vrne porabljene kristale.
 */
function zvij_credit_reverse_on_cancel($order_id, $from, $to, $order): void {
    if (! $order instanceof WC_Order) {
        return;
    }
    if (! in_array($to, ['cancelled', 'refunded', 'failed'], true)) {
        return;
    }

    $email = sanitize_email($order->get_billing_email());

    $earned = (int) $order->get_meta('_zvij_credit_earned');
    if ($earned > 0 && $order->get_meta('_zvij_credit_earn_reversed') === '') {
        zvij_credit_add($email, -$earned, 'adjust', (int) $order->get_id(), 'Storno pripisa, naročilo #' . $order->get_id() . ' → ' . $to);
        $order->update_meta_data('_zvij_credit_earn_reversed', '1');
    }

    $redeemed = (int) $order->get_meta('_zvij_credit_redeemed');
    if ($redeemed > 0 && $order->get_meta('_zvij_credit_redeem_restored') === '') {
        zvij_credit_add($email, $redeemed, 'refund', (int) $order->get_id(), 'Vračilo porabe, naročilo #' . $order->get_id() . ' → ' . $to);
        $order->update_meta_data('_zvij_credit_redeem_restored', '1');
    }

    $order->save();
}
add_action('woocommerce_order_status_changed', 'zvij_credit_reverse_on_cancel', 30, 4);

/**
 * Email člana, ki sme unovčevati na tej blagajni: prijavljen uporabnik ali
 * gost, ki je vpisal svojo Zvij kodo (session postavi zvij-referral.php).
 */
function zvij_credit_checkout_email(): string {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        return sanitize_email((string) $user->user_email);
    }

    if (function_exists('zvij_referral_session_member_email')) {
        return zvij_referral_session_member_email();
    }

    return '';
}

function zvij_credit_available_for_checkout(): int {
    $email = zvij_credit_checkout_email();
    if ($email === '' || ! zvij_membership_find_by_email($email)) {
        return 0;
    }
    return max(0, zvij_credit_balance($email));
}

/** Checkbox za porabo kristalov — znotraj payment fragmenta (glej opombo
 * pri Zvij koda polju v zvij-referral.php). */
add_action('woocommerce_review_order_before_submit', function (): void {
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
        <?php echo esc_html(sprintf(__('Uporabi kristale (na voljo %1$s = %2$s €)', 'zvij-core'), zvij_kristali_izpis($available), zvij_credit_format_eur(zvij_kristali_eur($available)))); ?>
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

/**
 * Negativni fee: celo število kristalov, največ do vrednosti izdelkov
 * (dostava se vedno plača).
 */
function zvij_credit_checkout_spend(WC_Cart $cart): int {
    $available = zvij_credit_available_for_checkout();
    if ($available <= 0) {
        return 0;
    }
    $cap_kristali = (int) floor(((float) $cart->get_cart_contents_total()) * ZVIJ_KRISTALI_PER_EUR);
    return max(0, min($available, $cap_kristali));
}

add_action('woocommerce_cart_calculate_fees', function (WC_Cart $cart): void {
    if (is_admin() && ! defined('DOING_AJAX')) {
        return;
    }
    if (! WC()->session || ! WC()->session->get('zvij_use_credit')) {
        return;
    }

    $kristali = zvij_credit_checkout_spend($cart);
    if ($kristali <= 0) {
        return;
    }

    $cart->add_fee(__('Kristali', 'zvij-core'), -zvij_kristali_eur($kristali), false);
});

/** Ob oddaji naročila zabeleži porabo in počisti sejo. */
add_action('woocommerce_checkout_order_processed', function (int $order_id): void {
    $order = wc_get_order($order_id);
    if (! $order instanceof WC_Order) {
        return;
    }

    $redeemed_eur = 0.0;
    foreach ($order->get_fees() as $fee) {
        if ($fee->get_name() === __('Kristali', 'zvij-core') && (float) $fee->get_total() < 0) {
            $redeemed_eur += -(float) $fee->get_total();
        }
    }

    if ($redeemed_eur <= 0) {
        return;
    }

    $kristali = (int) round($redeemed_eur * ZVIJ_KRISTALI_PER_EUR);

    $email = zvij_credit_checkout_email();
    if ($email === '') {
        $email = sanitize_email($order->get_billing_email());
    }

    zvij_credit_add($email, -$kristali, 'redeem', $order_id, 'Poraba pri naročilu #' . $order_id);
    $order->update_meta_data('_zvij_credit_redeemed', (string) $kristali);
    $order->save();
    $order->add_order_note(sprintf('Kristali: porabljeno %s.', zvij_kristali_izpis($kristali)));

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
    $months = zvij_kristali_expiry_months();
    ?>
    <section class="zvij-credit-account">
      <h3><?php esc_html_e('Kristali', 'zvij-core'); ?></h3>
      <p class="zvij-credit-account__balance">
        <strong><?php echo esc_html(zvij_kristali_izpis($balance)); ?></strong>
        (<?php echo esc_html(zvij_credit_format_eur(zvij_kristali_eur($balance))); ?> €)
        — <?php esc_html_e('unovčiš jih na blagajni kot popust.', 'zvij-core'); ?>
        <?php echo esc_html(sprintf(__('Veljajo %d mesecev od zadnje aktivnosti.', 'zvij-core'), $months)); ?>
      </p>
      <?php if ($recent !== []) : ?>
        <table class="woocommerce-table shop_table shop_table_responsive">
          <thead><tr><th><?php esc_html_e('Datum', 'zvij-core'); ?></th><th><?php esc_html_e('Opis', 'zvij-core'); ?></th><th><?php esc_html_e('Kristali', 'zvij-core'); ?></th></tr></thead>
          <tbody>
          <?php foreach ($recent as $row) : ?>
            <tr>
              <td><?php echo esc_html(mysql2date('j. n. Y', (string) $row['created_at'])); ?></td>
              <td><?php echo esc_html((string) $row['note']); ?></td>
              <td><?php echo esc_html(($row['amount'] >= 0 ? '+' : '') . (int) $row['amount']); ?></td>
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
    $earned = (int) $order->get_meta('_zvij_credit_earned');
    if ($earned > 0) {
        echo '<p style="margin:12px 0;">' . esc_html(sprintf(__('Kristali: za ta nakup ti pripišemo %s za naslednji reload.', 'zvij-core'), zvij_kristali_izpis($earned))) . '</p>';
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
        . esc_html(sprintf(__('Član prejme %s za naslednji reload — pripišejo se, ko je naročilo plačano.', 'zvij-core'), zvij_kristali_izpis($earnable)))
        . '</div>';
}, 4);

/** Skupna obveznost iz kristalov (v €) za operativni pregled. */
function zvij_credit_total_outstanding(): float {
    global $wpdb;
    $table = zvij_credit_table();
    $kristali = (int) round((float) $wpdb->get_var("SELECT COALESCE(SUM(amount), 0) FROM {$table}"));
    return zvij_kristali_eur(max(0, $kristali));
}

/** Rok trajanja: meseci brez aktivnosti, po katerih kristali ugasnejo. */
function zvij_kristali_expiry_months(): int {
    return max(1, (int) get_option('zvij_kristali_expiry_months', 12));
}

/**
 * Dnevni cron: članom, ki toliko mesecev niso imeli nobene spremembe
 * (nakupa ali porabe), stanje ugasne z 'expire' vrstico — sled ostane.
 */
function zvij_kristali_expire_stale(): int {
    global $wpdb;
    $table = zvij_credit_table();
    $cutoff = gmdate('Y-m-d H:i:s', strtotime('-' . zvij_kristali_expiry_months() . ' months', current_time('timestamp')));

    $rows = (array) $wpdb->get_results(
        $wpdb->prepare(
            "SELECT member_email, SUM(amount) AS balance, MAX(created_at) AS last_activity
             FROM {$table} GROUP BY member_email
             HAVING balance > 0 AND last_activity < %s",
            $cutoff
        ),
        ARRAY_A
    );

    $expired = 0;
    foreach ($rows as $row) {
        $balance = (int) round((float) $row['balance']);
        if ($balance <= 0) {
            continue;
        }
        zvij_credit_add((string) $row['member_email'], -$balance, 'expire', null, sprintf('Kristali potekli (%d mesecev brez aktivnosti)', zvij_kristali_expiry_months()));
        $expired++;
    }

    return $expired;
}
add_action('zvij_kristali_expiry_daily', 'zvij_kristali_expire_stale');
