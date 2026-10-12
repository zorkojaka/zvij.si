<?php
/** Public invitations are separate from the private code used to redeem credit. */
if (! defined('ABSPATH')) {
    exit;
}

function zvij_invitation_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'zvij_invitations';
}

function zvij_invitation_links_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'zvij_invitation_links';
}

function zvij_invitation_install(): void {
    if (get_option('zvij_invitation_db_version') === '2') {
        return;
    }
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset = $wpdb->get_charset_collate();
    $links = zvij_invitation_links_table();
    $invitations = zvij_invitation_table();
    $members = zvij_membership_table();
    // dbDelta does not reliably change nullability of an existing column.
    if ($wpdb->query("ALTER TABLE {$members} MODIFY consent_at datetime NULL") === false) {
        return;
    }
    dbDelta("CREATE TABLE {$links} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        owner_member_id bigint(20) unsigned NOT NULL,
        token varchar(64) NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY owner_member_id (owner_member_id),
        UNIQUE KEY token (token)
    ) {$charset};");
    dbDelta("CREATE TABLE {$invitations} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        owner_member_id bigint(20) unsigned NOT NULL,
        friend_user_id bigint(20) unsigned NOT NULL,
        friend_email varchar(190) NOT NULL,
        channel varchar(30) NOT NULL DEFAULT 'link',
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY friend_user_id (friend_user_id),
        UNIQUE KEY friend_email (friend_email),
        KEY owner_member_id (owner_member_id)
    ) {$charset};");
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $links)) === $links
        && $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $invitations)) === $invitations) {
        update_option('zvij_invitation_db_version', '2', false);
        update_option('woocommerce_enable_myaccount_registration', 'yes');
    }
}
add_action('plugins_loaded', 'zvij_invitation_install', 12);

function zvij_invitation_channel(string $channel): string {
    return in_array($channel, ['link', 'qr', 'whatsapp', 'facebook', 'telegram', 'x', 'native'], true) ? $channel : 'link';
}

function zvij_invitation_token(string $email): string {
    global $wpdb;
    $member = zvij_membership_find_by_email(sanitize_email($email));
    if (! $member) {
        return '';
    }
    $table = zvij_invitation_links_table();
    $token = $wpdb->get_var($wpdb->prepare("SELECT token FROM {$table} WHERE owner_member_id = %d", $member['id']));
    if (! $token) {
        $candidate = bin2hex(random_bytes(24));
        $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$table} (owner_member_id, token, created_at) VALUES (%d, %s, %s)", $member['id'], $candidate, current_time('mysql')));
        $token = $wpdb->get_var($wpdb->prepare("SELECT token FROM {$table} WHERE owner_member_id = %d", $member['id']));
    }
    return (string) $token;
}

function zvij_invitation_owner(string $token): ?array {
    if (! preg_match('/^[a-f0-9]{48}$/', $token)) {
        return null;
    }
    global $wpdb;
    $links = zvij_invitation_links_table();
    $members = zvij_membership_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT m.* FROM {$links} l JOIN {$members} m ON m.id = l.owner_member_id WHERE l.token = %s", $token), ARRAY_A);
    return is_array($row) ? $row : null;
}

function zvij_invitation_url(string $token, string $channel = 'link'): string {
    return add_query_arg(['zvij_invite' => $token, 'via' => zvij_invitation_channel($channel)], wc_get_page_permalink('myaccount'));
}

function zvij_invitation_sign(string $token, string $channel, int $at): string {
    $data = $token . '.' . zvij_invitation_channel($channel) . '.' . $at;
    return $data . '.' . hash_hmac('sha256', $data, wp_salt('auth'));
}

function zvij_invitation_cookie_data(string $value): ?array {
    $parts = explode('.', $value);
    if (count($parts) !== 4 || ! ctype_digit($parts[2])) {
        return null;
    }
    [$token, $channel, $at, $signature] = $parts;
    if (! preg_match('/^[a-f0-9]{48}$/', $token) || zvij_invitation_channel($channel) !== $channel
        || (int) $at > time() || (int) $at < time() - 30 * DAY_IN_SECONDS
        || ! hash_equals(zvij_invitation_sign($token, $channel, (int) $at), $value)) {
        return null;
    }
    return ['token' => $token, 'channel' => $channel];
}

function zvij_invitation_pending(): ?array {
    $data = zvij_invitation_cookie_data((string) ($_COOKIE['zvij_invitation'] ?? ''));
    return $data && zvij_invitation_owner($data['token']) ? $data : null;
}

