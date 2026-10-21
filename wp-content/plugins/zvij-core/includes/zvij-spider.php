<?php
/** One shop adapter to Spider. Telegram and chat identities are managed by Spider. */
if (! defined('ABSPATH')) { exit; }
function zvij_spider_config(): array { return (array) get_option('zvij_spider_connection', []); }
add_action('woocommerce_order_status_zvij-shipped', function ($id): void {
    $order = wc_get_order($id);
    if ($order instanceof WC_Order) { zvij_invoice_number($order); }
}, 15);
add_filter('zvij_dispatch_message', function (string $message, WC_Order $order): string {
    $number = (string) $order->get_meta(ZVIJ_INVOICE_NUMBER_META);
    // A status hook may have saved the number using another order instance.
    if ($number === '') { $fresh = wc_get_order($order->get_id()); $number = $fresh ? (string) $fresh->get_meta(ZVIJ_INVOICE_NUMBER_META) : ''; }
    if ($number === '') { return $message; }
    $seller = zvij_invoice_seller();
    $message .= "\nRAČUN " . $number . "\nDatum: " . zvij_invoice_date($order) . "\n" . $seller['name'] . "\n" . implode("\n", $seller['lines']) . "\n\nKupec:\n" . html_entity_decode(wp_strip_all_tags(str_replace(['<br/>','<br />','<br>'], "\n", $order->get_formatted_billing_address())), ENT_QUOTES, 'UTF-8') . "\n\n";
    foreach ($order->get_items() as $item) { $message .= $item->get_quantity() . ' × ' . $item->get_name() . ': ' . html_entity_decode(wp_strip_all_tags($order->get_formatted_line_subtotal($item)), ENT_QUOTES, 'UTF-8') . "\n"; }
    foreach ($order->get_order_item_totals() as $total) { $message .= wp_strip_all_tags($total['label']) . ' ' . html_entity_decode(wp_strip_all_tags($total['value']), ENT_QUOTES, 'UTF-8') . "\n"; }
    return $message . zvij_invoice_vat_note() . "\n";
}, 10, 2);
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
        $body = wp_json_encode([
            'eventId' => 'order-created-' . $id, 'orderId' => (string) $id, 'number' => $order->get_order_number(),
            'status' => $order->get_status(), 'total' => html_entity_decode(wp_strip_all_tags($order->get_formatted_order_total()), ENT_QUOTES, 'UTF-8'),
            'payment' => $order->get_payment_method_title(),
            'address' => html_entity_decode(wp_strip_all_tags(str_replace(['<br/>', '<br />', '<br>'], "\n", $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address())), ENT_QUOTES, 'UTF-8'),
            'items' => array_values(array_map(static fn ($item) => ['name' => $item->get_name(), 'quantity' => (int) $item->get_quantity()], $order->get_items())),
            'workspace' => zvij_order_workspace_url($id),
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
        if (! $order instanceof WC_Order || ! preg_match('/^[a-f0-9]{32}$/', $event) || ! in_array($operation, ['paid', 'ship'], true)) { return new WP_Error('invalid_command', 'Invalid command', ['status' => 400]); }
        $lock = 'zvij_dispatch_lock_' . $id;
        if (! add_option($lock, time(), '', false)) { return new WP_Error('busy', 'Order busy', ['status' => 409]); }
        try {
            $done = (array) $order->get_meta('_zvij_spider_actions');
            if (in_array($event, $done, true)) { return ['ok' => true, 'duplicate' => true]; }
            $result = zvij_dispatch_update($order, $operation, sanitize_text_field($input['tracking'] ?? ''));
            if (is_wp_error($result)) { return new WP_Error('dispatch_failed', $result->get_error_message(), ['status' => 409]); }
            $done[] = $event; $order->update_meta_data('_zvij_spider_actions', array_slice($done, -100)); $order->save();
            $order->add_order_note('Spider: izrecno potrjeno dejanje ' . $operation . ', referenca ' . $event);
            return ['ok' => true, 'status' => $order->get_status()];
        } finally { delete_option($lock); }
    }]);
});
