<?php
if (PHP_SAPI !== 'cli') { exit; }
require '/var/www/html/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/user.php';
if (wp_get_environment_type() !== 'development') { exit('Development only'); }
add_filter('pre_wp_mail', '__return_true');
$email = 'smoke-test-' . wp_generate_password(10, false) . '@example.invalid';
$id = wc_create_new_customer($email);
if (is_wp_error($id)) { exit('FAIL test setup'); }
try {
    wp_set_current_user($id);
    ob_start(); zvij_smoke_egg_quest(); $quest = ob_get_clean();
    if (strpos($quest, '?/?') === false) { throw new Exception('Quest must start hidden'); }
    echo "PASS undiscovered quest shows ?/?\n";
    zvij_smoke_egg_award($id);
    ob_start(); zvij_smoke_egg_quest(); $quest = ob_get_clean();
    if (strpos($quest, '1/10') === false) { throw new Exception('First-click progress missing'); }
    echo "PASS first click reveals 1/10\n";
    $awards = 1;
    for ($n = 0; $n < 25; $n++) { $awards += zvij_smoke_egg_award($id) ? 1 : 0; }
    if ($awards !== 10 || zvij_credit_balance($email) !== 10) { throw new Exception('Reward cap failed'); }
    echo "PASS exactly ten rewards after 25 clicks\n";
    ob_start(); zvij_smoke_egg_quest(); $quest = ob_get_clean();
    if (strpos($quest, '10/10') === false) { throw new Exception('Completion missing'); }
    echo "PASS completed quest shows 10/10\n";
    if (zvij_smoke_egg_award(0)) { throw new Exception('Guest rewarded'); }
    echo "PASS guests cannot earn\n";
    wp_set_current_user($id);
    if (zvij_smoke_egg_award($id)) { throw new Exception('Reward reset'); }
    echo "PASS cap persists independently of browser state\n";
    global $wpdb;
    $indexes = $wpdb->get_results('SHOW INDEX FROM ' . zvij_credit_table(), ARRAY_A);
    $unique = array_filter($indexes, static fn ($index) => $index['Column_name'] === 'event_key' && (int) $index['Non_unique'] === 0);
    if (! $unique) { throw new Exception('Missing concurrency protection'); }
    echo "PASS database unique constraint protects concurrent reward slots\n";
} finally {
    global $wpdb;
    $wpdb->delete(zvij_credit_table(), ['member_email' => $email]);
    $wpdb->delete(zvij_membership_table(), ['email' => $email]);
    wp_delete_user($id);
}
