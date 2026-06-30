<?php
/**
 * Plugin Name: Zvij Core
 * Plugin URI: https://dev.inteligent.si
 * Description: Core dev features for the Zvij.si WordPress/WooCommerce app.
 * Version: 0.2.0
 * Author: Zvij.si
 * Requires at least: 6.5
 * Requires PHP: 8.2
 * Text Domain: zvij-core
 */

if (! defined('ABSPATH')) {
    exit;
}

define('ZVIJ_CORE_VERSION', '0.2.0');
define('ZVIJ_MEMBER_PRIVACY_VERSION', '2026-06-30');

register_activation_hook(__FILE__, 'zvij_membership_install');
add_action('plugins_loaded', 'zvij_membership_install');

function zvij_membership_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'zvij_members';
}

function zvij_membership_install(): void {
    global $wpdb;

    $installed = (string) get_option('zvij_membership_db_version', '');
    if ($installed === ZVIJ_CORE_VERSION) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset = $wpdb->get_charset_collate();
    $table = zvij_membership_table();

    dbDelta(
        "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email varchar(190) NOT NULL,
            name varchar(190) NOT NULL DEFAULT '',
            status varchar(40) NOT NULL DEFAULT 'subscribed',
            source varchar(40) NOT NULL DEFAULT 'manual',
            customer_status varchar(40) NOT NULL DEFAULT 'lead',
            consent_at datetime NOT NULL,
            consent_ip_hash varchar(64) NOT NULL DEFAULT '',
            privacy_version varchar(40) NOT NULL DEFAULT '',
            first_order_coupon varchar(64) NOT NULL DEFAULT '',
            provider varchar(40) NOT NULL DEFAULT 'mailerlite',
            provider_status varchar(40) NOT NULL DEFAULT 'not_configured',
            provider_message text NULL,
            last_order_date datetime NULL,
            last_test_at datetime NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email),
            KEY status (status),
            KEY source (source)
        ) {$charset};"
    );

    update_option('zvij_membership_db_version', ZVIJ_CORE_VERSION, false);
}

function zvij_membership_provider_config(): array {
    $api_key = getenv('MAILERLITE_API_KEY') ?: (string) get_option('zvij_mailerlite_api_key', '');
    $group_id = getenv('MAILERLITE_GROUP_ID') ?: (string) get_option('zvij_mailerlite_group_id', '');
    $webhook_secret = getenv('MAILERLITE_WEBHOOK_SECRET') ?: (string) get_option('zvij_mailerlite_webhook_secret', '');

    return [
        'provider' => 'MailerLite',
        'api_key_set' => $api_key !== '',
        'group_id_set' => $group_id !== '',
        'webhook_secret_set' => $webhook_secret !== '',
        'api_key' => $api_key,
        'group_id' => $group_id,
    ];
}

function zvij_membership_is_provider_ready(): bool {
    $config = zvij_membership_provider_config();
    return $config['api_key_set'] && $config['group_id_set'];
}

function zvij_membership_allowed_source(string $source): string {
    $source = sanitize_key($source);
    $allowed = ['homepage', 'footer', 'checkout', 'account', 'manual', 'admin_test'];
    return in_array($source, $allowed, true) ? $source : 'manual';
}

function zvij_membership_client_ip_hash(): string {
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if ($ip === '') {
        return '';
    }
    return hash('sha256', wp_salt('nonce') . '|' . $ip);
}

function zvij_membership_rate_limited(): bool {
    $hash = zvij_membership_client_ip_hash();
    if ($hash === '') {
        return false;
    }

    $key = 'zvij_member_rate_' . substr($hash, 0, 16);
    $count = (int) get_transient($key);
    if ($count >= 5) {
        return true;
    }

    set_transient($key, $count + 1, 10 * MINUTE_IN_SECONDS);
    return false;
}

