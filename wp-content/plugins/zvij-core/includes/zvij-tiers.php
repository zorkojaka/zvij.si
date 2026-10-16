<?php
/**
 * ZVIJ — stopnje članstva in kristali iz marže (Jaka, 16. 10. 2026).
 *
 * Kristali niso več delež KONČNE cene, ampak delež MARŽE (prodajna cena po
 * popustih minus nabavna cena), deljene s članom glede na njegovo stopnjo:
 *
 *   stopnja 1 — 5 % marže, ob včlanitvi
 *   stopnje 2–4 — samodejno po porabi v zadnjih 12 mesecih (do 35 %)
 *   stopnja 5 (VIP) — do 50 %, samodejno pri visoki porabi ali ročno
 *
 * Deleži in pragovi so v opciji `zvij_tiers` (Zvij.si → Stopnje članstva).
 * Ročna stopnja (in po želji lasten delež 35–50 %) in brezplačna poštnina za
 * posameznega člana sta v tabeli zvij_member_tiers (level 0 = stopnja samodejno).
 *
 * Nabavna cena: meta `_zvij_cost` (bruto na kos, kar dejansko plačaš — nismo
 * zavezanci za DDV), na variaciji ali nadrejenem izdelku. Brez nje izdelek ostane
 * na starem pravilu iz cene (zvij-credit.php), da nič ne izgine; seznam takih
 * izdelkov je na strani nastavitev.
 *
 * Vse velja šele, ko je vklopljeno `zvij_tiers_enabled` = yes.
 * Strankam se marža nikoli ne prikaže — vidijo le kristale in stopnjo.
 */

if (! defined('ABSPATH')) {
    exit;
}

const ZVIJ_TIERS_DB_VERSION = '2';

function zvij_tiers_enabled(): bool {
    return get_option('zvij_tiers_enabled') === 'yes';
}

/** @return array<int,array{share:float,min:float}> stopnja => delež marže v % in prag porabe v € */
function zvij_tiers_config(): array {
    $defaults = [
        1 => ['share' => 5, 'min' => 0],
        2 => ['share' => 15, 'min' => 100],
        3 => ['share' => 25, 'min' => 250],
        4 => ['share' => 35, 'min' => 500],
        5 => ['share' => 50, 'min' => 1000],
    ];
    $saved = (array) get_option('zvij_tiers', []);
    foreach ($defaults as $level => $row) {
        if (isset($saved[$level]) && is_array($saved[$level])) {
            $defaults[$level] = [
                'share' => max(0, min(100, (float) ($saved[$level]['share'] ?? $row['share']))),
                'min'   => max(0, (float) ($saved[$level]['min'] ?? $row['min'])),
            ];
        }
    }
    return $defaults;
}

function zvij_tiers_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'zvij_member_tiers';
}

add_action('plugins_loaded', function (): void {
    if (get_option('zvij_tiers_db_version') === ZVIJ_TIERS_DB_VERSION) {
        return;
    }
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta('CREATE TABLE ' . zvij_tiers_table() . " (
        email varchar(190) NOT NULL,
        level tinyint(3) unsigned NOT NULL,
        share decimal(5,2) NULL,
        free_shipping tinyint(1) NOT NULL DEFAULT 0,
        note varchar(190) NOT NULL DEFAULT '',
        updated_at datetime NOT NULL,
        PRIMARY KEY  (email)
    ) {$wpdb->get_charset_collate()};");
    update_option('zvij_tiers_db_version', ZVIJ_TIERS_DB_VERSION, false);
});

/** Nabavna cena na kos; variacija ima prednost pred nadrejenim izdelkom. Null = ni vpisana. */
function zvij_tiers_cost(WC_Product $product): ?float {
    foreach ([$product->get_id(), $product->get_parent_id()] as $id) {
        if (! $id) {
            continue;
        }
        $raw = get_post_meta($id, '_zvij_cost', true);
        if ($raw !== '' && is_numeric($raw)) {
            return max(0.0, (float) $raw);
        }
    }
    return null;
}

