<?php
/**
 * Količinski popust (Jaka, 1. 9. 2026).
 *
 * Rizle so potrošni material — kupec ve, da jih bo porabil. Edini razlog,
 * da vzame samo eno, je, da nima razloga vzeti treh. Lestvica mu ga da:
 * ena doma, ena v jakni, ena v avtu — in vse tri so cenejše.
 *
 * Tri stopnje, okrogli odstotki, da je pravilo povedljivo v enem stavku:
 *
 *   3 kosi −10 %   ·   10 kosov −15 %   ·   cela škatla −20 %
 *
 * Cela škatla je vrh lestvice, ne posebna ponudba: prag je pri pakiranju
 * dobavitelja (26 / 22 / 16 / 14 kosov) in ima najvišji popust.
 *
 * Zakaj količinski prag in ne ločen izdelek »škatla 26 kos«:
 *
 * - zaloga ostane pravilna: nakup vzame kose z istega izdelka, pri ločenem
 *   izdelku bi imeli dve zalogi za isto blago in bi se razšli;
 * - katalog se ne podvoji;
 * - popust dobi tudi tisti, ki naroči 30 kosov, ne samo natanko 26.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Slovenska oblika besede »kos« za dano število: 1 kos, 2 kosa, 3 kosi,
 * 5 kosov. Brez tega se izpiše »10 kosi«.
 */
function zvij_kosov(int $n): string {
    $r = $n % 100;
    if ($r === 1) {
        return __('kos', 'zvij-core');
    }
    if ($r === 2) {
        return __('kosa', 'zvij-core');
    }
    if ($r === 3 || $r === 4) {
        return __('kosi', 'zvij-core');
    }

    return __('kosov', 'zvij-core');
}

/**
 * Lestvica: najmanjša količina => popust v odstotkih. Urejena naraščajoče.
 *
 * @return array<int,float>
 */
function zvij_qty_tiers(): array {
    $tiers = (array) get_option('zvij_qty_tiers', [3 => 10, 10 => 15]);

    $clean = [];
    foreach ($tiers as $qty => $pct) {
        $qty = (int) $qty;
        $pct = (float) $pct;
        if ($qty >= 2 && $pct > 0) {
            $clean[$qty] = $pct;
        }
    }
    ksort($clean);

    return (array) apply_filters('zvij_qty_tiers', $clean);
}

/** Popust za celo škatlo, v odstotkih. */
function zvij_box_discount(): float {
    return (float) apply_filters('zvij_box_discount', (float) get_option('zvij_box_discount', 20));
}

/** Koliko kosov je cela škatla; 0 = izdelek nima škatelnega praga. */
function zvij_box_qty($product): int {
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    if (! $product instanceof WC_Product) {
        return 0;
    }

    return (int) apply_filters('zvij_box_qty', (int) $product->get_meta('_zvij_box_qty'), $product);
}

/**
 * Celotna lestvica za izdelek, vključno s škatlo: količina => popust.
 *
 * @return array<int,float>
 */
function zvij_qty_ladder(WC_Product $product): array {
    $ladder = zvij_qty_tiers();

    $box = zvij_box_qty($product);
    if ($box >= 2) {
        // Skatla je vrh lestvice; ce bi kaksen prag lezal nad njo, ga
        // odstranimo, da lestvica ostane monotona in razumljiva.
        foreach (array_keys($ladder) as $qty) {
            if ($qty >= $box) {
                unset($ladder[$qty]);
            }
        }
        $ladder[$box] = zvij_box_discount();
    }
    ksort($ladder);

    return $ladder;
}

/** Popust v odstotkih za dano količino. */
function zvij_qty_discount(WC_Product $product, int $qty): float {
    $best = 0.0;
    foreach (zvij_qty_ladder($product) as $min => $pct) {
        if ($qty >= $min && $pct > $best) {
            $best = (float) $pct;
        }
    }

    return $best;
}

/** Cena na kos pri dani količini. */
function zvij_qty_unit_price(WC_Product $product, int $qty): float {
    $regular = (float) $product->get_regular_price();
    $pct     = zvij_qty_discount($product, $qty);

    return $pct > 0 ? round($regular * (1 - $pct / 100), 2) : $regular;
}

