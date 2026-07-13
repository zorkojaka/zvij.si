<?php
/**
 * Zapuščene košarice — opomnik po emailu, samo za člane s privolitvijo.
 *
 * Zajem (samo emaili, za katere obstaja privolitev v zvij_members):
 * - prijavljen član: ob vsaki spremembi košarice (`woocommerce_cart_updated`);
 * - gost, ki je član: ko na blagajni vpiše svoj email (checkout AJAX
 *   `woocommerce_checkout_update_order_review` pošlje billing_email).
 * Ena vrstica na email (zadnja košarica) v tabeli wp_zvij_carts.
 *
 * Pošiljanje: urni WP-cron; košarica brez spremembe `zvij_cart_reminder_hours`
 * ur (privzeto 6) dobi EN opomnik (status reminded); nova sprememba košarice
 * status vrne na active, a varovalka dovoli največ en email na članski email
 * na 7 dni. Pred pošiljanjem se ponovno preveri privolitev in ali je bil vmes
 * že oddan kakšen nakup. Linki nosijo UTM parametre → kampanjska atribucija
 * v Plausible. Odjava: isti HMAC endpoint kot reload opomniki.
 *
 * Ob oddanem naročilu se košarica označi kot recovered (šteje na dashboardu);
 * izpraznjena košarica brez nakupa vrstico pobriše.
 */

if (! defined('ABSPATH')) {
    exit;
}

function zvij_cart_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'zvij_carts';
}

function zvij_cart_install(): void {
    global $wpdb;

    if ((string) get_option('zvij_cart_db_version', '') === ZVIJ_CORE_VERSION) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset = $wpdb->get_charset_collate();
    $table = zvij_cart_table();

    dbDelta(
        "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email varchar(190) NOT NULL,
            items text NOT NULL,
            cart_total decimal(10,2) NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'active',
            last_reminded_at datetime NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email),
            KEY status (status)
        ) {$charset};"
    );

    update_option('zvij_cart_db_version', ZVIJ_CORE_VERSION, false);

    if (! wp_next_scheduled('zvij_cart_reminder_hourly')) {
        wp_schedule_event(time() + 15 * MINUTE_IN_SECONDS, 'hourly', 'zvij_cart_reminder_hourly');
    }
}
add_action('plugins_loaded', 'zvij_cart_install', 13);

function zvij_cart_reminder_hours(): int {
    return max(1, (int) get_option('zvij_cart_reminder_hours', 6));
}

/** Snapshot trenutne košarice: [['name' =>, 'qty' =>], ...]. */
function zvij_cart_snapshot(): array {
    if (! function_exists('WC') || ! WC()->cart) {
        return [];
    }

    $items = [];
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'] ?? null;
        if ($product instanceof WC_Product) {
            $items[] = ['name' => $product->get_name(), 'qty' => max(1, (int) ($cart_item['quantity'] ?? 1))];
        }
    }

    return $items;
}

/** Upsert košarice za email; prazna košarica aktivno vrstico pobriše. */
function zvij_cart_capture(string $email, array $items, float $total): void {
    global $wpdb;

    $email = sanitize_email($email);
    if ($email === '' || ! zvij_reload_member_subscribed($email)) {
        return;
    }

    $table = zvij_cart_table();
    $now = current_time('mysql');
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE email = %s", $email), ARRAY_A);

    if ($items === []) {
        if ($row && in_array((string) $row['status'], ['active', 'reminded'], true)) {
            $wpdb->delete($table, ['email' => $email], ['%s']);
        }
        return;
    }

    $items_json = wp_json_encode($items);

    if ($row) {
        // Nespremenjena vsebina naj NE podaljšuje updated_at v nedogled —
        // capture se sproži tudi ob navadnem nalaganju strani s košarico.
        if ((string) $row['items'] === $items_json && in_array((string) $row['status'], ['active', 'reminded'], true)) {
            return;
        }
        $wpdb->update(
            $table,
            ['items' => $items_json, 'cart_total' => $total, 'status' => 'active', 'updated_at' => $now],
            ['email' => $email],
            ['%s', '%f', '%s', '%s'],
            ['%s']
        );
        return;
    }

    $wpdb->insert($table, [
        'email' => $email,
        'items' => $items_json,
        'cart_total' => $total,
        'status' => 'active',
        'created_at' => $now,
        'updated_at' => $now,
    ], ['%s', '%s', '%f', '%s', '%s', '%s']);
}

/** Prijavljen član: vsaka sprememba košarice. */
add_action('woocommerce_cart_updated', function (): void {
    if (! is_user_logged_in() || ! function_exists('WC') || ! WC()->cart) {
        return;
    }
    $email = sanitize_email((string) wp_get_current_user()->user_email);
    zvij_cart_capture($email, zvij_cart_snapshot(), (float) WC()->cart->get_cart_contents_total());
});

/** Gost na blagajni: checkout AJAX pošlje billing_email ob vsaki spremembi polj. */
add_action('woocommerce_checkout_update_order_review', function ($post_data): void {
    if (is_user_logged_in()) {
        return; // prijavljene pokrije woocommerce_cart_updated
    }
    parse_str((string) $post_data, $data);
    $email = sanitize_email((string) ($data['billing_email'] ?? ''));
    if ($email === '' || ! is_email($email)) {
        return;
    }
    zvij_cart_capture($email, zvij_cart_snapshot(), WC()->cart ? (float) WC()->cart->get_cart_contents_total() : 0.0);
}, 20);

