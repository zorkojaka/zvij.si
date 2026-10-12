<?php
/** Run in dev: docker exec zvij-dev-wordpress php /var/www/html/scripts/test-invitations.php */
if (PHP_SAPI !== 'cli') {
    exit(1);
}
$_SERVER['SERVER_NAME'] = 'dev.inteligent.si';
$_SERVER['HTTP_HOST'] = 'dev.inteligent.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
if (wp_get_environment_type() !== 'development' || ! function_exists('zvij_invitation_register')) {
    throw new RuntimeException('This integration test only runs on development.');
}
add_filter('pre_wp_mail', static fn () => true);
add_filter('pre_http_request', static fn () => new WP_Error('test-no-network', 'Tests do not send external requests.'), 10, 3);
global $wpdb;
$passed = 0;
function invite_check(bool $condition, string $label): void {
    global $passed, $wpdb;
    if (! $condition || $wpdb->last_error !== '') {
        throw new RuntimeException($label . ': ' . $wpdb->last_error);
    }
    $passed++;
    echo 'PASS ' . $label . PHP_EOL;
}
function invite_customer(string $label): int {
    $id = wc_create_new_customer('invite-test-' . $label . '-' . bin2hex(random_bytes(4)) . '@example.invalid', '', wp_generate_password(24));
    if (is_wp_error($id)) {
        throw new RuntimeException($id->get_error_message());
    }
    return $id;
}
function invite_order(int $user_id, int $crystals, bool $credit = true): WC_Order {
    $email = get_userdata($user_id)->user_email;
    $order = wc_create_order(['customer_id' => $user_id]);
    $order->set_billing_email($email);
    $order->set_status('pending');
    $order->update_meta_data('_zvij_credit_earned', (string) $crystals);
    $order->save();
    if ($credit) {
        zvij_credit_add($email, $crystals, 'earn', $order->get_id(), 'Invitation integration test');
    }
    return $order;
}

