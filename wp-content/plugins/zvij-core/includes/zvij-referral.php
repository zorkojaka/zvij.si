<?php
/**
 * Zvij koda — glej docs/REFERRAL_SPEC.md.
 *
 * Vsak član ima svojo Zvij kodo (ZK-XXXXXX, stolpec zvij_code v zvij_members).
 * Na blagajni je eno polje "Zvij koda" z dvema vlogama:
 * - SVOJA koda (lastnik = billing email): gost se z njo izkaže kot član in
 *   lahko unovči kristale (koda je pol-skrivnost; sam email ne zadošča).
 * - TUJA koda (povabilo): kupec brez predhodnega plačanega naročila dobi
 *   popust na izdelke prvega naročila, lastnik kode pa ob plačilu naročila
 *   kristale. Samo-priporočanje in ponovni nakupi ne štejejo.
 *
 * Privzetki (spremenljivi prek opcij): popust prijatelja 10 %
 * (`zvij_referral_friend_percent`), nagrada lastnika 30 kristalov
 * (`zvij_referral_owner_kristali`). Storno nagrade ob preklicu naročila.
 * Brez izplačil — vse ostane dobroimetje za naslednji reload.
 */

if (! defined('ABSPATH')) {
    exit;
}

function zvij_referral_friend_percent(): float {
    return (float) apply_filters('zvij_referral_friend_percent', (float) get_option('zvij_referral_friend_percent', 10));
}

function zvij_referral_owner_kristali(): int {
    return (int) apply_filters('zvij_referral_owner_kristali', (int) get_option('zvij_referral_owner_kristali', 30));
}