function zvij_invitation_capture(): void {
    if (is_user_logged_in() || ! isset($_GET['zvij_invite']) || zvij_invitation_pending()) {
        return;
    }
    $token = sanitize_text_field(wp_unslash((string) $_GET['zvij_invite']));
    if (! zvij_invitation_owner($token)) {
        return;
    }
    $channel = zvij_invitation_channel(sanitize_key((string) ($_GET['via'] ?? 'link')));
    $value = zvij_invitation_sign($token, $channel, time());
    setcookie('zvij_invitation', $value, [
        'expires' => time() + 30 * DAY_IN_SECONDS,
        'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax',
    ]);
    $_COOKIE['zvij_invitation'] = $value;
}
add_action('init', 'zvij_invitation_capture', 5);

/** A shop account earns loyalty credit without subscribing to marketing. */
function zvij_invitation_account_member(int $user_id): void {
    $user = get_userdata($user_id);
    if (! $user || ! is_email($user->user_email) || zvij_membership_find_by_email($user->user_email)) {
        return;
    }
    global $wpdb;
    $now = current_time('mysql');
    $wpdb->insert(zvij_membership_table(), [
        'email' => sanitize_email($user->user_email), 'name' => sanitize_text_field($user->display_name),
        'status' => 'account', 'source' => 'account', 'consent_at' => null,
        'provider_status' => 'not_subscribed', 'created_at' => $now, 'updated_at' => $now,
    ]);
}

function zvij_invitation_register(int $user_id): void {
    zvij_invitation_account_member($user_id);
    $pending = zvij_invitation_pending();
    $user = get_userdata($user_id);
    if (! $pending || ! $user) {
        return;
    }
    $owner = zvij_invitation_owner($pending['token']);
    $email = sanitize_email($user->user_email);
    if (! $owner || $email === sanitize_email($owner['email'])) {
        return;
    }
    global $wpdb;
    $table = zvij_invitation_table();
    $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$table} (owner_member_id, friend_user_id, friend_email, channel, created_at) VALUES (%d, %d, %s, %s, %s)", $owner['id'], $user_id, $email, $pending['channel'], current_time('mysql')));
}
add_action('woocommerce_created_customer', 'zvij_invitation_register', 20);
add_action('woocommerce_account_dashboard', function (): void {
    if (get_current_user_id() > 0) {
        zvij_invitation_account_member(get_current_user_id());
    }
}, 5);

function zvij_invitation_registration_note(): void {
    $pending = zvij_invitation_pending();
    if ($pending) {
        echo '<p class="woocommerce-info">Prišel si s prijateljevim povabilom. Ko ustvariš račun, se povabilo shrani. Z nakupi zbiraš kristale, prijatelj pa dobi dodaten bonus. Tvoji kristali ostanejo tvoji.</p>';
    }
    echo '<p>Z računom zbiraš kristale za naslednje nakupe. Prijava na e-poštne novice je ločena in neobvezna.</p>';
}
add_action('woocommerce_register_form_start', 'zvij_invitation_registration_note');

function zvij_invitation_bonus_amount(int $earned): int {
    return intdiv(max(0, $earned), 10);
}

function zvij_invitation_ledger_event(string $key): ?array {
    global $wpdb;
    $table = zvij_credit_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE event_key = %s", $key), ARRAY_A);
    return is_array($row) ? $row : null;
}

