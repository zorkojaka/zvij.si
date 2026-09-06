<?php
/**
 * Zvij.si kristali (dobroimetje).
 *
 * Model cen in kristalov je opisan v docs/CENE_IN_KRISTALI.md — tam je edini
 * opis; strategija in zgodovina odlocitev sta v docs/DOBROIMETJE_STRATEGY.md.
 *
 * ENOTA je en kos izdelka. Valuta so KRISTALI (cela števila), en kristal je
 * en cent — 100 kristalov = 1 €. Tečaj je na enem mestu
 * (ZVIJ_KRISTALI_PER_EUR); nikjer drugje ni vpisan.
 *
 * Koliko kristalov da izdelek, določa PRAVILO iz cene, ne ročno vpisana
 * številka. Podrobno pri zvij_credit_reward_percent(). Posledica: ob novem
 * izdelku ni treba ničesar vpisovati, ob spremembi tečaja ali radodarnosti
 * pa se popravi ena nastavitev namesto vseh izdelkov.
 *
 * - Pripis: ko naročilo preide v plačan status (isti kriterij kot računi,
 *   zvij_invoice_statuses), član (vrstica v zvij_members po billing emailu)
 *   prejme vsoto kristalov po postavkah — kristali za kos krat količina.
 * - Poraba: na blagajni checkbox "Uporabi kristale" → negativni fee do
 *   vrednosti izdelkov (dostava se vedno plača). Na voljo prijavljenim
 *   članom, gostom pa po vpisu svoje Zvij kode (glej zvij-referral.php).
 * - Ledger: vsaka sprememba je vrstica (earn/redeem/refund/adjust/referral/
 *   expire), stanje je vsota. Storno ob preklicu/vračilu v obe smeri.
 * - Rok trajanja: kristali ugasnejo po 12 mesecih brez aktivnosti (dnevni
 *   WP-cron); mesece določa opcija `zvij_kristali_expiry_months`.
 * - Samo store credit: brez izplačil, brez prenosa med člani.
 */

if (! defined('ABSPATH')) {
    exit;
}

const ZVIJ_CREDIT_LEDGER_VERSION_OPTION = 'zvij_credit_db_version';
const ZVIJ_KRISTALI_PER_EUR = 100;

function zvij_credit_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'zvij_credit_ledger';
}

function zvij_credit_install(): void {
    global $wpdb;

    if ((string) get_option(ZVIJ_CREDIT_LEDGER_VERSION_OPTION, '') === ZVIJ_CORE_VERSION) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset = $wpdb->get_charset_collate();
    $table = zvij_credit_table();

    dbDelta(
        "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            member_email varchar(190) NOT NULL,
            order_id bigint(20) unsigned NULL,
            amount decimal(10,2) NOT NULL,
            type varchar(30) NOT NULL DEFAULT 'adjust',
            note varchar(190) NOT NULL DEFAULT '',
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY member_email (member_email),
            KEY order_id (order_id)
        ) {$charset};"
    );

    update_option(ZVIJ_CREDIT_LEDGER_VERSION_OPTION, ZVIJ_CORE_VERSION, false);

    if (! wp_next_scheduled('zvij_kristali_expiry_daily')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'zvij_kristali_expiry_daily');
    }
}
add_action('plugins_loaded', 'zvij_credit_install', 11);

/** Sklanjanje: 1 kristal, 2 kristala, 3–4 kristali, 5+ kristalov. */
function zvij_kristali_beseda(int $n): string {
    $mod100 = abs($n) % 100;
    if ($mod100 === 1) {
        return 'kristal';
    }
    if ($mod100 === 2) {
        return 'kristala';
    }
    if ($mod100 === 3 || $mod100 === 4) {
        return 'kristali';
    }
    return 'kristalov';
}

function zvij_kristali_izpis(int $n): string {
    return $n . ' ' . zvij_kristali_beseda($n);
}

function zvij_kristali_eur(int $kristali): float {
    return round($kristali / ZVIJ_KRISTALI_PER_EUR, 2);
}

function zvij_credit_format_eur(float $amount): string {
    return number_format($amount, 2, ',', '.');
}

