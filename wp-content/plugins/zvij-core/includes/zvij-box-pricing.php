<?php
/**
 * Prodaja rizel po škatlah (Jaka, 1. 9. 2026).
 *
 * Cilj: kdor vzame celo škatlo, jo dobi ceneje — in ljudi to spodbudi, da
 * kupujejo na zalogo, namesto da naročajo po en zavitek.
 *
 * Zakaj količinski prag in ne ločen izdelek »škatla 26 kos«:
 *
 * - zaloga ostane pravilna: nakup škatle vzame 26 kosov z istega izdelka,
 *   pri ločenem izdelku bi imeli dve zalogi za isto blago in bi se razšli;
 * - katalog se ne podvoji (10 izdelkov namesto 20);
 * - popust dobi tudi tisti, ki naroči 30 kosov, ne samo natanko 26.
 *
 * Popust je en sam odstotek za vse izdelke (opcija `zvij_box_discount`),
 * da je pravilo razumljivo tudi kupcu: »cela škatla − 15 %«.
 */

if (! defined('ABSPATH')) {
    exit;
}

/** Privzeti popust za celo škatlo, v odstotkih. */
function zvij_box_discount(): float {
    return (float) apply_filters('zvij_box_discount', (float) get_option('zvij_box_discount', 15));
}

/** Koliko kosov je cela škatla za ta izdelek; 0 = izdelek se ne prodaja po škatlah. */
function zvij_box_qty($product): int {
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    if (! $product instanceof WC_Product) {
        return 0;
    }

    return (int) apply_filters('zvij_box_qty', (int) $product->get_meta('_zvij_box_qty'), $product);
}

/** Cena na kos pri celi škatli. */
function zvij_box_unit_price(WC_Product $product): float {
    $regular = (float) $product->get_regular_price();

    return round($regular * (1 - zvij_box_discount() / 100), 2);
}

/* -------------------------------------------------------------------------
   Admin: polje na izdelku
------------------------------------------------------------------------- */

add_action('woocommerce_product_options_general_product_data', static function (): void {
    woocommerce_wp_text_input([
        'id'                => '_zvij_box_qty',
        'label'             => __('Cela škatla (kosov)', 'zvij-core'),
        'description'       => sprintf(
            /* translators: %s: popust v odstotkih */
            __('Koliko kosov je cela škatla. Ob tej količini v košarici cena pade za %s %%. Prazno = brez škatelne cene.', 'zvij-core'),
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
        if (empty($item['data']) || ! $item['data'] instanceof WC_Product) {
            continue;
        }
        $box = zvij_box_qty($item['data']);
        if ($box < 2 || (int) $item['quantity'] < $box) {
            continue;
        }
        $item['data']->set_price((string) zvij_box_unit_price($item['data']));
    }
}, 20);

/* -------------------------------------------------------------------------
   Prikaz na izdelku
------------------------------------------------------------------------- */

add_action('woocommerce_single_product_summary', static function (): void {
    global $product;
    if (! $product instanceof WC_Product) {
        return;
    }

    $box = zvij_box_qty($product);
    if ($box < 2) {
        return;
    }

    $regular   = (float) $product->get_regular_price();
    $unit      = zvij_box_unit_price($product);
    $box_total = round($unit * $box, 2);
    $saving    = round(($regular - $unit) * $box, 2);
    $url       = add_query_arg(['add-to-cart' => $product->get_id(), 'quantity' => $box], wc_get_cart_url());
    ?>
    <div class="zv-box-offer">
      <p class="zv-box-offer__head">
        <strong><?php printf(esc_html__('Cela škatla — %d kosov', 'zvij-core'), $box); ?></strong>
        <span class="zv-box-offer__badge">−<?php echo esc_html(number_format_i18n(zvij_box_discount())); ?> %</span>
      </p>
      <p class="zv-box-offer__price">
        <?php echo wp_kses_post(wc_price($box_total)); ?>
        <span><?php printf(esc_html__('%s / kos namesto %s', 'zvij-core'), wp_strip_all_tags(wc_price($unit)), wp_strip_all_tags(wc_price($regular))); ?></span>
      </p>
      <?php if ($product->is_in_stock() && (! $product->managing_stock() || $product->get_stock_quantity() >= $box)) : ?>
        <a class="button zv-box-offer__btn" href="<?php echo esc_url($url); ?>">
          <?php esc_html_e('Dodaj celo škatlo', 'zvij-core'); ?>
        </a>
        <p class="zv-box-offer__save"><?php printf(esc_html__('Prihraniš %s.', 'zvij-core'), wp_strip_all_tags(wc_price($saving))); ?></p>
      <?php else : ?>
        <p class="zv-box-offer__save"><?php esc_html_e('Cele škatle trenutno ni na zalogi.', 'zvij-core'); ?></p>
      <?php endif; ?>
    </div>
    <?php
}, 25);

/* -------------------------------------------------------------------------
   Spodbuda v košarici
------------------------------------------------------------------------- */

add_action('woocommerce_before_cart', static function (): void {
    if (! WC()->cart || WC()->cart->is_empty()) {
        return;
    }

    $lines = [];
    foreach (WC()->cart->get_cart() as $item) {
        $product = $item['data'] ?? null;
        if (! $product instanceof WC_Product) {
            continue;
        }
        $box = zvij_box_qty($product);
        $qty = (int) $item['quantity'];
        if ($box < 2 || $qty >= $box) {
            continue;
        }
        if ($product->managing_stock() && (int) $product->get_stock_quantity() < $box) {
            continue;
        }

        $missing = $box - $qty;
        $saving  = round(((float) $product->get_regular_price() - zvij_box_unit_price($product)) * $box, 2);
        $lines[] = sprintf(
            /* translators: 1: ime izdelka, 2: manjkajoci kosi, 3: prihranek */
            __('%1$s: še %2$d do cele škatle — s tem prihraniš %3$s.', 'zvij-core'),
            $product->get_name(),
            $missing,
            wp_strip_all_tags(wc_price($saving))
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

/** Oznaka v košarici, ko škatelna cena že velja. */
add_filter('woocommerce_cart_item_name', static function ($name, $item) {
    $product = $item['data'] ?? null;
    if (! $product instanceof WC_Product) {
        return $name;
    }
    $box = zvij_box_qty($product);
    if ($box < 2 || (int) $item['quantity'] < $box) {
        return $name;
    }

    return $name . ' <span class="zv-box-tag">' . sprintf(
        esc_html__('cena cele škatle (−%s %%)', 'zvij-core'),
        esc_html(number_format_i18n(zvij_box_discount()))
    ) . '</span>';
}, 10, 2);
