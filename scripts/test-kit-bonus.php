<?php
/** Development-only integration checks, rolled back without retained test data. */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
if (wp_get_environment_type() !== 'development') { throw new RuntimeException('Development only'); }
add_filter('pre_wp_mail', static fn () => true);
add_filter('pre_http_request', static fn () => new WP_Error('test-no-network', 'No external requests'));
function kit_check(bool $condition, string $label): void {
    if (! $condition) { throw new RuntimeException($label); }
    echo "PASS $label\n";
}
/** Izdelek ali variacija po slugu (po združitvi v variacije 16. 10. 2026 imajo variacije stare sluge). */
function zvij_test_product(string $slug): WC_Product {
    $post = get_page_by_path($slug, OBJECT, 'product') ?: (get_posts(['name' => $slug, 'post_type' => 'product_variation', 'post_status' => ['publish', 'private'], 'numberposts' => 1])[0] ?? null);
    $product = $post ? wc_get_product($post->ID) : null;
    if (! $product) { throw new RuntimeException("Ni izdelka $slug"); }
    return $product;
}
$groups = [[1, 2, 3], [2, 3, 4]];
$line = static fn ($id, $reward = 100, $quantity = 1) => compact('id', 'reward', 'quantity');
kit_check(zvij_kit_bonus_calculate([$line(1), $line(2)], $groups) === 0, 'Two products do not qualify');
kit_check(zvij_kit_bonus_calculate([$line(1, 100, 3)], $groups) === 0, 'Three copies do not qualify');
kit_check(zvij_kit_bonus_calculate([$line(1), $line(1), $line(2)], $groups) === 0, 'Split lines do not bypass distinct count');
kit_check(zvij_kit_bonus_calculate([$line(1), $line(2), $line(3)], $groups) === 15, 'Three products earn 5 percent extra');
kit_check(zvij_kit_bonus_calculate([$line(1), $line(2), $line(3), $line(99, 1000)], $groups) === 15, 'Unrelated products excluded');
kit_check(zvij_kit_bonus_calculate([$line(1), $line(2), $line(3), $line(4)], $groups) === 20, 'Overlapping kits never double count');
kit_check(zvij_kit_bonus_calculate([$line(1, 100, 2), $line(2), $line(3)], $groups) === 20, 'Quantities included after distinct threshold');
kit_check(zvij_kit_bonus_calculate([$line(1, 1), $line(2, 1), $line(3, 1)], $groups) === 0, 'Fractional crystals round down');
kit_check(zvij_kit_bonus_calculate([$line(1), $line(2), $line(3, 100, 0)], $groups) === 0, 'Removed products lose eligibility');

global $wpdb;
$wpdb->query('START TRANSACTION');
try {
    $user_id = wc_create_new_customer('kit-test-' . bin2hex(random_bytes(6)) . '@example.invalid', '', wp_generate_password(24));
    if (is_wp_error($user_id)) { throw new RuntimeException($user_id->get_error_message()); }
    $email = get_userdata($user_id)->user_email;
    $order = wc_create_order(['customer_id' => $user_id]);
    $order->set_billing_email($email);
    $base = 0;
    foreach (['clipper-black', 'zvij-mini-grinder-5-cm', 'dubi-42-aktivnih-ogljikovih-filtrov'] as $slug) {
        $product = zvij_test_product($slug);
        $base += zvij_credit_product_kristali($product);
        $order->add_product($product, 1);
    }
    kit_check(zvij_kit_bonus_order($order) === 0, 'Existing orders keep original terms');
    do_action('woocommerce_checkout_create_order', $order, []);
    $bonus = intdiv($base * 5, 100);
    kit_check($bonus > 0 && zvij_kit_bonus_order($order) === $bonus, 'Real catalog products qualify');
    kit_check(zvij_credit_order_earnable($order) === $base + $bonus, 'Earnable total includes bonus');
    $order->save();
    zvij_credit_earn_on_paid($order->get_id(), 'pending', 'pending', $order);
    kit_check(zvij_credit_balance($email) === 0, 'Unpaid order earns nothing');
    zvij_credit_earn_on_paid($order->get_id(), 'pending', 'processing', $order);
    kit_check(zvij_credit_balance($email) === $base + $bonus, 'Paid member receives base plus bonus');
    zvij_credit_earn_on_paid($order->get_id(), 'processing', 'completed', $order);
    kit_check(zvij_credit_balance($email) === $base + $bonus, 'Repeated paid hook cannot duplicate reward');
    kit_check((int) $order->get_meta('_zvij_kit_bonus_kristali') === $bonus, 'Bonus breakdown saved for admin');
    zvij_credit_reverse_on_cancel($order->get_id(), 'processing', 'refunded', $order);
    kit_check(zvij_credit_balance($email) === 0, 'Refund reverses base and bonus');
    zvij_credit_reverse_on_cancel($order->get_id(), 'refunded', 'cancelled', $order);
    kit_check(zvij_credit_balance($email) === 0, 'Repeated cancellation cannot double reverse');
} finally {
    $wpdb->query('ROLLBACK');
    wp_cache_flush();
    echo "Test data rolled back\n";
}