/** Ali izdelek sploh ima količinsko lestvico. */
function zvij_qty_has_ladder(WC_Product $product): bool {
    return zvij_box_qty($product) >= 2 && zvij_qty_ladder($product) !== [];
}

/* -------------------------------------------------------------------------
   Admin
------------------------------------------------------------------------- */

add_action('woocommerce_product_options_general_product_data', static function (): void {
    woocommerce_wp_text_input([
        'id'                => '_zvij_box_qty',
        'label'             => __('Cela škatla (kosov)', 'zvij-core'),
        'description'       => sprintf(
            /* translators: %s: popust v odstotkih */
            __('Pakiranje dobavitelja. Ob tej količini cena pade za %s %%. Vpis hkrati vklopi celotno količinsko lestvico. Prazno = brez količinskega popusta.', 'zvij-core'),
            number_format_i18n(zvij_box_discount())
        ),
        'desc_tip'          => true,
        'type'              => 'number',
        'custom_attributes' => ['min' => '0', 'step' => '1'],
    ]);
});

add_action('woocommerce_admin_process_product_object', static function (WC_Product $product): void {
    $raw = isset($_POST['_zvij_box_qty']) ? sanitize_text_field((string) $_POST['_zvij_box_qty']) : '';
    if ($raw === '' || ! is_numeric($raw) || (int) $raw < 2) {
        $product->delete_meta_data('_zvij_box_qty');
    } else {
        $product->update_meta_data('_zvij_box_qty', (string) (int) $raw);
    }
});

/* -------------------------------------------------------------------------
   Cena v košarici
------------------------------------------------------------------------- */

add_action('woocommerce_before_calculate_totals', static function ($cart): void {
    if (is_admin() && ! wp_doing_ajax()) {
        return;
    }
    if (! $cart instanceof WC_Cart) {
        return;
    }

    foreach ($cart->get_cart() as $item) {
        $product = $item['data'] ?? null;
        if (! $product instanceof WC_Product || ! zvij_qty_has_ladder($product)) {
            continue;
        }
        $unit = zvij_qty_unit_price($product, (int) $item['quantity']);
        if ($unit < (float) $product->get_regular_price()) {
            $product->set_price((string) $unit);
        }
    }
}, 20);

/* -------------------------------------------------------------------------
   Prikaz na izdelku
------------------------------------------------------------------------- */

add_action('woocommerce_single_product_summary', static function (): void {
    global $product;
    if (! $product instanceof WC_Product || ! zvij_qty_has_ladder($product)) {
        return;
    }

    $regular = (float) $product->get_regular_price();
    $ladder  = zvij_qty_ladder($product);
    $box     = zvij_box_qty($product);
    // Kristali se mnozijo s kolicino, zato jih pokazemo ob vsaki stopnji:
    // vec kosov je hkrati nizja cena IN vec kristalov.
    $kristali = function_exists('zvij_credit_product_kristali')
        ? (int) zvij_credit_product_kristali($product)
        : 0;
    $stock   = $product->managing_stock() ? (int) $product->get_stock_quantity() : PHP_INT_MAX;
    ?>
    <div class="zv-qty-offer">
      <p class="zv-qty-offer__head"><strong><?php esc_html_e('Več kosov, nižja cena', 'zvij-core'); ?></strong></p>
      <p class="zv-qty-offer__lead"><?php echo esc_html($kristali > 0
          ? __('Ena doma, ena v jakni, ena v avtu — pri treh je vsaka cenejša, kristalov pa trikrat toliko.', 'zvij-core')
          : __('Ena doma, ena v jakni, ena v avtu — pri treh je vsaka cenejša.', 'zvij-core')); ?></p>
      <ul class="zv-qty-offer__tiers">
        <?php foreach ($ladder as $min => $pct) :
            if ($min > $stock) {
                continue;
            }
            $unit  = round($regular * (1 - $pct / 100), 2);
            $total = round($unit * $min, 2);
            $save  = round(($regular - $unit) * $min, 2);
            $url   = add_query_arg(['add-to-cart' => $product->get_id(), 'quantity' => $min], wc_get_cart_url());
            $is_box = ($min === $box);
        ?>
          <li class="zv-qty-tier<?php echo $is_box ? ' zv-qty-tier--box' : ''; ?>">
            <span class="zv-qty-tier__qty">
              <?php echo esc_html($is_box
                  ? sprintf(__('cela škatla — %d %s', 'zvij-core'), $min, zvij_kosov($min))
                  : sprintf('%d %s', $min, zvij_kosov($min))); ?>
            </span>
            <span class="zv-qty-tier__unit"><?php echo wp_kses_post(wc_price($unit)); ?> <small><?php esc_html_e('/ kos', 'zvij-core'); ?></small></span>
            <span class="zv-qty-tier__save">−<?php echo esc_html(number_format_i18n($pct)); ?> %<small><?php printf(esc_html__('prihraniš %s', 'zvij-core'), wp_strip_all_tags(wc_price($save))); ?></small></span>
            <?php if ($kristali > 0) : ?>
              <span class="zv-qty-tier__kr"><?php echo esc_html(zvij_kristali_izpis($kristali * $min)); ?><small><?php esc_html_e('za člane', 'zvij-core'); ?></small></span>
            <?php endif; ?>
            <a class="zv-qty-tier__btn" href="<?php echo esc_url($url); ?>">
              <?php printf(esc_html__('Vzemi %d', 'zvij-core'), $min); ?>
              <small><?php echo wp_strip_all_tags(wc_price($total)); ?></small>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php
}, 25);

