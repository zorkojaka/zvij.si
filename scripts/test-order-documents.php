<?php
require_once '/var/www/html/wp-load.php';
$failed = 0;
function check_doc(bool $ok, string $label): void {
    global $failed;
    echo ($ok ? 'PASS ' : 'FAIL ') . $label . "\n";
    if (! $ok) { $failed++; }
}
$order = new WC_Order();
$email = (object) ['id' => 'new_order'];
foreach ([false, true] as $plain) {
    ob_start(); zvij_order_admin_email_links($order, true, $plain, $email); $output = ob_get_clean();
    check_doc(strpos($output, 'zvij-order-documents') !== false, 'Admin email workspace link ' . (int) $plain);
    check_doc(strpos($output, '_wpnonce') === false, 'Email link does not expire with a nonce');
}
ob_start(); zvij_order_admin_email_links($order, false, false, $email); $output = ob_get_clean();
check_doc($output === '', 'No admin links in customer email');
ob_start(); zvij_order_admin_email_links($order, true, false, (object) ['id' => 'cancelled_order']); $output = ob_get_clean();
check_doc($output === '', 'No links in unrelated admin emails');
$admins = get_users(['role' => 'administrator', 'number' => 1]);
wp_set_current_user($admins[0]->ID);
$orders = wc_get_orders(['limit' => 1]);
if ($orders) {
    $existing = $orders[0];
    $before = $existing->get_meta(ZVIJ_INVOICE_NUMBER_META);
    foreach (['', 'label', 'invoice', 'upn', 'invalid'] as $document) {
        $_GET = ['order_id' => $existing->get_id(), 'document' => $document];
        ob_start(); zvij_order_workspace(); $html = ob_get_clean();
        check_doc(strpos($html, 'zv-order-document') !== false, 'Document renders: ' . $document);
        if ($document === 'label') { check_doc(strpos($html, 'Prejemnik') !== false && strpos($html, 'Pošiljatelj') !== false, 'Both label addresses'); }
    }
    check_doc(wc_get_order($existing->get_id())->get_meta(ZVIJ_INVOICE_NUMBER_META) === $before, 'Printing does not assign invoice numbers');
} else { echo "SKIP no existing orders for render tests\n"; }
exit($failed ? 1 : 0);