/** Poraba (plačana naročila brez dostave) v zadnjih 12 mesecih. */
function zvij_tiers_spend(string $email, int $exclude_order = 0): float {
    $email = sanitize_email($email);
    if ($email === '') {
        return 0.0;
    }
    $orders = wc_get_orders([
        'billing_email' => $email,
        'status'        => function_exists('zvij_invoice_statuses') ? zvij_invoice_statuses() : ['processing', 'completed'],
        'date_created'  => '>' . (time() - YEAR_IN_SECONDS),
        'exclude'       => $exclude_order ? [$exclude_order] : [],
        'limit'         => -1,
    ]);
    $sum = 0.0;
    foreach ($orders as $order) {
        $sum += max(0.0, (float) $order->get_total() - (float) $order->get_shipping_total());
    }
    return $sum;
}

function zvij_tiers_manual(string $email): ?array {
    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . zvij_tiers_table() . ' WHERE email = %s', sanitize_email($email)), ARRAY_A);
    return is_array($row) ? $row : null;
}

/**
 * Stopnja člana: ročna nastavitev ima prednost, sicer prag porabe.
 * Kdor ni član, nima stopnje (0) in ne zbira kristalov.
 *
 * @return array{level:int,share:float,spend:float,manual:bool,next_level:?int,next_min:?float}
 */
function zvij_tiers_for_email(string $email, int $exclude_order = 0): array {
    $config = zvij_tiers_config();
    $email = sanitize_email($email);
    if ($email === '' || ! function_exists('zvij_membership_find_by_email') || ! zvij_membership_find_by_email($email)) {
        return ['level' => 0, 'share' => 0.0, 'spend' => 0.0, 'manual' => false, 'next_level' => null, 'next_min' => null];
    }
    $spend = zvij_tiers_spend($email, $exclude_order);
    $level = 1;
    foreach ($config as $n => $row) {
        if ($spend >= $row['min']) {
            $level = $n;
        }
    }
    $share = $config[$level]['share'];
    $row = zvij_tiers_manual($email);
    $manual = $row && (int) $row['level'] >= 1 && (int) $row['level'] <= 5 ? $row : null;
    if ($manual) {
        $level = (int) $manual['level'];
        $share = $manual['share'] !== null ? (float) $manual['share'] : $config[$level]['share'];
    }
    $next = $level < 5 && ! $manual ? $level + 1 : null;
    return [
        'level'      => $level,
        'share'      => $share,
        'spend'      => $spend,
        'manual'     => (bool) $manual,
        'next_level' => $next,
        'next_min'   => $next ? $config[$next]['min'] : null,
    ];
}

/** Delež za prikaz na strani: prijavljen član vidi svojega, ostali stopnjo 1. */
function zvij_tiers_viewer_share(): float {
    static $share = null;
    if ($share !== null) {
        return $share;
    }
    $user = wp_get_current_user();
    if ($user->exists()) {
        $tier = zvij_tiers_for_email((string) $user->user_email);
        if ($tier['level'] > 0) {
            return $share = $tier['share'];
        }
    }
    return $share = zvij_tiers_config()[1]['share'];
}

/** Kristali iz marže za znesek in količino. */
function zvij_tiers_kristali_from_margin(float $line_total, float $cost, int $qty, float $share): int {
    $margin = max(0.0, $line_total - $cost * max(1, $qty));
    return (int) round($margin * $share / 100 * ZVIJ_KRISTALI_PER_EUR);
}