/* -------------------------------------------------------------------------
   Spodbuda v košarici — koliko manjka do naslednje stopnje
------------------------------------------------------------------------- */

add_action('woocommerce_before_cart', static function (): void {
    if (! WC()->cart || WC()->cart->is_empty()) {
        return;
    }

    $lines = [];
    foreach (WC()->cart->get_cart() as $item) {
        $product = $item['data'] ?? null;
        if (! $product instanceof WC_Product || ! zvij_qty_has_ladder($product)) {
            continue;
        }

        $qty     = (int) $item['quantity'];
        $regular = (float) $product->get_regular_price();
        $stock   = $product->managing_stock() ? (int) $product->get_stock_quantity() : PHP_INT_MAX;
        $current = zvij_qty_discount($product, $qty);

        $next = null;
        foreach (zvij_qty_ladder($product) as $min => $pct) {
            if ($min > $qty && $pct > $current && $min <= $stock) {
                $next = [$min, $pct];
                break;
            }
        }
        if ($next === null) {
            continue;
        }

        [$min, $pct] = $next;
        $unit  = round($regular * (1 - $pct / 100), 2);
        $now   = round(zvij_qty_unit_price($product, $qty) * $qty, 2);
        $then  = round($unit * $min, 2);
        $extra = $min - $qty;

        $lines[] = sprintf(
            /* translators: 1: ime, 2: manjkajoci kosi, 3: nova cena za vse, 4: popust */
            __('%1$s: še %2$d in vseh %5$s dobiš za %3$s (−%4$s %%) namesto %6$s.', 'zvij-core'),
            $product->get_name(),
            $extra,
            wp_strip_all_tags(wc_price($then)),
            number_format_i18n($pct),
            $min . ' ' . zvij_kosov($min),
            wp_strip_all_tags(wc_price($now))
        );
    }

    if ($lines === []) {
        return;
    }
    ?>
    <div class="zv-box-nudge">
      <p class="zv-box-nudge__head"><?php esc_html_e('Se splača dopolniti', 'zvij-core'); ?></p>
      <ul>
        <?php foreach ($lines as $line) : ?>
          <li><?php echo esc_html($line); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php
}, 8);

/** Oznaka v košarici, ko količinski popust že velja. */
add_filter('woocommerce_cart_item_name', static function ($name, $item) {
    $product = $item['data'] ?? null;
    if (! $product instanceof WC_Product || ! zvij_qty_has_ladder($product)) {
        return $name;
    }
    $pct = zvij_qty_discount($product, (int) $item['quantity']);
    if ($pct <= 0) {
        return $name;
    }

    $label = ((int) $item['quantity'] >= zvij_box_qty($product))
        ? __('cena cele škatle', 'zvij-core')
        : __('količinski popust', 'zvij-core');

    return $name . ' <span class="zv-box-tag">' . esc_html(sprintf('%s (−%s %%)', $label, number_format_i18n($pct))) . '</span>';
}, 10, 2);