/** Generira in shrani Zvij kodo za člana, če je še nima. Vrne kodo. */
function zvij_referral_ensure_code(string $email): string {
    global $wpdb;

    $member = zvij_membership_find_by_email($email);
    if (! $member) {
        return '';
    }
    if (! empty($member['zvij_code'])) {
        return (string) $member['zvij_code'];
    }

    $table = zvij_membership_table();
    // brez lahko zamenljivih znakov (0/O, 1/I/L)
    $alphabet = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
    for ($i = 0; $i < 10; $i++) {
        $code = 'ZK-';
        for ($j = 0; $j < 6; $j++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE zvij_code = %s", $code));
        if (! $exists) {
            $wpdb->update($table, ['zvij_code' => $code, 'updated_at' => current_time('mysql')], ['email' => sanitize_email($email)]);
            return $code;
        }
    }

    return '';
}

function zvij_referral_find_member_by_code(string $code): ?array {
    global $wpdb;
    $code = strtoupper(trim($code));
    if ($code === '' || ! preg_match('/^ZK-[0-9A-Z]{6}$/', $code)) {
        return null;
    }
    $table = zvij_membership_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE zvij_code = %s", $code), ARRAY_A);
    return is_array($row) ? $row : null;
}

/** Ali ima email že kakšno plačano/zaključeno naročilo (ni več "prijatelj")? */
function zvij_referral_email_has_paid_order(string $email): bool {
    if (! function_exists('wc_get_orders')) {
        return false;
    }
    $statuses = function_exists('zvij_invoice_statuses') ? zvij_invoice_statuses() : ['processing', 'completed'];
    $orders = wc_get_orders([
        'billing_email' => sanitize_email($email),
        'status' => $statuses,
        'limit' => 1,
        'return' => 'ids',
    ]);
    return $orders !== [];
}

/** Email člana, ki se je na blagajni izkazal s SVOJO kodo (za goste). */
function zvij_referral_session_member_email(): string {
    if (! WC()->session) {
        return '';
    }
    return sanitize_email((string) WC()->session->get('zvij_code_member_email', ''));
}

/** Polje za Zvij kodo na blagajni — znotraj payment fragmenta, ki ga
 * update_checkout AJAX zamenja (hooki nad <div id="payment"> padejo ven
 * iz fragmenta in se nikoli ne osvežijo). */
add_action('woocommerce_review_order_before_submit', function (): void {
    if (! WC()->cart || WC()->cart->is_empty()) {
        return;
    }

    $code = WC()->session ? (string) WC()->session->get('zvij_code_input', '') : '';
    $status = WC()->session ? (string) WC()->session->get('zvij_code_status', '') : '';
    $messages = [
        'own' => __('Koda prepoznana — tvoji kristali so na voljo spodaj.', 'zvij-core'),
        'referral' => sprintf(__('Zvij koda prijatelja velja: %s %% popusta na izdelke prvega naročila.', 'zvij-core'), rtrim(rtrim(number_format(zvij_referral_friend_percent(), 1, ',', ''), '0'), ',')),
        'not_first' => __('Zvij koda velja samo za prvo naročilo.', 'zvij-core'),
        'self' => __('Svoje kode ni mogoče uporabiti kot povabilo.', 'zvij-core'),
        'invalid' => __('Te kode ne najdemo. Preveri zapis (ZK-XXXXXX).', 'zvij-core'),
    ];
    ?>
    <div class="zvij-code-field">
      <label for="zvij_code_input"><?php esc_html_e('Zvij koda (neobvezno)', 'zvij-core'); ?></label>
      <input type="text" id="zvij_code_input" name="zvij_code_input" value="<?php echo esc_attr($code); ?>" placeholder="ZK-XXXXXX" autocomplete="off" style="text-transform:uppercase;">
      <?php if ($status !== '' && isset($messages[$status])) : ?>
        <p class="zvij-code-field__status" data-status="<?php echo esc_attr($status); ?>"><?php echo esc_html($messages[$status]); ?></p>
      <?php endif; ?>
    </div>
    <script>
      jQuery(function ($) {
        var t;
        $(document.body).on('input change', '#zvij_code_input', function () {
          clearTimeout(t);
          t = setTimeout(function () { $(document.body).trigger('update_checkout'); }, 700);
        });
      });
    </script>
    <?php
}, 5);

/**
 * Ob AJAX osvežitvi blagajne razreši vpisano kodo v enega od stanj:
 * own / referral / not_first / self / invalid. Rezultat je v seji.
 */
add_action('woocommerce_checkout_update_order_review', function ($post_data): void {
    if (! WC()->session) {
        return;
    }

    parse_str((string) $post_data, $data);
    $code = strtoupper(trim((string) ($data['zvij_code_input'] ?? '')));
    $billing_email = sanitize_email((string) ($data['billing_email'] ?? ''));

        WC()->session->set('zvij_code_input', $code);
    WC()->session->set('zvij_code_member_email', '');
    WC()->session->set('zvij_code_referral_owner', '');
    WC()->session->set('zvij_code_status', '');

    if ($code === '') {
        return;
    }

    $owner = zvij_referral_find_member_by_code($code);
    if (! $owner) {
        WC()->session->set('zvij_code_status', 'invalid');
        return;
    }

    $owner_email = sanitize_email((string) $owner['email']);
    $checkout_email = is_user_logged_in() ? sanitize_email((string) wp_get_current_user()->user_email) : $billing_email;

    if ($checkout_email !== '' && $owner_email === $checkout_email) {
        if (is_user_logged_in()) {
            // prijavljen član kristale že ima na voljo; koda ni povabilo
            WC()->session->set('zvij_code_status', 'self');
            return;
        }
        WC()->session->set('zvij_code_member_email', $owner_email);
        WC()->session->set('zvij_code_status', 'own');
        return;
    }

    // povabilo: velja samo za prvo plačano naročilo kupca
    if ($checkout_email !== '' && zvij_referral_email_has_paid_order($checkout_email)) {
        WC()->session->set('zvij_code_status', 'not_first');
        return;
    }

    WC()->session->set('zvij_code_referral_owner', $owner_email);
    WC()->session->set('zvij_code_status', 'referral');
}, 5);

/** Popust prijatelja: odstotek na vrednost izdelkov. */
add_action('woocommerce_cart_calculate_fees', function (WC_Cart $cart): void {
    if (is_admin() && ! defined('DOING_AJAX')) {
        return;
    }
    if (! WC()->session || (string) WC()->session->get('zvij_code_status') !== 'referral') {
        return;
    }

    $percent = zvij_referral_friend_percent();
    if ($percent <= 0) {
        return;
    }

    $discount = round(((float) $cart->get_cart_contents_total()) * $percent / 100, 2);
    if ($discount <= 0) {
        return;
    }

    $cart->add_fee(__('Zvij koda — popust za prvo naročilo', 'zvij-core'), -$discount, false);
}, 9);

/** Ob oddaji naročila shrani referral podatke na naročilo. */
add_action('woocommerce_checkout_order_processed', function (int $order_id): void {
    if (! WC()->session) {
        return;
    }

    $order = wc_get_order($order_id);
    if (! $order instanceof WC_Order) {
        return;
    }

    $status = (string) WC()->session->get('zvij_code_status');
    $code = (string) WC()->session->get('zvij_code_input');

    if ($status === 'referral') {
        $owner_email = sanitize_email((string) WC()->session->get('zvij_code_referral_owner'));
        if ($owner_email !== '' && $owner_email !== sanitize_email($order->get_billing_email())) {
            $order->update_meta_data('_zvij_referral_code', $code);
            $order->update_meta_data('_zvij_referral_owner', $owner_email);
            $order->save();
            $order->add_order_note(sprintf('Zvij koda %s (lastnik %s): popust za prvo naročilo uporabljen.', $code, $owner_email));
        }
    }

    WC()->session->set('zvij_code_input', '');
    WC()->session->set('zvij_code_status', '');
    WC()->session->set('zvij_code_member_email', '');
    WC()->session->set('zvij_code_referral_owner', '');
}, 15);

/** Nagrada lastniku kode, ko prijateljevo naročilo doseže plačan status. */
add_action('woocommerce_order_status_changed', function ($order_id, $from, $to, $order): void {
    if (! $order instanceof WC_Order || ! function_exists('zvij_invoice_statuses')) {
        return;
    }
    if (! in_array($to, zvij_invoice_statuses(), true)) {
        return;
    }
    if ($order->get_meta('_zvij_referral_credited') !== '') {
        return;
    }

    $owner_email = sanitize_email((string) $order->get_meta('_zvij_referral_owner'));
    if ($owner_email === '' || $owner_email === sanitize_email($order->get_billing_email())) {
        return;
    }
    if (! zvij_membership_find_by_email($owner_email)) {
        return;
    }

    $kristali = zvij_referral_owner_kristali();
    if ($kristali <= 0) {
        return;
    }

    zvij_credit_add($owner_email, $kristali, 'referral', (int) $order->get_id(), 'Zvij koda: prijateljevo naročilo #' . $order->get_id());
    $order->update_meta_data('_zvij_referral_credited', (string) $kristali);
    $order->save();
    $order->add_order_note(sprintf('Zvij koda: lastniku %s pripisano %s.', $owner_email, zvij_kristali_izpis($kristali)));
}, 35, 4);

/** Storno nagrade ob preklicu prijateljevega naročila. */
add_action('woocommerce_order_status_changed', function ($order_id, $from, $to, $order): void {
    if (! $order instanceof WC_Order) {
        return;
    }
    if (! in_array($to, ['cancelled', 'refunded', 'failed'], true)) {
        return;
    }

    $credited = (int) $order->get_meta('_zvij_referral_credited');
    if ($credited <= 0 || $order->get_meta('_zvij_referral_credit_reversed') !== '') {
        return;
    }

    $owner_email = sanitize_email((string) $order->get_meta('_zvij_referral_owner'));
    if ($owner_email === '') {
        return;
    }

    zvij_credit_add($owner_email, -$credited, 'adjust', (int) $order->get_id(), 'Storno Zvij koda nagrade, naročilo #' . $order_id . ' → ' . $to);
    $order->update_meta_data('_zvij_referral_credit_reversed', '1');
    $order->save();
}, 35, 4);

/** Zvij koda v Moj račun (pod kristali). */
add_action('woocommerce_account_dashboard', function (): void {
    $user = wp_get_current_user();
    $email = sanitize_email((string) $user->user_email);
    if ($email === '' || ! zvij_membership_find_by_email($email)) {
        return;
    }

    $code = zvij_referral_ensure_code($email);
    if ($code === '') {
        return;
    }
    ?>
    <section class="zvij-code-account">
      <h3><?php esc_html_e('Tvoja Zvij koda', 'zvij-core'); ?></h3>
      <p>
        <strong style="font-size:1.2em;letter-spacing:0.06em;"><?php echo esc_html($code); ?></strong><br>
        <?php echo esc_html(sprintf(
            __('Povabi prijatelja: ob vpisu kode na blagajni prijatelj dobi %1$s %% popusta na prvo naročilo, ti pa %2$s, ko je njegovo naročilo plačano.', 'zvij-core'),
            rtrim(rtrim(number_format(zvij_referral_friend_percent(), 1, ',', ''), '0'), ','),
            zvij_kristali_izpis(zvij_referral_owner_kristali())
        )); ?>
      </p>
    </section>
    <?php
}, 15);