/** Stanje člana v kristalih (celo število). */
function zvij_credit_balance(string $email): int {
    global $wpdb;
    $email = sanitize_email($email);
    if ($email === '') {
        return 0;
    }
    $table = zvij_credit_table();
    return (int) round((float) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount), 0) FROM {$table} WHERE member_email = %s", $email)));
}

function zvij_credit_add(string $email, int $kristali, string $type, ?int $order_id = null, string $note = ''): bool {
    global $wpdb;
    $email = sanitize_email($email);
    if ($email === '' || $kristali === 0) {
        return false;
    }

    return (bool) $wpdb->insert(zvij_credit_table(), [
        'member_email' => $email,
        'order_id' => $order_id,
        'amount' => $kristali,
        'type' => sanitize_key($type),
        'note' => sanitize_text_field($note),
        'created_at' => current_time('mysql'),
    ]);
}

function zvij_credit_recent(string $email, int $limit = 5): array {
    global $wpdb;
    $table = zvij_credit_table();
    return (array) $wpdb->get_results(
        $wpdb->prepare("SELECT amount, type, order_id, note, created_at FROM {$table} WHERE member_email = %s ORDER BY id DESC LIMIT %d", sanitize_email($email), $limit),
        ARRAY_A
    );
}

/**
 * Delež cene, ki se kupcu vrne v kristalih (v odstotkih).
 *
 * Tri ravni, od splosne k posebni:
 *
 *   1. privzeto pravilo    — opcija `zvij_credit_reward_percent` (10 %)
 *   2. pravilo kategorije  — opcija `zvij_credit_reward_by_cat` (npr. rizle 5 %)
 *   3. izjema na izdelku   — meta `_zvij_kristali`, absolutno stevilo
 *
 * Ob novem izdelku torej ni treba nikjer vpisovati stevilk, ob spremembi
 * tecaja ali radodarnosti pa se ne popravlja 24 mest, ampak eno.
 */
function zvij_credit_reward_percent(?WC_Product $product = null): float {
    $percent = (float) get_option('zvij_credit_reward_percent', 10);

    // Raven kategorije: skupine izdelkov z drugacno razdajalnostjo (rizle so
    // tanjsi izdelek kot filtri ali vrsicki) so pravilo, ne izjema na vsakem
    // izdelku posebej. Opcija: ['rizle' => 5, 'rolce' => 5].
    if ($product instanceof WC_Product) {
        $by_cat = (array) get_option('zvij_credit_reward_by_cat', []);
        if ($by_cat !== []) {
            $id    = $product->get_parent_id() ?: $product->get_id();
            $slugs = wp_get_post_terms($id, 'product_cat', ['fields' => 'slugs']);
            if (! is_wp_error($slugs)) {
                foreach ($slugs as $slug) {
                    if (isset($by_cat[$slug])) {
                        $percent = (float) $by_cat[$slug];
                        break;
                    }
                }
            }
        }
    }

    return (float) apply_filters('zvij_credit_reward_percent', $percent, $product);
}

/**
 * Kristali za EN KOS izdelka ali variacije.
 *
 * Vrstni red: izjema na izdelku (`_zvij_kristali`) → sicer pravilo iz cene.
 * Variacija ima prednost pred nadrejenim izdelkom.
 *
 * Meta `_zvij_kristali` je IZJEMA, ne privzeti način vpisa. Uporabi jo samo
 * takrat, kadar izdelek namenoma odstopa od pravila; sicer jo pusti prazno,
 * da vrednost sledi ceni.
 */
function zvij_credit_product_kristali(WC_Product $product): int {
    foreach ([$product->get_id(), $product->get_parent_id()] as $id) {
        if (! $id) {
            continue;
        }
        $override = get_post_meta($id, '_zvij_kristali', true);
        if ($override !== '' && is_numeric($override)) {
            return max(0, (int) $override);
        }
    }

    return zvij_credit_kristali_from_price($product);
}

/** Kristali po pravilu: delež veljavne cene, pretvorjen v kristale. */
function zvij_credit_kristali_from_price(WC_Product $product): int {
    $price = (float) $product->get_price();
    if ($price <= 0) {
        return 0;
    }

    return (int) round($price * zvij_credit_reward_percent($product) / 100 * ZVIJ_KRISTALI_PER_EUR);
}