/** Kristali za en kos pri trenutnem gledalcu, ali null, če izdelek nima nabavne cene. */
function zvij_tiers_unit_kristali(WC_Product $product, ?float $share = null): ?int {
    // Kartica variabilnega izdelka (vršički 1 g / 5 g) pokaže najcenejšo variacijo,
    // tako kot cena »od 7,50 €«; nabavna cena je vpisana na variacijah.
    if ($product->is_type('variable') && zvij_tiers_cost($product) === null) {
        $cheapest = null;
        foreach ($product->get_children() as $child_id) {
            $child = wc_get_product($child_id);
            if ($child && $child->is_purchasable() && ($cheapest === null || (float) $child->get_price() < (float) $cheapest->get_price())) {
                $cheapest = $child;
            }
        }
        return $cheapest ? zvij_tiers_unit_kristali($cheapest, $share) : null;
    }
    $cost = zvij_tiers_cost($product);
    if ($cost === null) {
        return null;
    }
    return zvij_tiers_kristali_from_margin((float) $product->get_price(), $cost, 1, $share ?? zvij_tiers_viewer_share());
}

/**
 * Kristali za naročilo po marži dejansko plačanih postavk (po količinskih in
 * drugih popustih). Postavke brez nabavne cene: staro pravilo iz cene.
 */
function zvij_tiers_order_kristali(WC_Order $order): int {
    $tier = zvij_tiers_for_email((string) $order->get_billing_email(), (int) $order->get_id());
    $total = 0;
    foreach ($order->get_items() as $item) {
        if (! $item instanceof WC_Order_Item_Product || ! ($product = $item->get_product()) instanceof WC_Product) {
            continue;
        }
        $qty = max(1, (int) $item->get_quantity());
        $cost = zvij_tiers_cost($product);
        $total += $cost === null
            ? zvij_credit_legacy_kristali($product) * $qty
            : zvij_tiers_kristali_from_margin((float) $item->get_total(), $cost, $qty, $tier['share']);
    }
    return $total;
}

/* ---------- Nabavna cena v adminu izdelka ---------- */

add_action('woocommerce_product_options_pricing', function (): void {
    woocommerce_wp_text_input([
        'id'          => '_zvij_cost',
        'label'       => __('Nabavna cena (€, bruto na kos)', 'zvij-core'),
        'description' => __('Kar dejansko plačaš dobavitelju za en kos, z DDV dobavitelja. Iz nje se računajo kristali (delež marže). Ni vidna strankam.', 'zvij-core'),
        'desc_tip'    => true,
        'data_type'   => 'price',
    ]);
});

add_action('woocommerce_admin_process_product_object', function (WC_Product $product): void {
    if (! isset($_POST['_zvij_cost'])) { // phpcs:ignore WordPress.Security.NonceVerification
        return;
    }
    $raw = wc_format_decimal(sanitize_text_field(wp_unslash((string) $_POST['_zvij_cost']))); // phpcs:ignore WordPress.Security.NonceVerification
    $raw === '' ? $product->delete_meta_data('_zvij_cost') : $product->update_meta_data('_zvij_cost', $raw);
});

add_action('woocommerce_variation_options_pricing', function ($loop, $data, $variation): void {
    woocommerce_wp_text_input([
        'id'            => "_zvij_cost_{$loop}",
        'name'          => "_zvij_cost[{$loop}]",
        'value'         => get_post_meta($variation->ID, '_zvij_cost', true),
        'label'         => __('Nabavna cena (€, bruto na kos)', 'zvij-core'),
        'placeholder'   => __('kot izdelek', 'zvij-core'),
        'data_type'     => 'price',
        'wrapper_class' => 'form-row form-row-full',
    ]);
}, 10, 3);

add_action('woocommerce_admin_process_variation_object', function ($variation, $i): void {
    if (! isset($_POST['_zvij_cost'][$i])) { // phpcs:ignore WordPress.Security.NonceVerification
        return;
    }
    $raw = wc_format_decimal(sanitize_text_field(wp_unslash((string) $_POST['_zvij_cost'][$i]))); // phpcs:ignore WordPress.Security.NonceVerification
    $raw === '' ? $variation->delete_meta_data('_zvij_cost') : $variation->update_meta_data('_zvij_cost', $raw);
}, 10, 2);

/* ---------- Moj račun: stopnja brez omembe marže ---------- */