$tracked_tables = [zvij_membership_table(), zvij_credit_table(), zvij_invitation_table(), zvij_invitation_links_table(), $wpdb->users, $wpdb->posts];
foreach (['wc_orders', 'wc_orders_meta', 'wc_order_addresses', 'wc_order_operational_data'] as $suffix) {
    $candidate = $wpdb->prefix . $suffix;
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $candidate)) === $candidate) {
        $tracked_tables[] = $candidate;
    }
}
$before_counts = [];
foreach ($tracked_tables as $tracked) {
    $before_counts[$tracked] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$tracked}");
}
$wpdb->query('START TRANSACTION');
$exit = 0;
try {
    unset($_COOKIE['zvij_invitation']);
    $owner_id = invite_customer('owner');
    $owner_email = get_userdata($owner_id)->user_email;
    $owner = zvij_membership_find_by_email($owner_email);
    invite_check($owner && $owner['status'] === 'account' && $owner['consent_at'] === null, 'Shop account earns loyalty without marketing consent');
    $token = zvij_invitation_token($owner_email);
    invite_check(strlen($token) === 48 && $token === zvij_invitation_token($owner_email), 'Stable public invitation token');
    invite_check(zvij_invitation_owner($token)['email'] === $owner_email, 'Token resolves its owner');
    invite_check(zvij_invitation_owner('not-a-token') === null, 'Invalid mapping rejected');
    $signed = zvij_invitation_sign($token, 'whatsapp', time());
    invite_check(zvij_invitation_cookie_data($signed)['channel'] === 'whatsapp', 'Signed attribution retains source');
    invite_check(zvij_invitation_cookie_data($signed . 'x') === null, 'Tampered attribution rejected');
    invite_check(zvij_invitation_cookie_data(zvij_invitation_sign($token, 'link', time() - 31 * DAY_IN_SECONDS)) === null, 'Expired attribution rejected');
    invite_check(zvij_invitation_cookie_data(zvij_invitation_sign($token, 'link', time() + 100)) === null, 'Future attribution rejected');
    invite_check(zvij_invitation_channel('injected') === 'link', 'Unknown channel safely normalized');
    $_COOKIE['zvij_invitation'] = $signed;
    $friend_id = invite_customer('friend');
    $friend_email = get_userdata($friend_id)->user_email;
    $table = zvij_invitation_table();
    $link = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE friend_user_id = %d", $friend_id), ARRAY_A);
    invite_check($link && (int) $link['owner_member_id'] === (int) $owner['id'] && $link['channel'] === 'whatsapp', 'New account binds to inviter and source');
    zvij_invitation_register($friend_id);
    invite_check((int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE friend_user_id = %d", $friend_id)) === 1, 'Repeated registration cannot duplicate friendship');
    zvij_invitation_register($owner_id);
    invite_check((int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE friend_user_id = %d", $owner_id)) === 0, 'Self-referral rejected');
    unset($_COOKIE['zvij_invitation']);
    $other_id = invite_customer('other');
    $other_email = get_userdata($other_id)->user_email;
    $other_token = zvij_invitation_token($other_email);
    $_COOKIE['zvij_invitation'] = zvij_invitation_sign($other_token, 'facebook', time());
    zvij_invitation_register($friend_id);
    invite_check(zvij_invitation_member_stats((int) $owner['id'], $owner_email)['friends'] === 1, 'First inviter cannot be overwritten');
    invite_check(zvij_invitation_member_stats((int) zvij_membership_find_by_email($other_email)['id'], $other_email)['friends'] === 0, 'Second inviter receives no copied friendship');
    foreach ([0 => 0, 9 => 0, 10 => 1, 25 => 2, 100 => 10, 450 => 45] as $earned => $expected) {
        invite_check(zvij_invitation_bonus_amount($earned) === $expected, '10 percent and floor: ' . $earned);
    }
    $order = invite_order($friend_id, 100);
    zvij_invitation_paid($order->get_id(), 'pending', 'pending', $order);
    invite_check(zvij_credit_balance($owner_email) === 0, 'No reward for unpaid order');
    zvij_invitation_paid($order->get_id(), 'pending', 'processing', $order);
    zvij_invitation_paid($order->get_id(), 'processing', 'completed', $order);
    invite_check(zvij_credit_balance($owner_email) === 10, '100 friend crystals award 10 exactly once');
    invite_check(zvij_credit_balance($friend_email) === 100, 'Friend retains all own crystals');
    $event = zvij_invitation_ledger_event('invitation:earn:' . $order->get_id());
    invite_check($event['related_email'] === $friend_email && (int) $event['order_id'] === $order->get_id(), 'Bonus records source friend and order');
    // Simulate a crash after ledger insert but before order meta persisted.
    $order->delete_meta_data('_zvij_invitation_bonus');
    $order->save();
    zvij_invitation_paid($order->get_id(), 'processing', 'completed', $order);
    invite_check(zvij_credit_balance($owner_email) === 10 && (int) $order->get_meta('_zvij_invitation_bonus') === 10, 'Retry repairs missing metadata without paying twice');
    zvij_credit_add($friend_email, 1000, 'referral_bonus', null, 'Nested bonus fixture');
    $second = invite_order($friend_id, 25);
    zvij_invitation_paid($second->get_id(), 'pending', 'processing', $second);
    invite_check(zvij_credit_balance($owner_email) === 12, 'Later purchases also award 10 percent rounded down');
    $no_ledger = invite_order($friend_id, 100, false);
    zvij_invitation_paid($no_ledger->get_id(), 'pending', 'processing', $no_ledger);
    invite_check(zvij_credit_balance($owner_email) === 12, 'Order metadata without actual earned ledger cannot pay bonus');
    $legacy = invite_order($friend_id, 100);
    $legacy->update_meta_data('_zvij_referral_credited', '300');
    $legacy->save();
    zvij_invitation_paid($legacy->get_id(), 'pending', 'processing', $legacy);
    invite_check(zvij_credit_balance($owner_email) === 12, 'Historical fixed reward is not paid twice');
    $wrong_account = invite_order($friend_id, 100);
    $wrong_account->set_customer_id($owner_id);
    $wrong_account->save();
    zvij_invitation_paid($wrong_account->get_id(), 'pending', 'processing', $wrong_account);
    invite_check(zvij_credit_balance($owner_email) === 12, 'Account mismatch cannot award bonus');
    $old_order = invite_order($friend_id, 100);
    $old_order->set_date_created(time() - 2 * DAY_IN_SECONDS);
    $old_order->save();
    zvij_invitation_paid($old_order->get_id(), 'pending', 'processing', $old_order);
    invite_check(zvij_credit_balance($owner_email) === 12, 'Orders before invitation are not rewarded retroactively');
    invite_check((int) zvij_invitation_ledger_event('invitation:earn:' . $second->get_id())['amount'] === 2, 'Reward base excludes referral bonuses');
    zvij_invitation_cancel($order->get_id(), 'processing', 'refunded', $order);
    zvij_invitation_cancel($order->get_id(), 'refunded', 'cancelled', $order);
    invite_check(zvij_credit_balance($owner_email) === 2, 'Refund reverses reward exactly once');
    $order->update_meta_data('_zvij_credit_earn_reversed', '1');
    $order->save();
    zvij_invitation_paid($order->get_id(), 'refunded', 'processing', $order);
    invite_check(zvij_credit_balance($owner_email) === 2, 'Reversed earnings cannot award a new bonus');
    invite_check(zvij_invitation_member_stats((int) $owner['id'], $owner_email)['bonus'] === 2, 'Account stats report net referral bonuses');
    $signup = zvij_membership_process_signup(['email' => $friend_email, 'source' => 'account'], false);
    $subscribed = zvij_membership_find_by_email($friend_email);
    invite_check($signup['ok'] && $subscribed['status'] === 'subscribed' && $subscribed['consent_at'] !== null, 'Explicit newsletter signup records consent for a loyalty account');
    $qr = zvij_invitation_qr($token);
    $uploads = wp_upload_dir();
    $qr_file = str_replace($uploads['baseurl'], $uploads['basedir'], $qr);
    invite_check($qr !== '' && getimagesize($qr_file)[0] === 360, 'Local QR renderer outputs a valid 360px PNG');
    file_put_contents('/tmp/zvij-invitation-test-qr.txt', $qr_file);
    wp_set_current_user($owner_id);
    ob_start();
    zvij_invitation_admin();
    invite_check(ob_get_clean() === '', 'Customers cannot access admin invitation analytics');
    $administrators = get_users(['role' => 'administrator', 'number' => 1]);
    wp_set_current_user($administrators[0]->ID);
    $_GET = ['period' => 'all', 'member' => $owner_email];
    ob_start();
    zvij_invitation_admin();
    $admin = ob_get_clean();
    invite_check(str_contains($admin, $friend_email) && str_contains($admin, '10 %') && str_contains($admin, 'Storno bonusa'), 'Admin reports friendship, bonus sources and reversals');
    file_put_contents('/tmp/zvij-invitation-admin.html', '<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="' . admin_url('css/common.min.css') . '"><link rel="stylesheet" href="' . admin_url('css/forms.min.css') . '"><link rel="stylesheet" href="' . admin_url('css/list-tables.min.css') . '"></head><body>' . $admin . '</body></html>');
    wp_set_current_user($owner_id);
    $_GET = [];
    global $wp_query;
    $wp_query = new WP_Query(['page_id' => wc_get_page_id('myaccount')]);
    ob_start();
    get_header();
    echo '<div class="zv-commerce">';
    do_action('woocommerce_account_dashboard');
    echo '</div>';
    get_footer();
    $html = ob_get_clean();
    invite_check(str_contains($html, 'data-invite-copy') && str_contains($html, $qr) && str_contains($html, 'whatsapp.com'), 'Account has QR, copy and social links');
    invite_check(! str_contains(zvij_invitation_url($token), zvij_referral_ensure_code($owner_email)), 'Public invitation does not expose redemption code');
    file_put_contents('/tmp/zvij-invitation-account.html', $html);
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL ' . $error->getMessage() . PHP_EOL);
    $exit = 1;
} finally {
    $wpdb->query('ROLLBACK');
    unset($_COOKIE['zvij_invitation']);
}
foreach ($before_counts as $tracked => $count) {
    if ((int) $wpdb->get_var("SELECT COUNT(*) FROM {$tracked}") !== $count) {
        fwrite(STDERR, 'FAIL Transaction cleanup: ' . $tracked . PHP_EOL);
        $exit = 1;
    }
}
if ($exit === 0) {
    echo 'PASS Transaction rolled back all fixture users, memberships, links, credit and posts' . PHP_EOL;
    echo 'PASS TOTAL ' . ($passed + 1) . PHP_EOL;
}
exit($exit);