/** Ali ima izdelek vpisano izjemo namesto pravila. */
function zvij_credit_has_override(WC_Product $product): bool {
    foreach ([$product->get_id(), $product->get_parent_id()] as $id) {
        if ($id && get_post_meta($id, '_zvij_kristali', true) !== '') {
            return true;
        }
    }

    return false;
}

/**
 * Za kaj so kristali dobri — besedilo sledi temu, ali je Reload na strani.
 * Brez tega bi stran obljubljala »naslednji reload«, ki ga ni nikjer videti.
 */
function zvij_credit_next_label(): string {
    return (function_exists('zvij_reload_is_public') && zvij_reload_is_public())
        ? __('naslednji reload', 'zvij-core')
        : __('naslednji nakup', 'zvij-core');
}

/**
 * Javni napis o kristalih za izdelek, izpeljan iz zive vrednosti.
 *
 * Prej je bil ta stavek shranjen v meta `_zvij_dobroimetje_note` z vpisano
 * stevilko. To pomeni isto dejstvo na dveh mestih — in ob spremembi tecaja
 * 4. 9. 2026 sta se razsla: sistem je pripisoval 130 kristalov, na strani
 * pa je pisalo 13. Zdaj se napis vedno izracuna.
 */
function zvij_credit_public_note(WC_Product $product): string {
    $kristali = zvij_credit_product_kristali($product);
    if ($kristali <= 0) {
        return '';
    }

    return sprintf(
        /* translators: %s: kolicina kristalov z besedo */
        __('Član prejme %1$s za %2$s.', 'zvij-core'),
        zvij_kristali_izpis($kristali),
        zvij_credit_next_label()
    );
}

function zvij_credit_order_earnable(WC_Order $order): int {
    $total = 0;
    foreach ($order->get_items() as $item) {
        if (! $item instanceof WC_Order_Item_Product) {
            continue;
        }
        $product = $item->get_product();
        if (! $product instanceof WC_Product) {
            continue;
        }
        $total += zvij_credit_product_kristali($product) * max(1, (int) $item->get_quantity());
    }

    return $total;
}

/**
 * Pripis ob prehodu v plačan status. Idempotentno prek order meta.
 */
function zvij_credit_earn_on_paid($order_id, $from, $to, $order): void {
    if (! $order instanceof WC_Order || ! function_exists('zvij_invoice_statuses')) {
        return;
    }
    if (! in_array($to, zvij_invoice_statuses(), true)) {
        return;
    }
    if ($order->get_meta('_zvij_credit_earned') !== '') {
        return;
    }

    $email = sanitize_email($order->get_billing_email());
    if ($email === '' || ! zvij_membership_find_by_email($email)) {
        return;
    }

    $kristali = zvij_credit_order_earnable($order);
    if ($kristali <= 0) {
        return;
    }

    zvij_credit_add($email, $kristali, 'earn', (int) $order->get_id(), 'Pripis ob naročilu #' . $order->get_id());
    $order->update_meta_data('_zvij_credit_earned', (string) $kristali);
    $order->save();
    $order->add_order_note(sprintf('Kristali: članu pripisano %s.', zvij_kristali_izpis($kristali)));
}
add_action('woocommerce_order_status_changed', 'zvij_credit_earn_on_paid', 30, 4);

/**
 * Storno ob preklicu/vračilu: obrne pripis in vrne porabljene kristale.
 */
function zvij_credit_reverse_on_cancel($order_id, $from, $to, $order): void {
    if (! $order instanceof WC_Order) {
        return;
    }
    if (! in_array($to, ['cancelled', 'refunded', 'failed'], true)) {
        return;
    }

    $email = sanitize_email($order->get_billing_email());

    $earned = (int) $order->get_meta('_zvij_credit_earned');
    if ($earned > 0 && $order->get_meta('_zvij_credit_earn_reversed') === '') {
        zvij_credit_add($email, -$earned, 'adjust', (int) $order->get_id(), 'Storno pripisa, naročilo #' . $order->get_id() . ' → ' . $to);
        $order->update_meta_data('_zvij_credit_earn_reversed', '1');
    }

    $redeemed = (int) $order->get_meta('_zvij_credit_redeemed');
    if ($redeemed > 0 && $order->get_meta('_zvij_credit_redeem_restored') === '') {
        zvij_credit_add($email, $redeemed, 'refund', (int) $order->get_id(), 'Vračilo porabe, naročilo #' . $order->get_id() . ' → ' . $to);
        $order->update_meta_data('_zvij_credit_redeem_restored', '1');
    }

    $order->save();
}
add_action('woocommerce_order_status_changed', 'zvij_credit_reverse_on_cancel', 30, 4);