add_action('woocommerce_account_dashboard', function (): void {
    if (! zvij_tiers_enabled()) {
        return;
    }
    $tier = zvij_tiers_for_email((string) wp_get_current_user()->user_email);
    if ($tier['level'] < 1) {
        return;
    }
    ?>
    <section class="zvij-tier-account">
      <h3><?php echo esc_html(sprintf(__('Stopnja članstva: %d od 5', 'zvij-core'), $tier['level'])); ?></h3>
      <p>
        <?php if ($tier['next_level']) : ?>
          <?php echo esc_html(sprintf(
              __('Še %1$s € nakupov v zadnjih 12 mesecih do stopnje %2$d — višja stopnja pomeni več kristalov pri vsakem nakupu.', 'zvij-core'),
              number_format_i18n(max(0, $tier['next_min'] - $tier['spend']), 2),
              $tier['next_level']
          )); ?>
        <?php else : ?>
          <?php esc_html_e('Pri vsakem nakupu zbiraš največ kristalov.', 'zvij-core'); ?>
        <?php endif; ?>
      </p>
    </section>
    <?php
}, 5);

/* ---------- Admin: Zvij.si → Stopnje članstva ---------- */

add_action('admin_menu', function (): void {
    add_submenu_page('zvij-dashboard', __('Stopnje članstva', 'zvij-core'), __('Stopnje članstva', 'zvij-core'), 'manage_woocommerce', 'zvij-tiers', 'zvij_tiers_admin_page');
}, 20);

add_action('admin_post_zvij_tiers_save', function (): void {
    if (! current_user_can('manage_woocommerce')) {
        wp_die('Ni dovoljenja.');
    }
    check_admin_referer('zvij_tiers_save');
    $rows = [];
    foreach ((array) ($_POST['tiers'] ?? []) as $level => $row) {
        $rows[(int) $level] = ['share' => (float) wc_format_decimal($row['share'] ?? 0), 'min' => (float) wc_format_decimal($row['min'] ?? 0)];
    }
    update_option('zvij_tiers', $rows, false);
    update_option('zvij_tiers_enabled', empty($_POST['enabled']) ? 'no' : 'yes');
    wp_safe_redirect(admin_url('admin.php?page=zvij-tiers&saved=1'));
    exit;
});

add_action('admin_post_zvij_tiers_member', function (): void {
    global $wpdb;
    if (! current_user_can('manage_woocommerce')) {
        wp_die('Ni dovoljenja.');
    }
    check_admin_referer('zvij_tiers_member');
    $email = sanitize_email(wp_unslash((string) ($_POST['email'] ?? '')));
    $level = max(0, min(5, (int) ($_POST['level'] ?? 0)));
    $share = trim((string) ($_POST['share'] ?? ''));
    $free = empty($_POST['free_shipping']) ? 0 : 1;
    if ($email !== '') {
        if ($level === 0 && ! $free) {
            $wpdb->delete(zvij_tiers_table(), ['email' => $email]);
        } else {
            $wpdb->replace(zvij_tiers_table(), [
                'email' => $email, 'level' => $level,
                'share' => $share === '' || $level === 0 ? null : max(0, min(100, (float) wc_format_decimal($share))),
                'free_shipping' => $free,
                'note' => sanitize_text_field(wp_unslash((string) ($_POST['note'] ?? ''))), 'updated_at' => current_time('mysql'),
            ]);
        }
    }
    wp_safe_redirect(admin_url('admin.php?page=zvij-tiers&saved=1#clani'));
    exit;
});