/** Oddano naročilo = rešena košarica (za KPI); ob praznjenju se ne pobriše. */
add_action('woocommerce_checkout_order_processed', function (int $order_id): void {
    $order = wc_get_order($order_id);
    if (! $order instanceof WC_Order) {
        return;
    }
    $email = sanitize_email($order->get_billing_email());
    if ($email === '') {
        return;
    }

    global $wpdb;
    $wpdb->query($wpdb->prepare(
        "UPDATE " . zvij_cart_table() . " SET status = 'recovered', updated_at = %s WHERE email = %s AND status IN ('active', 'reminded')",
        current_time('mysql'),
        $email
    ));
}, 30);

/** Urni cron: en opomnik na zapuščeno košarico (in največ en na email na 7 dni). */
function zvij_cart_send_due_reminders(): int {
    global $wpdb;
    $table = zvij_cart_table();
    $now_ts = current_time('timestamp');
    $stale_before = gmdate('Y-m-d H:i:s', $now_ts - zvij_cart_reminder_hours() * HOUR_IN_SECONDS);
    $throttle_before = gmdate('Y-m-d H:i:s', $now_ts - 7 * DAY_IN_SECONDS);

    $rows = (array) $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table}
         WHERE status = 'active' AND updated_at < %s
           AND (last_reminded_at IS NULL OR last_reminded_at < %s)
         LIMIT 50",
        $stale_before,
        $throttle_before
    ), ARRAY_A);

    $sent = 0;
    foreach ($rows as $row) {
        $email = (string) $row['email'];

        if (! zvij_reload_member_subscribed($email)) {
            $wpdb->delete($table, ['email' => $email], ['%s']);
            continue;
        }

        // Varnost: vmes oddano naročilo (npr. iz druge naprave) = rešeno.
        $orders_since = wc_get_orders([
            'billing_email' => $email,
            'date_created' => '>=' . strtotime((string) $row['updated_at']),
            'return' => 'ids',
            'limit' => 1,
        ]);
        if ($orders_since !== []) {
            $wpdb->update($table, ['status' => 'recovered'], ['email' => $email], ['%s'], ['%s']);
            continue;
        }

        zvij_cart_send_reminder_email($email, (array) json_decode((string) $row['items'], true), (float) $row['cart_total']);
        $wpdb->update(
            $table,
            ['status' => 'reminded', 'last_reminded_at' => current_time('mysql')],
            ['email' => $email],
            ['%s', '%s'],
            ['%s']
        );
        $sent++;
    }

    return $sent;
}
add_action('zvij_cart_reminder_hourly', 'zvij_cart_send_due_reminders');

function zvij_cart_utm(string $url): string {
    return add_query_arg([
        'utm_source' => 'zvij',
        'utm_medium' => 'email',
        'utm_campaign' => 'zapuscena-kosarica',
    ], $url);
}

function zvij_cart_send_reminder_email(string $email, array $items, float $total): bool {
    $member = zvij_membership_find_by_email($email);
    $first_name = $member ? trim(explode(' ', (string) $member['name'])[0]) : '';
    $cart_url = zvij_cart_utm(wc_get_cart_url());
    $shop_url = zvij_cart_utm(home_url('/trgovina/'));
    $privacy_url = get_privacy_policy_url();

    $lines = [];
    foreach ($items as $item) {
        if (is_array($item) && isset($item['name'])) {
            $lines[] = sprintf('- %d× %s', max(1, (int) ($item['qty'] ?? 1)), (string) $item['name']);
        }
    }

    $subject = 'Tvoja košarica te čaka — Zvij.si';
    $message = 'Živjo' . ($first_name !== '' ? ' ' . $first_name : '') . ",\n\n";
    $message .= "v košarici si pustil:\n";
    $message .= implode("\n", $lines) . "\n";
    $message .= sprintf("Skupaj: %s €\n\n", zvij_credit_format_eur($total));
    $message .= "Košarica te čaka tukaj:\n{$cart_url}\n\n";

    if (function_exists('zvij_credit_balance')) {
        $balance = zvij_credit_balance($email);
        if ($balance > 0) {
            $message .= sprintf(
                "Psst — na računu imaš %s (= %s € popusta), ki jih lahko uporabiš na blagajni.\n\n",
                zvij_kristali_izpis($balance),
                zvij_credit_format_eur(zvij_kristali_eur($balance))
            );
        }
    }

    $message .= "Če si si premislil, ni panike — košarico lahko kadarkoli sprazniš ali zamenjaš: {$shop_url}\n\n";
    $message .= "Ne želiš več takih opomnikov? Odjava: " . zvij_member_unsubscribe_url($email) . "\n";
    if ($privacy_url !== '') {
        $message .= "Politika zasebnosti: {$privacy_url}\n";
    }
    $message .= "\nZvij.si\nTvoj vajb. Tvoja rutina. Tvoj lajf. Tvoja pravila.\n";

    $delivered = wp_mail($email, $subject, $message, ['Content-Type: text/plain; charset=UTF-8']);

    update_option('zvij_cart_last_reminder', [
        'to' => $email,
        'subject' => $subject,
        'body' => $message,
        'delivered' => $delivered ? 'yes' : 'no',
        'at' => current_time('mysql'),
    ], false);

    return $delivered;
}

/** Statistika za operativni pregled. */
function zvij_cart_stats(): array {
    global $wpdb;
    $table = zvij_cart_table();
    $counts = ['active' => 0, 'reminded' => 0, 'recovered' => 0];
    foreach ((array) $wpdb->get_results("SELECT status, COUNT(*) AS n FROM {$table} GROUP BY status", ARRAY_A) as $row) {
        $counts[(string) $row['status']] = (int) $row['n'];
    }
    return $counts;
}
