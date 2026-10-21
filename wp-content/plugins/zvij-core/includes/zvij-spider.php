<?php
/** One shop adapter to Spider. Telegram and chat identities are managed by Spider. */
if (! defined('ABSPATH')) { exit; }
function zvij_spider_config(): array { return (array) get_option('zvij_spider_connection', []); }
add_action('woocommerce_order_status_zvij-shipped', function ($id): void {
    $order = wc_get_order($id);
    if ($order instanceof WC_Order) { zvij_invoice_number($order); }
}, 15);
add_filter('zvij_dispatch_message', function (string $message, WC_Order $order, array $custom = []): string {
    $number = (string) $order->get_meta(ZVIJ_INVOICE_NUMBER_META);
    // A status hook may have saved the number using another order instance.
    if ($number === '') { $fresh = wc_get_order($order->get_id()); $number = $fresh ? (string) $fresh->get_meta(ZVIJ_INVOICE_NUMBER_META) : ''; }
    // Dispatch issues the invoice; a preview must not use up a number.
    if ($number === '' && ! empty($custom['preview'])) { $number = '(številka bo dodeljena ob pošiljanju)'; }
    if ($number === '') { return $message; }
    $seller = zvij_invoice_seller();
    $message .= "\nRAČUN " . $number . "\nDatum: " . zvij_invoice_date($order) . "\n" . $seller['name'] . "\n" . implode("\n", $seller['lines']) . "\n\nKupec:\n" . html_entity_decode(wp_strip_all_tags(str_replace(['<br/>','<br />','<br>'], "\n", $order->get_formatted_billing_address())), ENT_QUOTES, 'UTF-8') . "\n\n";
    foreach ($order->get_items() as $item) { $message .= $item->get_quantity() . ' × ' . $item->get_name() . ': ' . html_entity_decode(wp_strip_all_tags($order->get_formatted_line_subtotal($item)), ENT_QUOTES, 'UTF-8') . "\n"; }
    foreach ($order->get_order_item_totals() as $total) { $message .= wp_strip_all_tags($total['label']) . ' ' . html_entity_decode(wp_strip_all_tags($total['value']), ENT_QUOTES, 'UTF-8') . "\n"; }
    return $message . zvij_invoice_vat_note() . "\n";
}, 10, 3);
/** Own message to the customer, optionally with the already issued invoice. */
function zvij_spider_message_email(WC_Order $order, string $text, bool $invoice): array {
    $message = "Živjo,\n\n" . trim($text) . "\n";
    if ($invoice) { $message = (string) apply_filters('zvij_dispatch_message', $message . "\n", $order, []); }
    return ['subject' => 'Glede tvojega naročila #' . $order->get_order_number(), 'heading' => 'Naročilo #' . $order->get_order_number(), 'text' => $message . "\nHvala, Zvij.si"];
}
function zvij_spider_text(string $html): string {
    return trim(html_entity_decode(wp_strip_all_tags(str_replace(['<br/>', '<br />', '<br>'], "\n", $html)), ENT_QUOTES, 'UTF-8'));
}
/** Current payment and dispatch facts, so Spider can redraw the Telegram order message. */
function zvij_spider_state(WC_Order $order): array {
    return ['status' => wc_get_order_status_name($order->get_status()), 'paid' => zvij_dispatch_paid($order), 'shipped' => (bool) $order->get_meta('_zvij_dispatched_at'),
        'tracking' => (string) $order->get_meta('_zvij_tracking_number'), 'email' => (string) $order->get_meta('_zvij_dispatch_email'), 'invoice' => (string) $order->get_meta(ZVIJ_INVOICE_NUMBER_META)];
}
function zvij_spider_queue($id): void {
    if (! zvij_spider_config() || ! $id) { return; }
    if (! wp_next_scheduled('zvij_spider_order_event', [(int) $id])) { wp_schedule_single_event(time() + 5, 'zvij_spider_order_event', [(int) $id]); }
}
add_action('woocommerce_checkout_order_processed', 'zvij_spider_queue', 50);
add_action('woocommerce_store_api_checkout_order_processed', function ($order): void { if ($order instanceof WC_Order) { zvij_spider_queue($order->get_id()); } }, 50);
add_action('zvij_spider_order_event', function (int $id): void {
    $config = zvij_spider_config(); $order = wc_get_order($id);
    if (! $config || ! $order instanceof WC_Order || $order->get_meta('_zvij_spider_notified')) { return; }
    $body = (string) $order->get_meta('_zvij_spider_event_body');
    if ($body === '') {
        $seller = zvij_invoice_seller();
        $body = wp_json_encode([
            'eventId' => 'order-created-' . $id, 'orderId' => (string) $id, 'number' => $order->get_order_number(),
            'status' => $order->get_status(), 'total' => zvij_spider_text($order->get_formatted_order_total()),
            'payment' => $order->get_payment_method_title(),
            'address' => zvij_spider_text($order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address()),
            'items' => array_values(array_map(static fn ($item) => ['name' => $item->get_name(), 'quantity' => (int) $item->get_quantity(), 'total' => zvij_spider_text($order->get_formatted_line_subtotal($item))], $order->get_items())),
            'workspace' => zvij_order_workspace_url($id),
            'customer' => trim($order->get_formatted_billing_full_name() . ($order->get_billing_company() ? ', ' . $order->get_billing_company() : '')),
            'phone' => $order->get_billing_phone(), 'shipping' => $order->get_shipping_method(),
            'trackingRequired' => zvij_dispatch_tracking_required($order), 'cod' => $order->get_payment_method() === 'cod',
            // Same sender lines as the printed address label in zvij-order-documents.php.
            'sender' => ['name' => $seller['name'], 'lines' => array_values(array_filter([get_option('woocommerce_store_address', ''), get_option('woocommerce_store_address_2', ''), trim(get_option('woocommerce_store_postcode', '') . ' ' . get_option('woocommerce_store_city', ''))]))],
            'state' => zvij_spider_state($order),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $order->update_meta_data('_zvij_spider_event_body', $body); $order->save();
    }
    $at = (string) round(microtime(true) * 1000);
    $result = wp_remote_post($config['endpoint'], ['timeout' => 15, 'redirection' => 0, 'headers' => ['Content-Type' => 'application/json', 'X-Spider-Time' => $at, 'X-Spider-Signature' => hash_hmac('sha256', $at . '.' . $body, $config['secret'])], 'body' => $body]);
    if (! is_wp_error($result) && wp_remote_retrieve_response_code($result) === 200) {
        $order->update_meta_data('_zvij_spider_notified', current_time('mysql'));
    } else {
        $tries = (int) $order->get_meta('_zvij_spider_attempts') + 1; $order->update_meta_data('_zvij_spider_attempts', $tries);
        if ($tries < 8) { wp_schedule_single_event(time() + min(3600, 30 * (2 ** $tries)), 'zvij_spider_order_event', [$id]); }
        else { $order->add_order_note('Spider naročila ni sprejel po 8 poskusih. Preveri povezavo.'); }
    }
    $order->save();
});
function zvij_spider_verify_command(WP_REST_Request $request): bool {
    $config = zvij_spider_config(); if (empty($config['secret'])) { return false; }
    $at = $request->get_header('X-Spider-Time');
    if (! ctype_digit($at) || abs((float) $at - microtime(true) * 1000) > 300000) { return false; }
    return hash_equals(hash_hmac('sha256', $at . '.' . $request->get_body(), $config['secret']), $request->get_header('X-Spider-Signature'));
}
add_action('rest_api_init', function (): void {
    register_rest_route('zvij/v1', '/spider-command', ['methods' => 'POST', 'permission_callback' => 'zvij_spider_verify_command', 'callback' => function (WP_REST_Request $request) {
        $input = $request->get_json_params(); $id = absint($input['orderId'] ?? 0); $event = sanitize_key($input['eventId'] ?? '');
        $operation = $input['operation'] ?? ''; $order = wc_get_order($id);
        if (! $order instanceof WC_Order || ! preg_match('/^[a-f0-9]{32}$/', $event) || ! in_array($operation, ['paid', 'ship', 'email', 'preview'], true)) { return new WP_Error('invalid_command', 'Invalid command', ['status' => 400]); }
        $field = static fn (string $key): string => sanitize_textarea_field(mb_substr((string) ($input[$key] ?? ''), 0, 3000));
        $custom = ['intro' => $field('intro'), 'note' => $field('note')];
        $tracking = strtoupper(preg_replace('/\s+/', '', sanitize_text_field($input['tracking'] ?? '')));
        $invoice = ! empty($input['invoice']) && (string) $order->get_meta(ZVIJ_INVOICE_NUMBER_META) !== '';
        if ($operation === 'preview') {
            $email = ($input['kind'] ?? '') === 'message' ? zvij_spider_message_email($order, $field('text'), $invoice) : zvij_dispatch_email($order, $tracking, $custom + ['preview' => true]);
            return ['ok' => true, 'preview' => ['subject' => $email['subject'], 'text' => $email['text'], 'to' => $order->get_billing_email()], 'state' => zvij_spider_state($order)];
        }
        if ($operation === 'email' && trim($field('text')) === '') { return new WP_Error('invalid_command', 'Invalid command', ['status' => 400]); }
        $lock = 'zvij_dispatch_lock_' . $id;
        if (! add_option($lock, time(), '', false)) { return new WP_Error('busy', 'Order busy', ['status' => 409]); }
        try {
            $done = (array) $order->get_meta('_zvij_spider_actions');
            if (in_array($event, $done, true)) { return ['ok' => true, 'duplicate' => true, 'state' => zvij_spider_state($order)]; }
            $result = $operation === 'email'
                ? (zvij_send_customer_email($order, zvij_spider_message_email($order, $field('text'), $invoice)) ? true : new WP_Error('email_failed', 'Sporočila ni bilo mogoče poslati. Poskusi ponovno.'))
                : zvij_dispatch_update($order, $operation, $tracking, $custom);
            if (is_wp_error($result)) { return new WP_Error('dispatch_failed', $result->get_error_message(), ['status' => 409, 'state' => zvij_spider_state(wc_get_order($id))]); }
            $done[] = $event; $order->update_meta_data('_zvij_spider_actions', array_slice($done, -100)); $order->save();
            $extra = $operation === 'email' ? $field('text') : trim($custom['intro'] . "\n" . $custom['note']);
            $order->add_order_note('Spider: izrecno potrjeno dejanje ' . $operation . ', referenca ' . $event . ($extra !== '' ? "\nBesedilo za kupca:\n" . $extra : ''));
            return ['ok' => true, 'status' => $order->get_status(), 'state' => zvij_spider_state(wc_get_order($id))];
        } finally { delete_option($lock); }
    }]);
});