/**
 * Email člana, ki sme unovčevati na tej blagajni: prijavljen uporabnik ali
 * gost, ki je vpisal svojo Zvij kodo (session postavi zvij-referral.php).
 */
function zvij_credit_checkout_email(): string {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        return sanitize_email((string) $user->user_email);
    }

    if (function_exists('zvij_referral_session_member_email')) {
        return zvij_referral_session_member_email();
    }

    return '';
}

function zvij_credit_available_for_checkout(): int {
    $email = zvij_credit_checkout_email();
    if ($email === '' || ! zvij_membership_find_by_email($email)) {
        return 0;
    }
    return max(0, zvij_credit_balance($email));
}

/** Checkbox za porabo kristalov — znotraj payment fragmenta (glej opombo
 * pri Zvij koda polju v zvij-referral.php). */
add_action('woocommerce_review_order_before_submit', function (): void {
    if (! WC()->cart || WC()->cart->is_empty()) {
        return;
    }

    $available = zvij_credit_available_for_checkout();
    if ($available <= 0) {
        return;
    }

    $requested = WC()->session ? max(0, (int) WC()->session->get('zvij_use_kristali', 0)) : 0;
    ?>
    <div class="zvij-credit-toggle">
      <label for="zvij_use_kristali"><?php echo esc_html(sprintf(__('Uporabi kristale — na voljo %1$s (= %2$s €)', 'zvij-core'), zvij_kristali_izpis($available), zvij_credit_format_eur(zvij_kristali_eur($available)))); ?></label>
      <span class="zvij-credit-toggle__row">
        <input type="number" id="zvij_use_kristali" name="zvij_use_kristali" inputmode="numeric" min="0" max="<?php echo esc_attr((string) $available); ?>" step="1" value="<?php echo esc_attr((string) ($requested > 0 ? min($requested, $available) : 0)); ?>" style="width:6.5em;">
        <button type="button" class="button zvij-credit-toggle__all" data-all="<?php echo esc_attr((string) $available); ?>"><?php esc_html_e('Uporabi vse', 'zvij-core'); ?></button>
      </span>
      <small><?php esc_html_e('100 kristalov = 1 € popusta. Kristali krijejo izdelke, dostava se plača.', 'zvij-core'); ?></small>
    </div>
    <script>
      jQuery(function ($) {
        var t;
        $(document.body).on('input change', '#zvij_use_kristali', function () {
          clearTimeout(t);
          t = setTimeout(function () { $(document.body).trigger('update_checkout'); }, 700);
        });
        $(document.body).on('click', '.zvij-credit-toggle__all', function () {
          $('#zvij_use_kristali').val($(this).data('all'));
          $(document.body).trigger('update_checkout');
        });
      });
    </script>
    <?php
});

/** Ob AJAX osvežitvi blagajne preberi želeno količino iz serializiranih podatkov. */
add_action('woocommerce_checkout_update_order_review', function ($post_data): void {
    if (! WC()->session) {
        return;
    }
    parse_str((string) $post_data, $data);
    if (array_key_exists('zvij_use_kristali', $data)) {
        WC()->session->set('zvij_use_kristali', max(0, (int) $data['zvij_use_kristali']));
    }
});

/**
 * Dejanska poraba: želena količina, navzgor omejena s stanjem in z
 * vrednostjo izdelkov (dostava se vedno plača).
 */
function zvij_credit_checkout_spend(WC_Cart $cart): int {
    $requested = WC()->session ? max(0, (int) WC()->session->get('zvij_use_kristali', 0)) : 0;
    if ($requested <= 0) {
        return 0;
    }
    $available = zvij_credit_available_for_checkout();
    if ($available <= 0) {
        return 0;
    }
    $cap_kristali = (int) floor(((float) $cart->get_cart_contents_total()) * ZVIJ_KRISTALI_PER_EUR);
    return max(0, min($requested, $available, $cap_kristali));
}

