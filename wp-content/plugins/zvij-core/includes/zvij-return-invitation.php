<?php
/** Explicitly curated old-customer invitation, separate from newsletter consent. */
if (! defined('ABSPATH')) { exit; }

function zvij_return_invitation_subject(): string {
    return 'Malo smo zabluzili ☝️😤 Zdaj je stran še jača.';
}
function zvij_return_invitation_body(string $code): string {
    return "Živjo,\n\nste naši najbolj zvesti člani, ki ste z nami že od začetka. ❤️\n\nPrejšnji mesec stran ni pravilno delovala in se ni dalo naročiti. Se opravičujemo. Priznamo, bili smo preveč spohani, da bi to opazili ☝️😤\n\nSmo pa zato ekspresno prenovili stran in je zdaj še jača.\n\nDodali smo te kot obstoječega člana, s čimer ob vsakem nakupu že zbiraš kristale na dnu svojega grinderja. Uporabiš jih za ugodnejši naslednji nakup ali pa jih šparaš za kriziča. 😏\n\n100 kristalov = 1 €\n\nZa naslednji nakup ti damo še 10 % popusta s kodo {$code}.\nKoda velja 30 dni, enkrat, samo za tvoj e-poštni naslov.\n\nTukaj si nastavi geslo in si noter: " . wc_lostpassword_url() . "\nVpiši isti e-poštni naslov, na katerega si dobil to sporočilo. Če geslo že imaš, se samo prijavi: " . wc_get_page_permalink('myaccount') . "\n\nHvala, ker si z nami.\n\nZvij.si\nZvijače za zvijače.\n\nTo je enkratno povabilo ob prenovi. Na novice te nismo samodejno prijavili. Za pomoč nam odgovori na to sporočilo.\n";
}
function zvij_return_invitation_coupon(int $id): string {
    $old = (string) get_user_meta($id, '_zvij_return_coupon', true);
    if ($old !== '') { return $old; }
    $user = get_userdata($id);
    $coupon = new WC_Coupon();
    $code = 'NAZAJ-' . strtoupper(wp_generate_password(10, false, false));
    $coupon->set_code($code);
    $coupon->set_discount_type('percent');
    $coupon->set_amount(10);
    $coupon->set_individual_use(true);
    $coupon->set_usage_limit(1);
    $coupon->set_usage_limit_per_user(1);
    $coupon->set_email_restrictions([$user->user_email]);
    $coupon->set_date_expires(time() + 30 * DAY_IN_SECONDS);
    $coupon->set_description('Enkratno povabilo starih kupcev ob prenovi.');
    $coupon->save();
    update_user_meta($id, '_zvij_return_coupon', $code);
    return $code;
}
add_action('admin_menu', function (): void {
    add_submenu_page('zvij-dashboard', 'Povabilo starim članom', 'Povabilo starim članom', 'manage_options', 'zvij-return-invitation', 'zvij_return_invitation_page');
});
function zvij_return_invitation_page(): void {
    if (! current_user_can('manage_options')) { return; }
    $users = get_users(['meta_key' => '_zvij_legacy_invitee', 'meta_value' => 'yes']);
    echo '<div class="wrap"><h1>Povabilo starim članom</h1><p>Seznam starih kupcev še ni obnovljen iz prejšnje baze. Trenutni uporabniki niso samodejno vključeni. Dodaj samo preverjene stare kupce. Uvoz in povabilo ne pomenita prijave na novice.</p>';
    if (isset($_GET['processed'])) { echo '<div class="notice notice-info"><p>Obdelava zaključena. Stanje preveri spodaj. Uspeh pomeni predajo e-poštnemu sistemu, ne potrjene dostave v predal.</p></div>'; }
    echo '<h2>' . esc_html(zvij_return_invitation_subject()) . '</h2><pre style="white-space:pre-wrap;max-width:760px;background:#fff;padding:20px">' . esc_html(zvij_return_invitation_body('[OSEBNA KODA]')) . '</pre>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    wp_nonce_field('zvij_return_invitation');
    echo '<input type="hidden" name="action" value="zvij_return_invitation"><h2>1. Test sporočila</h2><p><label>Tvoj naslov <input required type="email" name="test_email" value="' . esc_attr(wp_get_current_user()->user_email) . '"></label> <button class="button" name="operation" value="test">Pošlji test samo meni</button></p><p>Test vsebuje oznako TEST-KODA in ne ustvari popusta.</p></form>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">'; wp_nonce_field('zvij_return_invitation');
    echo '<input type="hidden" name="action" value="zvij_return_invitation"><h2>2. Preverjeni stari kupci</h2><textarea name="emails" rows="5" cols="65" placeholder="En e-poštni naslov na vrstico" required></textarea><p><label><input type="checkbox" name="verified" value="1" required> Preveril sem, da so to stari kupci, ki jih želim povabiti.</label></p><button class="button" name="operation" value="prepare">Pripravi račune, še ne pošlji povabila</button><p>Obstoječih gesel ne spreminjamo. Novi računi prejmejo standardno e-pošto za nastavitev gesla.</p></form>';
    echo '<h2>3. Prejemniki (' . count($users) . ')</h2><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">'; wp_nonce_field('zvij_return_invitation');
    echo '<input type="hidden" name="action" value="zvij_return_invitation"><table class="widefat striped"><thead><tr><th>Izberi</th><th>E-pošta</th><th>Povabilo</th></tr></thead><tbody>';
    foreach ($users as $user) {
        $sent = get_user_meta($user->ID, '_zvij_return_sent', true);
        echo '<tr><td><input type="checkbox" name="recipients[]" value="' . esc_attr((string) $user->ID) . '"' . ($sent ? ' disabled' : '') . '></td><td>' . esc_html($user->user_email) . '</td><td>' . esc_html($sent ?: get_user_meta($user->ID, '_zvij_return_error', true) ?: 'Ni poslano') . '</td></tr>';
    }
    echo '</tbody></table><p><label><input type="checkbox" name="confirmed" value="1" required> Potrjujem pošiljanje izbranim prejemnikom.</label></p><button class="button button-primary" name="operation" value="send">Pošlji izbranim (največ 20 naenkrat)</button></form></div>';
}
add_action('admin_post_zvij_return_invitation', function (): void {
    if (! current_user_can('manage_options')) { wp_die('Dostop ni dovoljen.'); }
    check_admin_referer('zvij_return_invitation');
    if (! add_option('zvij_return_invitation_lock', time(), '', false)) { wp_die('Pošiljanje že poteka.'); }
    try {
        $operation = sanitize_key($_POST['operation'] ?? '');
        if ($operation === 'test') {
            $email = sanitize_email(wp_unslash($_POST['test_email'] ?? ''));
            if (! is_email($email) || ! wp_mail($email, '[TEST] ' . zvij_return_invitation_subject(), zvij_return_invitation_body('TEST-KODA'), ['Content-Type: text/plain; charset=UTF-8'])) { throw new Exception('Test ni bil poslan.'); }
        } elseif ($operation === 'prepare' && ! empty($_POST['verified'])) {
            $emails = array_unique(array_filter(preg_split('/[\s,;]+/', wp_unslash($_POST['emails'] ?? ''))));
            if (count($emails) > 20) { throw new Exception('Pripravi največ 20 naslovov naenkrat.'); }
            foreach ($emails as $email) { if (! is_email($email)) { throw new Exception('Neveljaven e-poštni naslov.'); } }
            WC()->mailer();
            foreach ($emails as $email) {
                $id = zvij_membership_ensure_customer($email);
                if (is_wp_error($id)) { throw new Exception('Računa ni mogoče pripraviti: ' . $email); }
                update_user_meta($id, '_zvij_legacy_invitee', 'yes');
            }
        } elseif ($operation === 'send' && ! empty($_POST['confirmed'])) {
            $ids = array_unique(array_map('absint', (array) ($_POST['recipients'] ?? [])));
            if (count($ids) > 20) { throw new Exception('Izberi največ 20 prejemnikov.'); }
            foreach ($ids as $id) {
                if (get_user_meta($id, '_zvij_legacy_invitee', true) !== 'yes' || get_user_meta($id, '_zvij_return_sent', true)) { continue; }
                $user = get_userdata($id); if (! $user) { continue; }
                $code = zvij_return_invitation_coupon($id);
                $ok = wp_mail($user->user_email, zvij_return_invitation_subject(), zvij_return_invitation_body($code), ['Content-Type: text/plain; charset=UTF-8']);
                update_user_meta($id, $ok ? '_zvij_return_sent' : '_zvij_return_error', $ok ? current_time('mysql') : 'Napaka pošiljanja, poskusi ponovno.');
            }
        } else { throw new Exception('Potrdi dejanje.'); }
    } catch (Throwable $error) {
        delete_option('zvij_return_invitation_lock');
        wp_die(esc_html($error->getMessage()));
    }
    delete_option('zvij_return_invitation_lock');
    wp_safe_redirect(admin_url('admin.php?page=zvij-return-invitation&processed=1')); exit;
});