function zvij_invitation_paid($order_id, $from, $to, $order): void {
    if (! $order instanceof WC_Order || ! in_array($to, zvij_invoice_statuses(), true)
        || $order->get_meta('_zvij_credit_earn_reversed') !== '') {
        return;
    }
    // Historical fixed awards stay intact; never add a second reward to them.
    if ($order->get_meta('_zvij_referral_credited') !== '' && $order->get_meta('_zvij_invitation_bonus') === '') {
        return;
    }
    $earned = (int) $order->get_meta('_zvij_credit_earned');
    $amount = zvij_invitation_bonus_amount($earned);
    if ($amount < 1) {
        return;
    }
    global $wpdb;
    $table = zvij_invitation_table();
    $members = zvij_membership_table();
    $email = sanitize_email($order->get_billing_email());
    $link = $wpdb->get_row($wpdb->prepare("SELECT i.*, m.email AS owner_email FROM {$table} i JOIN {$members} m ON m.id = i.owner_member_id WHERE i.friend_email = %s", $email), ARRAY_A);
    if (! $link || sanitize_email($link['owner_email']) === $email
        || ! get_userdata((int) $link['friend_user_id'])
        || ($order->get_customer_id() > 0 && $order->get_customer_id() !== (int) $link['friend_user_id'])) {
        return;
    }
    if ($order->get_date_created() && $order->get_date_created()->date('Y-m-d H:i:s') < $link['created_at']) {
        return; // An invitation never earns bonuses on orders predating registration.
    }
    $ledger = zvij_credit_table();
    $actual = (int) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount),0) FROM {$ledger} WHERE member_email = %s AND order_id = %d AND type = 'earn'", $email, $order->get_id()));
    $earned = min($earned, max(0, $actual));
    $amount = zvij_invitation_bonus_amount($earned);
    if ($amount < 1) {
        return;
    }
    $key = 'invitation:earn:' . $order->get_id();
    $event = zvij_invitation_ledger_event($key);
    if (! $event) {
        $added = zvij_credit_add($link['owner_email'], $amount, 'referral_bonus', (int) $order->get_id(), sprintf('10 %% od %d kristalov prijatelja; povabilo #%d', $earned, $link['id']), $key, $email);
        if (! $added && ! zvij_invitation_ledger_event($key)) {
            return;
        }
        $event = zvij_invitation_ledger_event($key);
    }
    if (! $event) {
        return;
    }
    $order->update_meta_data('_zvij_invitation_bonus', (string) (int) $event['amount']);
    $order->update_meta_data('_zvij_referral_owner', $event['member_email']);
    $order->save();
}
add_action('woocommerce_order_status_changed', 'zvij_invitation_paid', 36, 4);

function zvij_invitation_cancel($order_id, $from, $to, $order): void {
    if (! $order instanceof WC_Order || ! in_array($to, ['cancelled', 'refunded', 'failed'], true)) {
        return;
    }
    $event = zvij_invitation_ledger_event('invitation:earn:' . $order->get_id());
    if (! $event) {
        return;
    }
    $key = 'invitation:reverse:' . $order->get_id();
    if (! zvij_invitation_ledger_event($key)) {
        zvij_credit_add($event['member_email'], -(int) $event['amount'], 'referral_reversal', (int) $order->get_id(), 'Storno bonusa povabila: ' . $to, $key, $event['related_email']);
    }
    if (zvij_invitation_ledger_event($key)) {
        $order->update_meta_data('_zvij_referral_credit_reversed', '1');
        $order->save();
    }
}
add_action('woocommerce_order_status_changed', 'zvij_invitation_cancel', 36, 4);

function zvij_invitation_qr(string $token): string {
    if (! class_exists('BaconQrCode\\Writer') || ! extension_loaded('gd')) {
        return '';
    }
    $url = zvij_invitation_url($token, 'qr');
    $uploads = wp_upload_dir();
    $dir = trailingslashit($uploads['basedir']) . 'zvij-invitations/';
    $name = hash('sha256', $url) . '.png';
    if (! file_exists($dir . $name)) {
        if (! wp_mkdir_p($dir)) {
            return '';
        }
        try {
            $writer = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\GDLibRenderer(360, 4));
            $png = $writer->writeString($url);
            if (file_put_contents($dir . $name, $png, LOCK_EX) === false) {
                return '';
            }
        } catch (\Throwable $error) {
            return '';
        }
    }
    return trailingslashit($uploads['baseurl']) . 'zvij-invitations/' . $name;
}

function zvij_invitation_member_stats(int $member_id, string $email): array {
    global $wpdb;
    $table = zvij_invitation_table();
    $ledger = zvij_credit_table();
    return [
        'friends' => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE owner_member_id = %d", $member_id)),
        'bonus' => (int) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount), 0) FROM {$ledger} WHERE member_email = %s AND type IN ('referral_bonus', 'referral_reversal')", $email)),
    ];
}

add_action('wp_enqueue_scripts', function (): void {
    if (function_exists('is_account_page') && is_account_page()) {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('zvij-invitations', plugins_url('assets/invitations.css', dirname(__DIR__) . '/zvij-core.php'), [], '1.0.0');
        wp_enqueue_script('zvij-invitations', plugins_url('assets/invitations.js', dirname(__DIR__) . '/zvij-core.php'), [], '1.0.0', true);
    }
});