function zvij_membership_generate_coupon(string $email): string {
    if (! function_exists('wc_get_coupon_id_by_code')) {
        return '';
    }

    $existing = zvij_membership_find_by_email($email);
    if (! empty($existing['first_order_coupon'])) {
        return (string) $existing['first_order_coupon'];
    }

    for ($i = 0; $i < 8; $i++) {
        $code = 'ZVIJ-' . strtoupper(wp_generate_password(6, false, false));
        if (! wc_get_coupon_id_by_code($code)) {
            break;
        }
    }

    $coupon_id = wp_insert_post([
        'post_title' => $code,
        'post_content' => 'Član Zvij.si first-order coupon.',
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'shop_coupon',
    ]);

    if (is_wp_error($coupon_id) || ! $coupon_id) {
        return '';
    }

    update_post_meta($coupon_id, 'discount_type', 'percent');
    update_post_meta($coupon_id, 'coupon_amount', '10');
    update_post_meta($coupon_id, 'individual_use', 'yes');
    update_post_meta($coupon_id, 'usage_limit', '1');
    update_post_meta($coupon_id, 'usage_limit_per_user', '1');
    update_post_meta($coupon_id, 'free_shipping', 'no');
    update_post_meta($coupon_id, 'date_expires', (string) strtotime('+30 days'));
    update_post_meta($coupon_id, 'customer_email', [$email]);
    update_post_meta($coupon_id, '_zvij_membership_coupon', '1');

    return $code;
}

function zvij_membership_find_by_email(string $email): ?array {
    global $wpdb;
    $table = zvij_membership_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE email = %s", $email), ARRAY_A);
    return is_array($row) ? $row : null;
}