function zvij_tiers_admin_page(): void {
    global $wpdb;
    $config = zvij_tiers_config();
    $members = $wpdb->get_results('SELECT email, name FROM ' . zvij_membership_table() . " WHERE status <> 'unsubscribed' ORDER BY created_at DESC LIMIT 200", ARRAY_A);
    $missing = [];
    foreach (wc_get_products(['status' => 'publish', 'limit' => -1]) as $product) {
        $ids = $product->is_type('variable') ? $product->get_children() : [$product->get_id()];
        foreach ($ids as $id) {
            $item = wc_get_product($id);
            if ($item && zvij_tiers_cost($item) === null) {
                $missing[] = $item;
            }
        }
    }
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('Stopnje članstva', 'zvij-core'); ?></h1>
      <?php if (isset($_GET['saved'])) : ?><div class="notice notice-success"><p><?php esc_html_e('Shranjeno.', 'zvij-core'); ?></p></div><?php endif; ?>
      <p><?php esc_html_e('Član ob vsakem plačanem naročilu dobi kristale v vrednosti deleža marže (prodajna cena po popustih minus nabavna cena). Stopnja se določi po porabi v zadnjih 12 mesecih (brez dostave) ali ročno. Strankam se marža ne prikaže.', 'zvij-core'); ?></p>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('zvij_tiers_save'); ?>
        <input type="hidden" name="action" value="zvij_tiers_save">
        <p><label><input type="checkbox" name="enabled" value="1" <?php checked(zvij_tiers_enabled()); ?>> <strong><?php esc_html_e('Kristali iz marže in stopnje so vklopljeni', 'zvij-core'); ?></strong></label><br>
        <span class="description"><?php esc_html_e('Izklopljeno = staro pravilo (delež končne cene).', 'zvij-core'); ?></span></p>
        <table class="widefat striped" style="max-width:640px">
          <thead><tr><th><?php esc_html_e('Stopnja', 'zvij-core'); ?></th><th><?php esc_html_e('Delež marže (%)', 'zvij-core'); ?></th><th><?php esc_html_e('Poraba v 12 mesecih od (€)', 'zvij-core'); ?></th></tr></thead>
          <tbody>
          <?php foreach ($config as $level => $row) : ?>
            <tr>
              <td><?php echo esc_html($level === 5 ? '5 (VIP)' : (string) $level); ?></td>
              <td><input type="number" step="0.5" min="0" max="100" name="tiers[<?php echo esc_attr((string) $level); ?>][share]" value="<?php echo esc_attr((string) $row['share']); ?>"></td>
              <td><input type="number" step="1" min="0" name="tiers[<?php echo esc_attr((string) $level); ?>][min]" value="<?php echo esc_attr((string) $row['min']); ?>" <?php echo $level === 1 ? 'readonly' : ''; ?>></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php submit_button(__('Shrani stopnje', 'zvij-core')); ?>
      </form>

      <h2><?php esc_html_e('Izdelki brez nabavne cene', 'zvij-core'); ?></h2>
      <?php if ($missing) : ?>
        <p><?php esc_html_e('Ti izdelki dajejo kristale po starem pravilu (delež cene), dokler ne vpišeš nabavne cene (Uredi izdelek → Splošno → Nabavna cena).', 'zvij-core'); ?></p>
        <ul>
          <?php foreach ($missing as $item) : ?>
            <li><a href="<?php echo esc_url(get_edit_post_link($item->get_parent_id() ?: $item->get_id())); ?>"><?php echo esc_html($item->get_name()); ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php else : ?>
        <p><?php esc_html_e('Vsi objavljeni izdelki imajo nabavno ceno.', 'zvij-core'); ?></p>
      <?php endif; ?>

      <h2 id="clani"><?php esc_html_e('Člani', 'zvij-core'); ?></h2>
      <p class="description"><?php esc_html_e('Ročna stopnja ima prednost pred porabo. Lasten delež (npr. 35–50 % za VIP) je neobvezen; prazno = delež stopnje. Brezplačna poštnina velja, ko je član prijavljen, in pokrije osnovno dostavo; nadgradnje (sledenje, podpis, paket) ostanejo plačljive.', 'zvij-core'); ?></p>
      <table class="widefat striped">
        <thead><tr><th><?php esc_html_e('Član', 'zvij-core'); ?></th><th><?php esc_html_e('Poraba 12 mes.', 'zvij-core'); ?></th><th><?php esc_html_e('Stopnja', 'zvij-core'); ?></th><th><?php esc_html_e('Delež', 'zvij-core'); ?></th><th><?php esc_html_e('Ročna nastavitev', 'zvij-core'); ?></th></tr></thead>
        <tbody>
        <?php foreach ($members as $member) : ?>
          <?php $tier = zvij_tiers_for_email($member['email']); $manual = zvij_tiers_manual($member['email']); ?>
          <tr>
            <td><?php echo esc_html($member['email']); ?><?php echo $member['name'] !== '' ? '<br><small>' . esc_html($member['name']) . '</small>' : ''; ?></td>
            <td><?php echo esc_html(number_format_i18n($tier['spend'], 2)); ?> €</td>
            <td><?php echo esc_html((string) $tier['level']) . ($tier['manual'] ? ' <em>' . esc_html__('(ročno)', 'zvij-core') . '</em>' : ''); ?></td>
            <td><?php echo esc_html(number_format_i18n($tier['share'], 1)); ?> %<?php echo ! empty($manual['free_shipping']) ? '<br><small>' . esc_html__('brezplačna poštnina', 'zvij-core') . '</small>' : ''; ?></td>
            <td>
              <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:flex;gap:.4rem;flex-wrap:wrap;align-items:center">
                <?php wp_nonce_field('zvij_tiers_member'); ?>
                <input type="hidden" name="action" value="zvij_tiers_member">
                <input type="hidden" name="email" value="<?php echo esc_attr($member['email']); ?>">
                <select name="level">
                  <option value="0"><?php esc_html_e('samodejno', 'zvij-core'); ?></option>
                  <?php for ($n = 1; $n <= 5; $n++) : ?><option value="<?php echo esc_attr((string) $n); ?>" <?php selected($manual ? (int) $manual['level'] : 0, $n); ?>><?php echo esc_html((string) $n); ?></option><?php endfor; ?>
                </select>
                <input type="number" step="0.5" min="0" max="100" name="share" placeholder="<?php esc_attr_e('delež %', 'zvij-core'); ?>" value="<?php echo esc_attr($manual && $manual['share'] !== null ? (string) (float) $manual['share'] : ''); ?>" style="width:6rem">
                <label><input type="checkbox" name="free_shipping" value="1" <?php checked(! empty($manual['free_shipping'])); ?>> <?php esc_html_e('brezplačna poštnina', 'zvij-core'); ?></label>
                <input type="text" name="note" placeholder="<?php esc_attr_e('opomba', 'zvij-core'); ?>" value="<?php echo esc_attr($manual['note'] ?? ''); ?>" style="width:10rem">
                <button class="button"><?php esc_html_e('Shrani', 'zvij-core'); ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
}