function zvij_invitation_account(): void {
    $user = wp_get_current_user();
    $email = sanitize_email($user->user_email);
    $member = zvij_membership_find_by_email($email);
    if (! $member) {
        echo '<section><h3>Zbiraj kristale in povabi prijatelje.</h3>' . zvij_membership_form(['source' => 'account']) . '</section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        return;
    }
    $token = zvij_invitation_token($email);
    if ($token === '') {
        return;
    }
    $url = zvij_invitation_url($token);
    $qr = zvij_invitation_qr($token);
    $stats = zvij_invitation_member_stats((int) $member['id'], $email);
    $message = 'Zvijače za zvijače. Ustvari račun, z nakupi nabiraj kristale in naslednjič plačaj manj. ';
    $shares = [
        ['WhatsApp', 'whatsapp', 'whatsapp', 'https://api.whatsapp.com/send?text=' . rawurlencode($message . zvij_invitation_url($token, 'whatsapp'))],
        ['Facebook', 'facebook', 'facebook-alt', 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode(zvij_invitation_url($token, 'facebook'))],
        ['Telegram', 'telegram', 'share', 'https://t.me/share/url?url=' . rawurlencode(zvij_invitation_url($token, 'telegram')) . '&text=' . rawurlencode($message)],
        ['X', 'x', 'twitter', 'https://twitter.com/intent/tweet?text=' . rawurlencode($message) . '&url=' . rawurlencode(zvij_invitation_url($token, 'x'))],
    ];
    ?>
    <section class="zv-invite" aria-labelledby="zv-invite-title">
      <h3 id="zv-invite-title">Dobra roba se deli s prijatelji.</h3>
      <p>Pošlji povabilo ali pokaži QR-kodo. Ko si prijatelj prek tvojega povabila ustvari račun, se povežeta. Ob njegovih plačanih nakupih dobiš <strong>dodatnih 10 % njegovih kristalov</strong>. Njemu ostanejo vsi.</p>
      <p>On dobi 100 kristalov. Ti dobiš še 10. Oba imata nekaj za naslednjič.</p>
      <div class="zv-invite__grid">
        <?php if ($qr !== '') : ?>
        <figure class="zv-invite__qr"><img src="<?php echo esc_url($qr); ?>" alt="QR-koda za tvoje osebno povabilo prijateljem" width="180" height="180"><figcaption>Tvoje povabilo, en sken stran.</figcaption><a href="<?php echo esc_url($qr); ?>" download="zvij-povabilo.png">Prenesi QR-kodo</a></figure>
        <?php endif; ?>
        <div class="zv-invite__tools">
          <label for="zv-invite-link">Tvoja povezava za prijatelje</label>
          <input id="zv-invite-link" type="url" readonly value="<?php echo esc_url($url); ?>">
          <div class="zv-invite__actions">
            <button type="button" class="button" data-invite-copy title="Kopiraj povezavo"><span class="dashicons dashicons-admin-links" aria-hidden="true"></span> Kopiraj povezavo</button>
            <button type="button" class="button" data-invite-share data-url="<?php echo esc_url(zvij_invitation_url($token, 'native')); ?>" hidden title="Deli povabilo"><span class="dashicons dashicons-share" aria-hidden="true"></span> Deli</button>
          </div>
          <div class="zv-invite__social" aria-label="Deli s prijatelji">
            <?php foreach ($shares as [$label, $channel, $icon, $href]) : ?>
            <a href="<?php echo esc_url($href); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr('Deli na ' . $label); ?>" aria-label="<?php echo esc_attr('Deli na ' . $label); ?>"><span class="dashicons dashicons-<?php echo esc_attr($icon); ?>" aria-hidden="true"></span><span><?php echo esc_html($label); ?></span></a>
            <?php endforeach; ?>
          </div>
          <p class="zv-invite__feedback" role="status" aria-live="polite"></p>
        </div>
      </div>
      <p class="zv-invite__stats">Prijatelji z računom: <strong><?php echo esc_html((string) $stats['friends']); ?></strong> · Iz povabil: <strong><?php echo esc_html(zvij_kristali_izpis($stats['bonus'])); ?></strong></p>
      <small>Bonus velja za prijatelje, ki si prek tvoje povezave ustvarijo nov račun, in samo za kristale iz njihovih nakupov. Nagrajujejo se neposredna povabila. Bonus zaokrožimo navzdol na cel kristal; ob stornu nakupa se bonus odšteje.</small>
    </section>
    <?php
}
add_action('woocommerce_account_dashboard', 'zvij_invitation_account', 16);