add_action('woocommerce_cart_calculate_fees', function (WC_Cart $cart): void {
    if (is_admin() && ! defined('DOING_AJAX')) {
        return;
    }
    if (! WC()->session) {
        return;
    }

    $kristali = zvij_credit_checkout_spend($cart);
    if ($kristali <= 0) {
        return;
    }

    $cart->add_fee(__('Kristali', 'zvij-core'), -zvij_kristali_eur($kristali), false);
});

/** Ob oddaji naročila zabeleži porabo in počisti sejo. */
add_action('woocommerce_checkout_order_processed', function (int $order_id): void {
    $order = wc_get_order($order_id);
    if (! $order instanceof WC_Order) {
        return;
    }

    $redeemed_eur = 0.0;
    foreach ($order->get_fees() as $fee) {
        if ($fee->get_name() === __('Kristali', 'zvij-core') && (float) $fee->get_total() < 0) {
            $redeemed_eur += -(float) $fee->get_total();
        }
    }

    if ($redeemed_eur <= 0) {
        return;
    }

    $kristali = (int) round($redeemed_eur * ZVIJ_KRISTALI_PER_EUR);

    $email = zvij_credit_checkout_email();
    if ($email === '') {
        $email = sanitize_email($order->get_billing_email());
    }

    // Zaščita pred dvojno porabo (dva sočasna checkouta z istim stanjem):
    // poraba nikoli ne preseže trenutnega stanja; razliko zabeležimo na naročilo.
    $balance = zvij_credit_balance($email);
    if ($kristali > $balance) {
        $order->add_order_note(sprintf(
            'Kristali: zahtevana poraba %d presega stanje %d — knjiženo samo razpoložljivo (preveri popust na naročilu).',
            $kristali,
            max(0, $balance)
        ));
        $kristali = max(0, $balance);
        if ($kristali === 0) {
            $order->save();
            return;
        }
    }

    zvij_credit_add($email, -$kristali, 'redeem', $order_id, 'Poraba pri naročilu #' . $order_id);
    $order->update_meta_data('_zvij_credit_redeemed', (string) $kristali);
    $order->save();
    $order->add_order_note(sprintf('Kristali: porabljeno %s.', zvij_kristali_izpis($kristali)));

    if (WC()->session) {
        WC()->session->set('zvij_use_kristali', 0);
    }
}, 20);

/** Stanje in zadnje transakcije v Moj račun. */
add_action('woocommerce_account_dashboard', function (): void {
    $user = wp_get_current_user();
    $email = sanitize_email((string) $user->user_email);
    if ($email === '' || ! zvij_membership_find_by_email($email)) {
        return;
    }

    $balance = zvij_credit_balance($email);
    $recent = zvij_credit_recent($email, 5);
    $months = zvij_kristali_expiry_months();
    ?>
    <section class="zvij-credit-account">
      <h3><?php esc_html_e('Kristali', 'zvij-core'); ?></h3>
      <p class="zvij-credit-account__balance">
        <strong><?php echo esc_html(zvij_kristali_izpis($balance)); ?></strong>
        (<?php echo esc_html(zvij_credit_format_eur(zvij_kristali_eur($balance))); ?> €)
        — <?php esc_html_e('unovčiš jih na blagajni kot popust.', 'zvij-core'); ?>
        <?php echo esc_html(sprintf(__('Veljajo %d mesecev od zadnje aktivnosti.', 'zvij-core'), $months)); ?>
      </p>
      <?php if ($recent !== []) : ?>
        <table class="woocommerce-table shop_table shop_table_responsive">
          <thead><tr><th><?php esc_html_e('Datum', 'zvij-core'); ?></th><th><?php esc_html_e('Opis', 'zvij-core'); ?></th><th><?php esc_html_e('Kristali', 'zvij-core'); ?></th></tr></thead>
          <tbody>
          <?php foreach ($recent as $row) : ?>
            <tr>
              <td data-title="<?php esc_attr_e('Datum', 'zvij-core'); ?>"><?php echo esc_html(mysql2date('j. n. Y', (string) $row['created_at'])); ?></td>
              <td data-title="<?php esc_attr_e('Opis', 'zvij-core'); ?>"><?php echo esc_html((string) $row['note']); ?></td>
              <td data-title="<?php esc_attr_e('Kristali', 'zvij-core'); ?>"><?php echo esc_html(($row['amount'] >= 0 ? '+' : '') . (int) $row['amount']); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
    <?php
});

