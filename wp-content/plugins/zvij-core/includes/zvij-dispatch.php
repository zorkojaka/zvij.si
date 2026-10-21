<?php
/** Payment and dispatch are independent facts, including for cash on delivery. */
if (! defined('ABSPATH')) { exit; }

add_action('init', function (): void {
    register_post_status('wc-zvij-shipped', ['label' => 'Poslano', 'public' => false, 'show_in_admin_all_list' => true, 'show_in_admin_status_list' => true, 'label_count' => _n_noop('Poslano <span class="count">(%s)</span>', 'Poslano <span class="count">(%s)</span>')]);
});
add_filter('wc_order_statuses', function (array $statuses): array {
    $statuses['wc-zvij-shipped'] = 'Poslano';
    return $statuses;
});

function zvij_dispatch_paid(WC_Order $order): bool {
    return (bool) $order->get_date_paid();
}
add_filter('woocommerce_order_is_paid', function (bool $paid, WC_Order $order): bool {
    return $order->has_status('zvij-shipped') ? zvij_dispatch_paid($order) : $paid;
}, 10, 2);
function zvij_dispatch_tracking_url(string $number): string {
    return 'https://moja.posta.si/tracking/' . rawurlencode($number);
}
function zvij_dispatch_tracking_required(WC_Order $order): bool {
    return stripos(remove_accents($order->get_shipping_method()), 'sleden') !== false;
}

/** Dispatch email content: optional own intro and note, then tracking, payment and invoice. */
function zvij_dispatch_email(WC_Order $order, string $number, array $custom = []): array {
    $intro = trim((string) ($custom['intro'] ?? ''));
    $note = trim((string) ($custom['note'] ?? ''));
    $message = "Živjo,\n\n" . ($intro !== '' ? $intro : 'tvoje naročilo #' . $order->get_order_number() . ' smo poslali.') . "\n\n" . ($note !== '' ? $note . "\n\n" : '');
    if ($number !== '') {
        $message .= 'Številka pošiljke: ' . $number . "\nSledi pošiljki: " . zvij_dispatch_tracking_url($number) . "\n\n";
    }
    $message .= zvij_dispatch_paid($order) ? "Naročilo je že plačano. Ne plačuj ponovno.\n" : ($order->get_payment_method() === 'cod' ? "Plačilo po povzetju ob prevzemu: " . html_entity_decode(wp_strip_all_tags($order->get_formatted_order_total()), ENT_QUOTES, 'UTF-8') . "\n" : "Stanje plačila preveri v svojem naročilu.\n");
    $message = (string) apply_filters('zvij_dispatch_message', $message, $order, $custom);
    return ['subject' => 'Tvoje naročilo #' . $order->get_order_number() . ' je poslano', 'heading' => 'Naročilo #' . $order->get_order_number() . ' je na poti', 'text' => $message . "\nHvala, Zvij.si"];
}
/** Same header, footer and styles as the other WooCommerce emails the customer receives. */
function zvij_send_customer_email(WC_Order $order, array $email): bool {
    $mailer = WC()->mailer();
    $html = $mailer->wrap_message($email['heading'], make_clickable(esc_html($email['text'])));
    return (bool) $mailer->send($order->get_billing_email(), $email['subject'], $html, "Content-Type: text/html; charset=UTF-8\r\n");
}
function zvij_dispatch_notify(WC_Order $order, array $custom = []): bool {
    $ok = zvij_send_customer_email($order, zvij_dispatch_email($order, (string) $order->get_meta('_zvij_tracking_number'), $custom));
    $order->update_meta_data('_zvij_dispatch_email', $ok ? 'sent' : 'failed');
    $order->save();
    return $ok;
}

function zvij_dispatch_update(WC_Order $order, string $action, string $tracking = '', array $custom = []) {
    if ($order->has_status(['cancelled', 'failed', 'refunded', 'checkout-draft'])) { return new WP_Error('invalid_status', 'Tega naročila ni mogoče odpremiti.'); }
    if ($action === 'paid') {
        if (! zvij_dispatch_paid($order)) {
            $shipped = (bool) $order->get_meta('_zvij_dispatched_at');
            // Payment is an internal fact; the customer gets the invoice with the dispatch email.
            add_filter('woocommerce_email_enabled_customer_processing_order', '__return_false', 99);
            try { $order->payment_complete(); } finally { remove_filter('woocommerce_email_enabled_customer_processing_order', '__return_false', 99); }
            if (! $order->get_date_paid()) { $order->set_date_paid(time()); $order->save(); }
            zvij_invoice_number($order);
            $order->add_order_note('Plačilo ročno potrjeno v odpremnem pregledu, kupec ni obveščen. Uporabnik #' . get_current_user_id());
            if ($shipped) { $order->update_status('zvij-shipped'); }
        }
        return true;
    }
    if ($action === 'resend') {
        if (! $order->get_meta('_zvij_dispatched_at')) { return new WP_Error('not_sent', 'Naročilo še ni poslano.'); }
        return zvij_dispatch_notify($order) ? true : new WP_Error('email_failed', 'Obvestila ni bilo mogoče poslati.');
    }
    if ($action !== 'ship') { return new WP_Error('invalid_action', 'Neveljavna akcija.'); }
    if ($order->get_meta('_zvij_dispatched_at')) { return true; }
    $tracking = strtoupper(preg_replace('/\s+/', '', $tracking));
    if (($tracking !== '' && ! preg_match('/^[A-Z0-9]{8,40}$/', $tracking)) || ($tracking === '' && zvij_dispatch_tracking_required($order))) {
        return new WP_Error('tracking_required', 'Vnesi veljavno številko pošiljke za izbrano dostavo s sledenjem.');
    }
    $order->update_meta_data('_zvij_tracking_number', $tracking);
    $order->update_meta_data('_zvij_dispatched_at', current_time('mysql'));
    $order->save();
    $order->update_status('zvij-shipped', 'Oddano na pošto. Uporabnik #' . get_current_user_id());
    return zvij_dispatch_notify($order, $custom) ? true : new WP_Error('email_failed', 'Naročilo je označeno kot poslano, obvestila pa ni bilo mogoče poslati. Poskusi ponovno.');
}

