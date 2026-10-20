<?php
/** Ten immutable, uniquely keyed ledger slots per account, not per browser. */
if (! defined('ABSPATH')) { exit; }
function zvij_smoke_egg_progress(int $user_id): int {
    if ($user_id < 1) { return 0; }
    global $wpdb;
    $keys = [];
    for ($slot = 1; $slot <= 10; $slot++) { $keys[] = 'smoke-egg:' . $user_id . ':' . $slot; }
    return (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . zvij_credit_table() . ' WHERE event_key IN (' . implode(',', array_fill(0, 10, '%s')) . ')', ...$keys));
}
function zvij_smoke_egg_quest(): void {
    if (! is_user_logged_in()) { return; }
    $progress = zvij_smoke_egg_progress(get_current_user_id());
    echo '<section class="zv-secret-quest" aria-labelledby="zv-secret-quest-title"><div><p class="zv-secret-quest-label">Secret quest</p><h3 id="zv-secret-quest-title">' . esc_html($progress ? 'Kjer je dim, je tudi ogenj' : 'Nekaj se skriva ...') . '</h3></div><div class="zv-secret-quest-progress" role="status" aria-live="polite"><strong data-smoke-quest-progress>' . esc_html($progress ? $progress . '/10' : '?/?') . '</strong><span data-smoke-quest-reward>' . esc_html($progress === 10 ? 'Zdej si se pa že uženu, dost maš... 😶‍🌫️' : ($progress ? '+1 kristal za vsak odkrit klik' : 'Skrita nagrada')) . '</span></div></section>';
}
add_action('woocommerce_account_dashboard', 'zvij_smoke_egg_quest', 12);
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
    wp_send_json_success(['awarded' => $awarded, 'progress' => zvij_smoke_egg_progress(get_current_user_id()), 'balance_label' => zvij_kristali_izpis($balance)]);
});
add_action('wp_enqueue_scripts', function (): void {
    $url = plugin_dir_url(dirname(__FILE__)) . 'assets/';
    wp_enqueue_style('zvij-smoke-egg', $url . 'smoke-egg.css', [], '1.4.0');
    wp_enqueue_script('zvij-smoke-egg', $url . 'smoke-egg.js', [], '1.4.0', true);
    wp_localize_script('zvij-smoke-egg', 'zvijSmokeEgg', ['url' => admin_url('admin-ajax.php'), 'nonce' => is_user_logged_in() ? wp_create_nonce('zvij_smoke_egg') : '', 'loggedIn' => is_user_logged_in()]);
});