/** Obvestilo o pripisu v emailu kupcu in na strani »naročilo prejeto«. */
add_action('woocommerce_email_after_order_table', function ($order): void {
    if (! $order instanceof WC_Order) {
        return;
    }
    $earned = (int) $order->get_meta('_zvij_credit_earned');
    if ($earned > 0) {
        echo '<p style="margin:12px 0;">' . esc_html(sprintf(__('Kristali: za ta nakup ti pripišemo %1$s za %2$s.', 'zvij-core'), zvij_kristali_izpis($earned), zvij_credit_next_label())) . '</p>';
    }
}, 20);

add_action('woocommerce_thankyou', function ($order_id): void {
    $order = wc_get_order($order_id);
    if (! $order instanceof WC_Order) {
        return;
    }

    $earnable = zvij_credit_order_earnable($order);
    if ($earnable <= 0) {
        return;
    }

    $email = sanitize_email($order->get_billing_email());
    $is_member = $email !== '' && zvij_membership_find_by_email($email);

    if ($is_member) {
        $message = sprintf(__('Član prejme %1$s za %2$s — pripišejo se, ko je naročilo plačano.', 'zvij-core'), zvij_kristali_izpis($earnable), zvij_credit_next_label());
    } else {
        // kristale zbirajo samo člani — nečlanu povemo, kaj zamuja
        $message = sprintf(__('S tem nakupom bi kot Član Zvij.si prejel %1$s za %2$s. Včlani se (obrazec na dnu strani) — kristale zbiraš pri prihodnjih nakupih.', 'zvij-core'), zvij_kristali_izpis($earnable), zvij_credit_next_label());
    }

    echo '<div class="zvij-credit-thankyou" style="margin:1rem 0;padding:0.9rem 1.1rem;border:1px solid rgba(199,177,148,0.58);border-radius:8px;">'
        . esc_html($message)
        . '</div>';
}, 4);

/** Skupna obveznost iz kristalov (v €) za operativni pregled. */
function zvij_credit_total_outstanding(): float {
    global $wpdb;
    $table = zvij_credit_table();
    $kristali = (int) round((float) $wpdb->get_var("SELECT COALESCE(SUM(amount), 0) FROM {$table}"));
    return zvij_kristali_eur(max(0, $kristali));
}

/** Rok trajanja: meseci brez aktivnosti, po katerih kristali ugasnejo. */
function zvij_kristali_expiry_months(): int {
    return max(1, (int) get_option('zvij_kristali_expiry_months', 12));
}

/**
 * Dnevni cron: članom, ki toliko mesecev niso imeli nobene spremembe
 * (nakupa ali porabe), stanje ugasne z 'expire' vrstico — sled ostane.
 */
function zvij_kristali_expire_stale(): int {
    global $wpdb;
    $table = zvij_credit_table();
    $cutoff = gmdate('Y-m-d H:i:s', strtotime('-' . zvij_kristali_expiry_months() . ' months', current_time('timestamp')));

    $rows = (array) $wpdb->get_results(
        $wpdb->prepare(
            "SELECT member_email, SUM(amount) AS balance, MAX(created_at) AS last_activity
             FROM {$table} GROUP BY member_email
             HAVING balance > 0 AND last_activity < %s",
            $cutoff
        ),
        ARRAY_A
    );

    $expired = 0;
    foreach ($rows as $row) {
        $balance = (int) round((float) $row['balance']);
        if ($balance <= 0) {
            continue;
        }
        zvij_credit_add((string) $row['member_email'], -$balance, 'expire', null, sprintf('Kristali potekli (%d mesecev brez aktivnosti)', zvij_kristali_expiry_months()));
        $expired++;
    }

    return $expired;
}
add_action('zvij_kristali_expiry_daily', 'zvij_kristali_expire_stale');
