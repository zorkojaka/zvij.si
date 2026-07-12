<?php
/**
 * Reload opomniki — glej docs/REPEAT_ORDER_SPEC.md.
 *
 * Interval določa meta `_zvij_reload_days` na izdelku (variacija podeduje od
 * starša); brez vpisanih intervalov se NE pošlje nič — aktivacija je torej
 * Jakova odločitev (vpis dni na izdelke), skladno z guardrailom iz speca.
 *
 * Tok:
 * - ob prehodu naročila v plačan status (isti kriterij kot računi/kristali)
 *   se za ČLANA s statusom `subscribed` (privolitev iz obrazca/checkouta)
 *   izračuna rok = max interval med postavkami in shrani na naročilo
 *   (`_zvij_reload_due`, `_zvij_reload_status` = pending);
 * - vsako novo plačano naročilo istega člana prejšnje čakajoče opomnike
 *   označi kot `completed` (reload se je očitno že zgodil) → na člana je
 *   največ en čakajoč opomnik;
 * - dnevni WP-cron pošlje zapadle opomnike (plain-text email z bližnjico na
 *   Moj račun → »Ponovi naročilo«), status → `sent`, zapis v order note;
 *   pred pošiljanjem se privolitev ponovno preveri (odjave prek MailerLite
 *   webhooka ali lokalne odjave se spoštujejo);
 * - vsak opomnik vsebuje lokalni odjavni link (admin-post
 *   `zvij_member_unsubscribe` s HMAC žetonom) — odjava nastavi status
 *   `unsubscribed` v zvij_members (MailerLite se ob odjavi ne kliče; za
 *   kampanje ima MailerLite svojo odjavo, ki jo webhook sinhronizira nazaj).
 */

if (! defined('ABSPATH')) {
    exit;
}

const ZVIJ_RELOAD_STATUS_META = '_zvij_reload_status';
const ZVIJ_RELOAD_DUE_META = '_zvij_reload_due';

add_action('plugins_loaded', function (): void {
    if (! wp_next_scheduled('zvij_reload_reminder_daily')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'zvij_reload_reminder_daily');
    }
}, 12);

/** Interval v dneh za en izdelek/variacijo (0 = brez opomnika). */
function zvij_reload_product_days(WC_Product $product): int {
    foreach ([$product->get_id(), $product->get_parent_id()] as $id) {
        if (! $id) {
            continue;
        }
        $meta = get_post_meta($id, '_zvij_reload_days', true);
        if ($meta !== '' && is_numeric($meta)) {
            return max(0, (int) $meta);
        }
    }

    return 0;
}

/** Interval naročila = najdaljši interval med postavkami (0 = nič za opomnit). */
function zvij_reload_order_days(WC_Order $order): int {
    $days = 0;
    foreach ($order->get_items() as $item) {
        if (! $item instanceof WC_Order_Item_Product) {
            continue;
        }
        $product = $item->get_product();
        if ($product instanceof WC_Product) {
            $days = max($days, zvij_reload_product_days($product));
        }
    }

    return $days;
}

/** Član z veljavno privolitvijo za marketinške emaile? */
function zvij_reload_member_subscribed(string $email): bool {
    $member = zvij_membership_find_by_email($email);
    return $member !== null && (string) $member['status'] === 'subscribed';
}

/**
 * Ob prehodu v plačan status: prejšnje čakajoče opomnike člana zaključi,
 * novega razporedi (če naročilo vsebuje izdelek z intervalom).
 */
function zvij_reload_schedule_on_paid($order_id, $from, $to, $order): void {
    if (! $order instanceof WC_Order || ! function_exists('zvij_invoice_statuses')) {
        return;
    }
    if (! in_array($to, zvij_invoice_statuses(), true)) {
        return;
    }

    $email = sanitize_email($order->get_billing_email());
    if ($email === '' || ! zvij_reload_member_subscribed($email)) {
        return;
    }

    // Nov nakup = reload; starejši čakajoči opomniki niso več relevantni.
    foreach (zvij_reload_pending_orders(['billing_email' => $email]) as $pending) {
        if ($pending->get_id() === $order->get_id()) {
            continue;
        }
        $pending->update_meta_data(ZVIJ_RELOAD_STATUS_META, 'completed');
        $pending->save();
    }

    if ($order->get_meta(ZVIJ_RELOAD_STATUS_META) !== '') {
        return;
    }

    $days = zvij_reload_order_days($order);
    if ($days <= 0) {
        return;
    }

    $due = gmdate('Y-m-d H:i:s', strtotime('+' . $days . ' days', current_time('timestamp')));
    $order->update_meta_data(ZVIJ_RELOAD_STATUS_META, 'pending');
    $order->update_meta_data(ZVIJ_RELOAD_DUE_META, $due);
    $order->save();
    $order->add_order_note(sprintf('Reload opomnik razporejen za %s (interval %d dni).', mysql2date('j. n. Y', $due), $days));
}
add_action('woocommerce_order_status_changed', 'zvij_reload_schedule_on_paid', 40, 4);

