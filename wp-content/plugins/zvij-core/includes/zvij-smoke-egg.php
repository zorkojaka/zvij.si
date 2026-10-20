<?php
/** Ten immutable, uniquely keyed ledger slots per account, not per browser. */
if (! defined('ABSPATH')) { exit; }
function zvij_smoke_egg_award(int $user_id): bool {
    global $wpdb;
    $user = get_userdata($user_id);
    if (! $user || ! is_email($user->user_email)) { return false; }
    for ($slot = 1; $slot <= 10; $slot++) {
        $inserted = $wpdb->query($wpdb->prepare(
            'INSERT IGNORE INTO ' . zvij_credit_table() . ' (member_email, amount, type, note, event_key, created_at) VALUES (%s, 1, %s, %s, %s, %s)',
            sanitize_email($user->user_email), 'smoke_egg', 'Kjer je dim, je tudi ogenj. Klik na I.', 'smoke-egg:' . $user_id . ':' . $slot, current_time('mysql')
        ));
        if ($inserted === 1) { return true; }
        if ($inserted === false) { return false; }
    }
    return false;
}
add_action('wp_ajax_zvij_smoke_egg', function (): void {
    check_ajax_referer('zvij_smoke_egg', 'nonce');
    $awarded = zvij_smoke_egg_award(get_current_user_id());
    $balance = zvij_credit_balance(wp_get_current_user()->user_email);
    wp_send_json_success(['awarded' => $awarded, 'balance_label' => zvij_kristali_izpis($balance)]);
});
add_action('wp_enqueue_scripts', function (): void {
    $url = plugin_dir_url(dirname(__FILE__)) . 'assets/';
    wp_enqueue_style('zvij-smoke-egg', $url . 'smoke-egg.css', [], '1.0.0');
    wp_enqueue_script('zvij-smoke-egg', $url . 'smoke-egg.js', [], '1.0.0', true);
    wp_localize_script('zvij-smoke-egg', 'zvijSmokeEgg', ['url' => admin_url('admin-ajax.php'), 'nonce' => is_user_logged_in() ? wp_create_nonce('zvij_smoke_egg') : '', 'loggedIn' => is_user_logged_in()]);
});