/* ---------- Brezplačna poštnina za označene člane ---------- */

/** Ali ima prijavljen kupec brezplačno poštnino (gost je ne more uveljaviti z vpisom tujega e-naslova). */
function zvij_tiers_free_shipping_for_current_user(): bool {
    $user = wp_get_current_user();
    if (! $user->exists()) {
        return false;
    }
    $row = zvij_tiers_manual((string) $user->user_email);
    return $row && ! empty($row['free_shipping']);
}

/**
 * Osnovna (navadna) dostava postane brezplačna; nadgradnje ostanejo plačljive.
 * Če je brezplačna dostava že na voljo (nad pragom), se ne spremeni nič.
 * Po filtru v zvij-core.php (prioriteta 10), ki skrije navadno poštnino nad pragom.
 */
add_filter('woocommerce_package_rates', function (array $rates): array {
    if (! zvij_tiers_free_shipping_for_current_user()) {
        return $rates;
    }
    foreach ($rates as $rate) {
        if ($rate->method_id === 'free_shipping') {
            return $rates;
        }
    }
    foreach ($rates as $rate) {
        if ($rate->method_id === 'flat_rate' && $rate->get_label() === 'Navadna poštnina') {
            $rate->set_cost(0);
            $rate->set_taxes([]);
            $rate->set_label(__('Brezplačna dostava (član)', 'zvij-core'));
        }
    }
    return $rates;
}, 20);