function zvij_membership_upsert(array $data): array {
    global $wpdb;

    $now = current_time('mysql');
    $email = sanitize_email((string) ($data['email'] ?? ''));
    $source = zvij_membership_allowed_source((string) ($data['source'] ?? 'manual'));
    $name = sanitize_text_field((string) ($data['name'] ?? ''));
    $coupon = (string) ($data['first_order_coupon'] ?? '');
    $provider_status = sanitize_key((string) ($data['provider_status'] ?? 'not_configured'));
    $provider_message = sanitize_text_field((string) ($data['provider_message'] ?? ''));
    $customer_status = sanitize_key((string) ($data['customer_status'] ?? 'lead'));

    if (! is_email($email)) {
        return ['ok' => false, 'message' => 'invalid_email'];
    }

    $existing = zvij_membership_find_by_email($email);
    $table = zvij_membership_table();

    if ($existing) {
        $coupon = $coupon !== '' ? $coupon : (string) $existing['first_order_coupon'];
        $wpdb->update(
            $table,
            [
                'name' => $name !== '' ? $name : (string) $existing['name'],
                'source' => $source,
                'status' => 'subscribed',
                'customer_status' => $customer_status !== '' ? $customer_status : (string) $existing['customer_status'],
                'first_order_coupon' => $coupon,
                'provider_status' => $provider_status,
                'provider_message' => $provider_message,
                'updated_at' => $now,
            ],
            ['email' => $email],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'],
            ['%s']
        );
    } else {
        $wpdb->insert(
            $table,
            [
                'email' => $email,
                'name' => $name,
                'status' => 'subscribed',
                'source' => $source,
                'customer_status' => $customer_status !== '' ? $customer_status : 'lead',
                'consent_at' => $now,
                'consent_ip_hash' => zvij_membership_client_ip_hash(),
                'privacy_version' => ZVIJ_MEMBER_PRIVACY_VERSION,
                'first_order_coupon' => $coupon,
                'provider' => 'mailerlite',
                'provider_status' => $provider_status,
                'provider_message' => $provider_message,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
    }

    return ['ok' => true, 'email' => $email, 'coupon' => $coupon];
}

function zvij_membership_sync_mailerlite(string $email, string $name, string $source, string $coupon, string $customer_status = 'lead'): array {
    $config = zvij_membership_provider_config();
    if (! $config['api_key_set'] || ! $config['group_id_set']) {
        return ['status' => 'not_configured', 'message' => 'Missing MailerLite API key or group ID.'];
    }

    $payload = [
        'email' => $email,
        'fields' => [
            'name' => $name,
            'source' => $source,
            'signup_date' => current_time('Y-m-d'),
            'customer_status' => $customer_status,
            'first_order_coupon' => $coupon,
        ],
        'groups' => [(string) $config['group_id']],
    ];

    $response = wp_remote_post('https://connect.mailerlite.com/api/subscribers', [
        'timeout' => 12,
        'headers' => [
            'Authorization' => 'Bearer ' . $config['api_key'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'body' => wp_json_encode($payload),
    ]);

    if (is_wp_error($response)) {
        return ['status' => 'failed', 'message' => $response->get_error_message()];
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code >= 200 && $code < 300) {
        return ['status' => 'synced', 'message' => 'Subscriber synced to MailerLite.'];
    }

    return ['status' => 'failed', 'message' => 'MailerLite HTTP ' . $code];
}

function zvij_membership_send_welcome_email(string $email, string $coupon): bool {
    $shop_url = home_url('/trgovina/');
    $privacy_url = get_privacy_policy_url();
    $subject = 'Dobrodošel med člani Zvij.si';
    $message = "Živjo,\n\n";
    $message .= "dobrodošel med člani Zvij.si.\n\n";
    $message .= "Tukaj je tvoja koda za prvi nakup:\n\n";
    $message .= ($coupon !== '' ? $coupon : 'Koda bo pripravljena kmalu') . "\n\n";
    $message .= "Z njo dobiš 10 % popusta pri prvem naročilu.\n\n";
    $message .= "Na mail ti bomo občasno poslali tudi kakšno Zvijačo za zvijače: nove izdelke, uporabne ideje, članske ponudbe in stvari, ki ti olajšajo ritual.\n\n";
    $message .= "Poglej ponudbo: {$shop_url}\n\n";
    $message .= "Odjava: povezava za odjavo bo aktivna po povezavi z MailerLite.\n";
    $message .= "Politika zasebnosti: {$privacy_url}\n\n";
    $message .= "Zvij.si\nTvoj vajb. Tvoja rutina. Tvoj lajf. Tvoja pravila.\n";

    return wp_mail($email, $subject, $message, ['Content-Type: text/plain; charset=UTF-8']);
}

function zvij_membership_process_signup(array $input, bool $send_email = true): array {
    $email = sanitize_email((string) ($input['email'] ?? ''));
    $name = sanitize_text_field((string) ($input['name'] ?? ''));
    $source = zvij_membership_allowed_source((string) ($input['source'] ?? 'manual'));

    if (! is_email($email)) {
        return ['ok' => false, 'public_message' => 'Prijava trenutno ni uspela. Poskusi ponovno ali nam piši.'];
    }

    $coupon = zvij_membership_generate_coupon($email);
    $provider = zvij_membership_sync_mailerlite($email, $name, $source, $coupon);
    $stored = zvij_membership_upsert([
        'email' => $email,
        'name' => $name,
        'source' => $source,
        'first_order_coupon' => $coupon,
        'provider_status' => $provider['status'],
        'provider_message' => $provider['message'],
    ]);

    if (! $stored['ok']) {
        return ['ok' => false, 'public_message' => 'Prijava trenutno ni uspela. Poskusi ponovno ali nam piši.'];
    }

    if ($send_email) {
        zvij_membership_send_welcome_email($email, $coupon);
    }

    update_option('zvij_membership_last_test_status', $source === 'admin_test' ? 'sent' : 'signup', false);
    update_option('zvij_membership_last_test_at', current_time('mysql'), false);

    return [
        'ok' => true,
        'email' => $email,
        'coupon' => $coupon,
        'provider_status' => $provider['status'],
        'public_message' => 'Dobrodošel med člani Zvij.si. Preveri svoj e-poštni predal.',
    ];
}

function zvij_membership_form(array $args = []): string {
    $source = zvij_membership_allowed_source((string) ($args['source'] ?? 'manual'));
    $privacy_url = get_privacy_policy_url();
    $message = '';
    if (isset($_GET['zvij_member'])) {
        $message = $_GET['zvij_member'] === 'ok'
            ? 'Dobrodošel med člani Zvij.si. Preveri svoj e-poštni predal.'
            : 'Prijava trenutno ni uspela. Poskusi ponovno ali nam piši.';
    }

    ob_start();
    ?>
    <form class="zvij-member-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <input type="hidden" name="action" value="zvij_membership_signup">
      <input type="hidden" name="source" value="<?php echo esc_attr($source); ?>">
      <?php wp_nonce_field('zvij_membership_signup', 'zvij_membership_nonce'); ?>
      <div class="zvij-member-form__trap" aria-hidden="true">
        <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
      </div>
      <div class="zvij-member-form__grid">
        <label>
          <span>Ime</span>
          <input type="text" name="name" autocomplete="name">
        </label>
        <label>
          <span>E-poštni naslov</span>
          <input type="email" name="email" autocomplete="email" required>
        </label>
      </div>
      <label class="zvij-member-form__consent">
        <input type="checkbox" name="consent" value="1" required>
        <span>Strinjam se, da mi Zvij.si pošilja novice, ponudbe in vsebine po e-pošti. Odjavim se lahko kadarkoli.<?php if ($privacy_url !== '') : ?> <a href="<?php echo esc_url($privacy_url); ?>">Politika zasebnosti</a>.<?php endif; ?></span>
      </label>
      <button class="button" type="submit">Postani član</button>
      <?php if ($message !== '') : ?><p class="zvij-member-form__message"><?php echo esc_html($message); ?></p><?php endif; ?>
    </form>
    <?php
    return (string) ob_get_clean();
}

add_shortcode('zvij_membership_form', function ($atts): string {
    return zvij_membership_form([
        'source' => is_array($atts) && isset($atts['source']) ? (string) $atts['source'] : 'manual',
    ]);
});

add_action('admin_post_nopriv_zvij_membership_signup', 'zvij_membership_handle_signup');
add_action('admin_post_zvij_membership_signup', 'zvij_membership_handle_signup');

function zvij_membership_handle_signup(): void {
    $fallback = wp_get_referer() ?: home_url('/');
    if (! isset($_POST['zvij_membership_nonce']) || ! wp_verify_nonce((string) $_POST['zvij_membership_nonce'], 'zvij_membership_signup')) {
        wp_safe_redirect(add_query_arg('zvij_member', 'fail', $fallback));
        exit;
    }
    if (! empty($_POST['website']) || empty($_POST['consent']) || zvij_membership_rate_limited()) {
        wp_safe_redirect(add_query_arg('zvij_member', 'fail', $fallback));
        exit;
    }

    $result = zvij_membership_process_signup([
        'email' => (string) ($_POST['email'] ?? ''),
        'name' => (string) ($_POST['name'] ?? ''),
        'source' => (string) ($_POST['source'] ?? 'manual'),
    ]);

    wp_safe_redirect(add_query_arg('zvij_member', $result['ok'] ? 'ok' : 'fail', $fallback));
    exit;
}

add_action('woocommerce_after_order_notes', function ($checkout): void {
    echo '<div class="zvij-checkout-optin">';
    woocommerce_form_field('zvij_membership_optin', [
        'type' => 'checkbox',
        'class' => ['form-row-wide'],
        'label' => 'Postani član Zvij.si in prejemaj novice, ponudbe in občasne Zvijače za zvijače.',
        'required' => false,
    ], $checkout->get_value('zvij_membership_optin'));
    echo '</div>';
});

add_action('woocommerce_checkout_update_order_meta', function (int $order_id): void {
    if (empty($_POST['zvij_membership_optin'])) {
        return;
    }
    $order = wc_get_order($order_id);
    if (! $order instanceof WC_Order) {
        return;
    }
    $email = $order->get_billing_email();
    if (! is_email($email)) {
        return;
    }
    update_post_meta($order_id, '_zvij_membership_marketing_consent', 'yes');
    zvij_membership_process_signup([
        'email' => $email,
        'name' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
        'source' => 'checkout',
    ], false);
});

add_action('woocommerce_checkout_order_processed', function (int $order_id): void {
    $order = wc_get_order($order_id);
    if (! $order instanceof WC_Order) {
        return;
    }
    $email = $order->get_billing_email();
    if (! is_email($email)) {
        return;
    }

    global $wpdb;
    $table = zvij_membership_table();
    $member = zvij_membership_find_by_email($email);
    if (! $member) {
        return;
    }

    $wpdb->update(
        $table,
        [
            'customer_status' => 'customer',
            'last_order_date' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ],
        ['email' => $email],
        ['%s', '%s', '%s'],
        ['%s']
    );
}, 20);

add_action('admin_menu', function (): void {
    add_options_page('Član Zvij.si Email', 'Član Zvij.si Email', 'manage_options', 'zvij-membership-email', 'zvij_membership_admin_page');
});

add_action('admin_notices', function (): void {
    if (! current_user_can('manage_options')) {
        return;
    }
    if (zvij_membership_is_provider_ready()) {
        return;
    }
    echo '<div class="notice notice-warning"><p>';
    echo esc_html__('Član Zvij.si email sistem je aktiven, MailerLite pa še ni povezan. Dodaj MAILERLITE_API_KEY in MAILERLITE_GROUP_ID ali nastavi zaščitene WordPress options.', 'zvij-core');
    echo '</p></div>';
});

function zvij_membership_admin_page(): void {
    if (! current_user_can('manage_options')) {
        return;
    }

    $config = zvij_membership_provider_config();
    $last_at = (string) get_option('zvij_membership_last_test_at', '');
    $last_status = (string) get_option('zvij_membership_last_test_status', '');
    ?>
    <div class="wrap">
      <h1>Član Zvij.si Email</h1>
      <table class="widefat striped" style="max-width: 760px;">
        <tbody>
          <tr><th>Email provider</th><td><?php echo $config['api_key_set'] ? 'connected/configured' : 'not connected'; ?></td></tr>
          <tr><th>Subscriber group</th><td><?php echo $config['group_id_set'] ? 'configured' : 'not configured'; ?></td></tr>
          <tr><th>Welcome automation</th><td><?php echo function_exists('wp_mail') ? 'local wp_mail active; provider automation pending' : 'inactive'; ?></td></tr>
          <tr><th>Coupon generation</th><td><?php echo function_exists('wc_get_coupon_id_by_code') ? 'active' : 'inactive'; ?></td></tr>
          <tr><th>Last test signup</th><td><?php echo esc_html($last_at !== '' ? $last_at . ' / ' . $last_status : 'never'); ?></td></tr>
          <tr><th>Webhook secret</th><td><?php echo $config['webhook_secret_set'] ? 'configured' : 'not configured'; ?></td></tr>
        </tbody>
      </table>

      <h2>Send test signup</h2>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="max-width: 520px;">
        <input type="hidden" name="action" value="zvij_membership_admin_test">
        <?php wp_nonce_field('zvij_membership_admin_test', 'zvij_membership_admin_nonce'); ?>
        <p><label>Test email<br><input class="regular-text" type="email" name="email" required></label></p>
        <p><label>Name<br><input class="regular-text" type="text" name="name"></label></p>
        <p><button class="button button-primary" type="submit">Send test signup</button></p>
      </form>
      <p>Secrets are intentionally not shown here.</p>
    </div>
    <?php
}

add_action('admin_post_zvij_membership_admin_test', function (): void {
    if (! current_user_can('manage_options') || ! isset($_POST['zvij_membership_admin_nonce']) || ! wp_verify_nonce((string) $_POST['zvij_membership_admin_nonce'], 'zvij_membership_admin_test')) {
        wp_die('Not allowed.');
    }

    zvij_membership_process_signup([
        'email' => (string) ($_POST['email'] ?? ''),
        'name' => (string) ($_POST['name'] ?? ''),
        'source' => 'admin_test',
    ]);

    wp_safe_redirect(admin_url('options-general.php?page=zvij-membership-email'));
    exit;
});
