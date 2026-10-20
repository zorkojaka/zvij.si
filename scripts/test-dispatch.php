<?php
if (PHP_SAPI !== 'cli') { exit; }
require '/var/www/html/wp-load.php';
if (wp_get_environment_type() !== 'development') { exit('Development only'); }
$messages = [];
add_filter('pre_wp_mail', function ($value, $args) use (&$messages) { $messages[] = $args; return true; }, 10, 2);
function dispatch_assert(bool $condition, string $message): void { if (! $condition) { throw new Exception($message); } echo 'PASS ' . $message . "\n"; }
$order = wc_create_order();
$seq = get_option(ZVIJ_INVOICE_NEXT_SEQ_OPTION);
$year = get_option(ZVIJ_INVOICE_SEQ_YEAR_OPTION);
try {
    $order->set_billing_email('dispatch-test@example.invalid');
    $order->set_payment_method('cod');
    $order->set_total(10);
    $shipping = new WC_Order_Item_Shipping(); $shipping->set_method_title('S sledenjem'); $order->add_item($shipping); $order->save();
    dispatch_assert(is_wp_error(zvij_dispatch_update($order, 'ship')), 'Tracked shipping requires number');
    dispatch_assert(is_wp_error(zvij_dispatch_update($order, 'ship', '<bad>')), 'Invalid tracking rejected');
    dispatch_assert(zvij_dispatch_update($order, 'ship', 'rr 123456789 si') === true, 'Dispatch saves and notifies');
    dispatch_assert(! zvij_dispatch_paid($order) && ! $order->is_paid(), 'COD dispatch does not mark paid');
    dispatch_assert($order->get_meta('_zvij_tracking_number') === 'RR123456789SI', 'Tracking normalized');
    dispatch_assert(strpos(end($messages)['message'], '/tracking/RR123456789SI') !== false, 'Customer email deep link');
    $count = count($messages); zvij_dispatch_update($order, 'ship', 'RR123456789SI');
    dispatch_assert(count($messages) === $count, 'Repeated dispatch does not duplicate email');
    dispatch_assert(zvij_dispatch_update($order, 'paid') === true && zvij_dispatch_paid($order), 'Explicit payment confirmation');
    dispatch_assert($order->has_status('zvij-shipped') && $order->is_paid(), 'Paid dispatched order stays shipped');
    $number = $order->get_meta(ZVIJ_INVOICE_NUMBER_META); zvij_dispatch_update($order, 'paid');
    dispatch_assert($number === $order->get_meta(ZVIJ_INVOICE_NUMBER_META), 'Repeated payment keeps invoice number');
} finally {
    $order->delete(true);
    update_option(ZVIJ_INVOICE_NEXT_SEQ_OPTION, $seq);
    update_option(ZVIJ_INVOICE_SEQ_YEAR_OPTION, $year);
}