/** Ob preklicu/vračilu opomnik odpade. */
add_action('woocommerce_order_status_changed', function ($order_id, $from, $to, $order): void {
    if (! $order instanceof WC_Order || ! in_array($to, ['cancelled', 'refunded', 'failed'], true)) {
        return;
    }
    if ($order->get_meta(ZVIJ_RELOAD_STATUS_META) === 'pending') {
        $order->update_meta_data(ZVIJ_RELOAD_STATUS_META, 'cancelled');
        $order->save();
    }
}, 40, 4);

/** Čakajoči opomniki (HPOS-združljivo prek wc_get_orders meta_query). */
function zvij_reload_pending_orders(array $extra = []): array {
    $args = [
        'limit' => 50,
        'meta_query' => [
            ['key' => ZVIJ_RELOAD_STATUS_META, 'value' => 'pending'],
        ],
    ];
    if (isset($extra['due_before'])) {
        $args['meta_query'][] = ['key' => ZVIJ_RELOAD_DUE_META, 'value' => $extra['due_before'], 'compare' => '<='];
    }
    if (isset($extra['billing_email'])) {
        $args['billing_email'] = $extra['billing_email'];
    }

    return array_filter((array) wc_get_orders($args), static fn ($o) => $o instanceof WC_Order);
}

/** Dnevni cron: pošlji zapadle opomnike. Vrne število poslanih. */
function zvij_reload_send_due_reminders(): int {
    $sent = 0;
    foreach (zvij_reload_pending_orders(['due_before' => gmdate('Y-m-d H:i:s', current_time('timestamp'))]) as $order) {
        $email = sanitize_email($order->get_billing_email());

        if ($email === '' || ! zvij_reload_member_subscribed($email)) {
            $order->update_meta_data(ZVIJ_RELOAD_STATUS_META, 'skipped');
            $order->save();
            $order->add_order_note('Reload opomnik preskočen: član je odjavljen ali ne obstaja.');
            continue;
        }

        $delivered = zvij_reload_send_reminder_email($order, $email);
        $order->update_meta_data(ZVIJ_RELOAD_STATUS_META, 'sent');
        $order->save();
        $order->add_order_note('Reload opomnik poslan' . ($delivered ? '.' : ' (wp_mail ni potrdil dostave).'));
        $sent++;
    }

    return $sent;
}
add_action('zvij_reload_reminder_daily', 'zvij_reload_send_due_reminders');

