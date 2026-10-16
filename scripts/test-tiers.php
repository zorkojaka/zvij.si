<?php
/** Stopnje članstva, kristali iz marže in brezplačna poštnina. Samo dev, vse se razveljavi. */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
if (wp_get_environment_type() !== 'development') { throw new RuntimeException('Development only'); }
add_filter('pre_wp_mail', static fn () => true);
add_filter('pre_http_request', static fn () => new WP_Error('test-no-network', 'No external requests'));
function tier_check(bool $condition, string $label): void {
    if (! $condition) { throw new RuntimeException($label); }
    echo "PASS $label\n";
}

global $wpdb;
$wpdb->query('START TRANSACTION');
$enabled_before = get_option('zvij_tiers_enabled');
try {
    $ziggi = wc_get_product(435);     // 2,30 €, nabava 0,96 €
    $plin = wc_get_product(445);      // nabavna cena se v testu začasno odstrani
    $cbd = wc_get_product(74);        // variabilen: 1 g po 8,00 €, nabava 1,50 €
    $legacy_ziggi = zvij_credit_legacy_kristali($ziggi);

    update_option('zvij_tiers_enabled', 'no');
    tier_check(zvij_credit_product_kristali($ziggi) === $legacy_ziggi, 'Izklopljeno: staro pravilo ostane');

    update_option('zvij_tiers_enabled', 'yes');
    update_option('zvij_tiers', [1 => ['share' => 5, 'min' => 0], 2 => ['share' => 15, 'min' => 100], 3 => ['share' => 25, 'min' => 250], 4 => ['share' => 35, 'min' => 500], 5 => ['share' => 50, 'min' => 1000]]);
    wp_set_current_user(0);
    tier_check(zvij_credit_product_kristali($ziggi) === (int) round((2.30 - 0.96) * 5), 'Gost vidi stopnjo 1: 5 % marže');
    delete_post_meta(445, '_zvij_cost');
    $plin = wc_get_product(445);
    tier_check(zvij_credit_product_kristali($plin) === zvij_credit_legacy_kristali($plin), 'Brez nabavne cene: staro pravilo');
    tier_check(zvij_credit_product_kristali($cbd) === (int) round((8.00 - 1.50) * 5), 'Kartica z variacijami: najcenejša variacija (1 g)');
    tier_check(zvij_tiers_kristali_from_margin(1.00, 2.00, 1, 50) === 0, 'Izguba nikoli ne da negativnih kristalov');

    $email = 'tier-test-' . bin2hex(random_bytes(5)) . '@example.invalid';
    tier_check(zvij_tiers_for_email($email)['level'] === 0, 'Nečlan nima stopnje');
    zvij_membership_upsert(['email' => $email, 'source' => 'manual']);
    tier_check(zvij_tiers_for_email($email)['level'] === 1, 'Nov član je na stopnji 1');

    $paid = static function (string $email, float $amount): WC_Order {
        $order = wc_create_order();
        $order->set_billing_email($email);
        $fee = new WC_Order_Item_Fee();
        $fee->set_name('test');
        $fee->set_total($amount);
        $order->add_item($fee);
        $order->calculate_totals(false);
        $order->set_status('processing');
        $order->save();
        return $order;
    };
    $paid($email, 120);
    tier_check(zvij_tiers_for_email($email)['level'] === 2, '120 € porabe: stopnja 2');
    $paid($email, 400);
    $tier = zvij_tiers_for_email($email);
    tier_check($tier['level'] === 4 && $tier['next_level'] === 5 && abs($tier['next_min'] - 1000) < 0.01, '520 € porabe: stopnja 4, naslednja 5 pri 1000 €');

    // Naročilo z dvema Ziggi: kristali iz dejansko plačane postavke po stopnji pred tem naročilom.
    $order = wc_create_order();
    $order->set_billing_email($email);
    $item_id = $order->add_product($ziggi, 2);
    $order->calculate_totals(false);
    $line = (float) $order->get_item($item_id)->get_total();
    $expected = (int) round(max(0, $line - 0.96 * 2) * 35);
    tier_check(zvij_credit_order_earnable($order) === $expected, 'Naročilo: 35 % marže plačane postavke (' . $expected . ' kristalov)');
    foreach ($order->get_items() as $item) {
        $item->set_total($line * 0.8);
    }
    $expected_discount = (int) round(max(0, $line * 0.8 - 0.96 * 2) * 35);
    tier_check(zvij_credit_order_earnable($order) === $expected_discount, 'Popust zmanjša maržo in s tem kristale');

    $wpdb->replace(zvij_tiers_table(), ['email' => $email, 'level' => 5, 'share' => 45, 'free_shipping' => 0, 'note' => 'test', 'updated_at' => current_time('mysql')]);
    $tier = zvij_tiers_for_email($email);
    tier_check($tier['level'] === 5 && abs($tier['share'] - 45) < 0.01 && $tier['manual'], 'Ročni VIP z lastnim deležem 45 %');

    $wpdb->replace(zvij_tiers_table(), ['email' => $email, 'level' => 0, 'share' => null, 'free_shipping' => 1, 'note' => '', 'updated_at' => current_time('mysql')]);
    tier_check(zvij_tiers_for_email($email)['level'] === 4, 'Samo poštnina: stopnja spet po porabi');

    $rate = static fn ($id, $method, $label, $cost) => new WC_Shipping_Rate($id, $label, $cost, [], $method);
    $rates = ['flat_rate:1' => $rate('flat_rate:1', 'flat_rate', 'Navadna poštnina', 2.90), 'flat_rate:3' => $rate('flat_rate:3', 'flat_rate', 'S sledenjem', 3.90)];
    $out = apply_filters('woocommerce_package_rates', $rates, []);
    tier_check((float) $out['flat_rate:1']->get_cost() === 2.90, 'Gost ne dobi brezplačne poštnine');

    $user_id = wp_insert_user(['user_login' => 'tiertest' . wp_rand(), 'user_email' => $email, 'user_pass' => wp_generate_password()]);
    wp_set_current_user($user_id);
    $rates = ['flat_rate:1' => $rate('flat_rate:1', 'flat_rate', 'Navadna poštnina', 2.90), 'flat_rate:3' => $rate('flat_rate:3', 'flat_rate', 'S sledenjem', 3.90)];
    $out = apply_filters('woocommerce_package_rates', $rates, []);
    tier_check((float) $out['flat_rate:1']->get_cost() === 0.0 && (float) $out['flat_rate:3']->get_cost() === 3.90, 'Označen prijavljen član: navadna brezplačna, nadgradnja plačljiva');
} finally {
    $wpdb->query('ROLLBACK');
    update_option('zvij_tiers_enabled', $enabled_before);
    wp_cache_flush();
    echo "Testni podatki razveljavljeni\n";
}