add_action('admin_post_zvij_dispatch', function (): void {
    if (! current_user_can('manage_woocommerce')) { wp_die('Dostop ni dovoljen.', '', ['response' => 403]); }
    $id = absint($_POST['order_id'] ?? 0);
    check_admin_referer('zvij_dispatch_' . $id);
    $order = wc_get_order($id);
    if (! $order instanceof WC_Order) { wp_die('Naročilo ne obstaja.'); }
    $lock = 'zvij_dispatch_lock_' . $id;
    if (! add_option($lock, time(), '', false)) { wp_die('Naročilo se že obdeluje. Osveži pregled.'); }
    try {
        $result = zvij_dispatch_update($order, sanitize_key($_POST['operation'] ?? ''), sanitize_text_field(wp_unslash($_POST['tracking'] ?? '')));
    } finally { delete_option($lock); }
    if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message()) . '<p><a href="' . esc_url(zvij_order_workspace_url($id)) . '">Nazaj na naročilo</a></p>'); }
    wp_safe_redirect(zvij_order_workspace_url($id));
    exit;
});

function zvij_dispatch_workspace(WC_Order $order): void {
    $paid = zvij_dispatch_paid($order);
    $sent = (string) $order->get_meta('_zvij_dispatched_at');
    echo '<h2>' . ($paid ? 'Plačano' : 'Plačilo še ni potrjeno') . ' · ' . ($sent ? 'Poslano' : 'Za odpremo') . '</h2>';
    zvij_order_document_addresses($order, true);
    echo '<p>Dostava: <strong>' . esc_html($order->get_shipping_method()) . '</strong></p>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="zvij_dispatch"><input type="hidden" name="order_id" value="' . esc_attr((string) $order->get_id()) . '">';
    wp_nonce_field('zvij_dispatch_' . $order->get_id());
    if (! $paid) { echo '<p><button class="button" name="operation" value="paid" formnovalidate>Potrdi prejeto plačilo</button></p>'; }
    if (! $sent) {
        echo '<p><label>Številka pošiljke' . (zvij_dispatch_tracking_required($order) ? ' (obvezno)' : ' (če ima sledenje)') . '<br><input name="tracking" autocomplete="off" style="max-width:100%;width:320px"' . (zvij_dispatch_tracking_required($order) ? ' required' : '') . '></label></p><button class="button button-primary" name="operation" value="ship">Potrdi: poslano in obvesti kupca</button>';
    } else {
        echo '<p>Poslano: ' . esc_html($sent) . '</p>';
        $number = (string) $order->get_meta('_zvij_tracking_number');
        if ($number !== '') { echo '<p><a target="_blank" rel="noopener" href="' . esc_url(zvij_dispatch_tracking_url($number)) . '">Sledi pošiljki: ' . esc_html($number) . '</a></p>'; }
        echo '<p>' . ($order->get_meta('_zvij_dispatch_email') === 'sent' ? 'Obvestilo predano e-poštnemu sistemu.' : 'Obvestilo ni bilo poslano.') . '</p><button class="button" name="operation" value="resend">Ponovno pošlji obvestilo</button>';
    }
    echo '</form>';
}

add_action('woocommerce_order_details_after_order_table', function ($order): void {
    if (! $order instanceof WC_Order || ! is_user_logged_in() || ((int) $order->get_customer_id() !== get_current_user_id() && ! current_user_can('manage_woocommerce'))) { return; }
    $number = (string) $order->get_meta('_zvij_tracking_number');
    if ($number !== '') { echo '<p><a href="' . esc_url(zvij_dispatch_tracking_url($number)) . '">Sledi pošiljki: ' . esc_html($number) . '</a></p>'; }
});