function zvij_reload_send_reminder_email(WC_Order $order, string $email): bool {
    $first_name = sanitize_text_field($order->get_billing_first_name());
    $orders_url = wc_get_account_endpoint_url('orders');
    $shop_url = home_url('/trgovina/');
    $privacy_url = get_privacy_policy_url();

    $items = [];
    foreach ($order->get_items() as $item) {
        $items[] = sprintf('- %d× %s', max(1, (int) $item->get_quantity()), $item->get_name());
    }

    $subject = 'Čas za reload? — Zvij.si';
    $message = 'Živjo' . ($first_name !== '' ? ' ' . $first_name : '') . ",\n\n";
    $message .= sprintf("%s si naročil:\n", $order->get_date_created() ? mysql2date('j. n. Y', $order->get_date_created()->date('Y-m-d H:i:s')) : 'Pred časom');
    $message .= implode("\n", $items) . "\n\n";
    $message .= "Zaloga počasi kopni? Reload je en klik — v Moj račun te čaka gumb »Ponovi naročilo«, ki napolni košarico z istimi izdelki:\n";
    $message .= $orders_url . "\n\n";
    $message .= "Ali pa poglej, kaj je novega: {$shop_url}\n\n";

    if (function_exists('zvij_credit_balance')) {
        $balance = zvij_credit_balance($email);
        if ($balance > 0) {
            $message .= sprintf(
                "Na računu imaš %s (= %s € popusta) — unovčiš jih na blagajni.\n\n",
                zvij_kristali_izpis($balance),
                zvij_credit_format_eur(zvij_kristali_eur($balance))
            );
        }
    }

    $message .= "Ne želiš več opomnikov? Odjava: " . zvij_member_unsubscribe_url($email) . "\n";
    if ($privacy_url !== '') {
        $message .= "Politika zasebnosti: {$privacy_url}\n";
    }
    $message .= "\nZvij.si\nTvoj vajb. Tvoja rutina. Tvoj lajf. Tvoja pravila.\n";

    $delivered = wp_mail($email, $subject, $message, ['Content-Type: text/plain; charset=UTF-8']);

    // Dev/admin pregled zadnjega opomnika (isti vzorec kot welcome email).
    update_option('zvij_reload_last_reminder', [
        'to' => $email,
        'order_id' => $order->get_id(),
        'subject' => $subject,
        'body' => $message,
        'delivered' => $delivered ? 'yes' : 'no',
        'at' => current_time('mysql'),
    ], false);

    return $delivered;
}

/** Odjavni link z žetonom, vezanim na email (brez prijave, brez poteka). */
function zvij_member_unsubscribe_token(string $email): string {
    return hash_hmac('sha256', 'zvij-unsubscribe|' . strtolower($email), wp_salt('auth'));
}

function zvij_member_unsubscribe_url(string $email): string {
    return add_query_arg([
        'action' => 'zvij_member_unsubscribe',
        'email' => rawurlencode($email),
        'token' => zvij_member_unsubscribe_token($email),
    ], admin_url('admin-post.php'));
}

function zvij_member_handle_unsubscribe(): void {
    $email = sanitize_email(rawurldecode((string) ($_GET['email'] ?? '')));
    $token = (string) ($_GET['token'] ?? '');

    if (! is_email($email) || ! hash_equals(zvij_member_unsubscribe_token($email), $token)) {
        wp_die('Neveljavna odjavna povezava.', 'Zvij.si', ['response' => 400]);
    }

    global $wpdb;
    $wpdb->update(
        zvij_membership_table(),
        ['status' => 'unsubscribed', 'updated_at' => current_time('mysql')],
        ['email' => $email],
        ['%s', '%s'],
        ['%s']
    );

    wp_die(
        '<h1>Odjava uspešna</h1><p>Na ' . esc_html($email) . ' ne bomo več pošiljali opomnikov in novic.</p><p><a href="' . esc_url(home_url('/')) . '">Nazaj na Zvij.si</a></p>',
        'Odjava — Zvij.si',
        ['response' => 200]
    );
}
add_action('admin_post_nopriv_zvij_member_unsubscribe', 'zvij_member_handle_unsubscribe');
add_action('admin_post_zvij_member_unsubscribe', 'zvij_member_handle_unsubscribe');

/** Polje »Reload opomnik (dni)« v admin urejanju izdelka (zavihek Splošno). */
add_action('woocommerce_product_options_general_product_data', function (): void {
    woocommerce_wp_text_input([
        'id' => '_zvij_reload_days',
        'label' => __('Reload opomnik (dni)', 'zvij-core'),
        'description' => __('Po koliko dneh od plačila član prejme opomnik za reload. Prazno = brez opomnika.', 'zvij-core'),
        'desc_tip' => true,
        'type' => 'number',
        'custom_attributes' => ['min' => '0', 'step' => '1'],
    ]);
});

add_action('woocommerce_admin_process_product_object', function (WC_Product $product): void {
    $raw = isset($_POST['_zvij_reload_days']) ? sanitize_text_field((string) $_POST['_zvij_reload_days']) : '';
    if ($raw === '' || ! is_numeric($raw)) {
        $product->delete_meta_data('_zvij_reload_days');
    } else {
        $product->update_meta_data('_zvij_reload_days', (string) max(0, (int) $raw));
    }
});
