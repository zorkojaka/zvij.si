<?php
/**
 * Vršički po gramih (Jaka, 17. 10. 2026).
 *
 * Namesto dveh pakiranj (1 g / 5 g) kupec z drsnikom ali številko izbere,
 * koliko gramov želi. V košarico gre variacija »1 g« s količino = grami,
 * cena na gram pa pade na dveh mejah:
 *
 *   1–4 g osnovna cena  ·  od 5 g −10 %  ·  od 25 g −20 %
 *
 * Meje so v opciji `zvij_gram_tiers` (gram => popust v %). Računa jih isti
 * količinski motor kot rizle (zvij-quantity-pricing.php), zato košarica,
 * oznaka popusta in spodbuda »še X in ...« delujejo brez podvajanja.
 * Variacija »5 g« je izklopljena (zasebna), ne izbrisana.
 */

if (! defined('ABSPATH')) {
    exit;
}

const ZVIJ_GRAM_CATEGORY = 'cbd-cbg-vrsicki';
const ZVIJ_GRAM_RANGE_MAX = 50;

/** @return array<int,float> gram => popust v % */
function zvij_gram_tiers(): array {
    $tiers = (array) get_option('zvij_gram_tiers', [5 => 10, 25 => 20]);
    $clean = [];
    foreach ($tiers as $g => $pct) {
        if ((int) $g >= 2 && (float) $pct > 0) {
            $clean[(int) $g] = (float) $pct;
        }
    }
    ksort($clean);
    return $clean;
}

/** Ali se izdelek (ali njegova variacija) prodaja po gramih. */
function zvij_is_gram_product($product): bool {
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    if (! $product instanceof WC_Product) {
        return false;
    }
    $id = $product->get_parent_id() ?: $product->get_id();
    return has_term(ZVIJ_GRAM_CATEGORY, 'product_cat', $id) && wc_get_product($id)->is_type('variable');
}

/** Variacija »1 g« izdelka po gramih. */
function zvij_gram_variation(WC_Product $product): ?WC_Product_Variation {
    $parent = $product->get_parent_id() ? wc_get_product($product->get_parent_id()) : $product;
    if (! $parent instanceof WC_Product_Variable) {
        return null;
    }
    foreach ($parent->get_children() as $child_id) {
        $child = wc_get_product($child_id);
        if ($child instanceof WC_Product_Variation && $child->is_purchasable()) {
            foreach ($child->get_attributes() as $value) {
                if (preg_match('/^1\s*g$/i', trim((string) $value))) {
                    return $child;
                }
            }
        }
    }
    return null;
}

/** Enota v besedilih: »g« namesto »kosov« za vršičke. */
function zvij_qty_unit_label(WC_Product $product, int $n): string {
    return zvij_is_gram_product($product) ? 'g' : zvij_kosov($n);
}

// Lestvica po gramih nadomesti lestvico kosov (motor v zvij-quantity-pricing.php).
add_filter('zvij_qty_ladder', static function (array $ladder, WC_Product $product): array {
    return zvij_is_gram_product($product) ? zvij_gram_tiers() : $ladder;
}, 10, 2);

/**
 * Podatki za drsnik: cena na gram in kristali na gram za vsako stopnjo.
 * Kristali se računajo iz cene stopnje, tako kot pri plačanem naročilu.
 *
 * @return array{base: float, tiers: array<int,array{min:int,pct:float,unit:float,kr:float}>}
 */
function zvij_gram_offer(WC_Product_Variation $gram): array {
    $base = zvij_qty_base_price($gram);
    $steps = [1 => 0.0] + zvij_gram_tiers();
    $cost = function_exists('zvij_tiers_cost') ? zvij_tiers_cost($gram) : null;
    $tiered = function_exists('zvij_tiers_enabled') && zvij_tiers_enabled() && $cost !== null;
    $flat = function_exists('zvij_credit_product_kristali') ? (float) zvij_credit_product_kristali($gram) : 0.0;
    $share = $tiered ? zvij_tiers_viewer_share() : 0.0;

    $tiers = [];
    foreach ($steps as $min => $pct) {
        $unit = $pct > 0 ? round($base * (1 - $pct / 100), 2) : $base;
        $kr = $tiered ? max(0.0, ($unit - $cost) * $share / 100 * ZVIJ_KRISTALI_PER_EUR) : $flat;
        $tiers[] = ['min' => (int) $min, 'pct' => (float) $pct, 'unit' => $unit, 'kr' => round($kr, 3)];
    }
    return ['base' => $base, 'tiers' => $tiers];
}

/**
 * Drsnik ni linearen: pri 1–50 g bi bili meji 1 g in 5 g ena na drugi.
 * Odseki med mejami dobijo enak del širine. @return array<int,array{0:int,1:float}> [gram, položaj %]
 */
function zvij_gram_scale(): array {
    $points = array_values(array_unique(array_merge([1], array_keys(zvij_gram_tiers()), [ZVIJ_GRAM_RANGE_MAX])));
    sort($points);
    $n = count($points) - 1;
    $scale = [];
    foreach ($points as $i => $g) {
        $scale[] = [$g, $n > 0 ? round($i / $n * 100, 2) : 0.0];
    }
    return $scale;
}

function zvij_gram_position(int $grams): float {
    $scale = zvij_gram_scale();
    for ($i = 1; $i < count($scale); $i++) {
        [$g0, $p0] = $scale[$i - 1];
        [$g1, $p1] = $scale[$i];
        if ($grams <= $g1) {
            return $p0 + ($grams - $g0) / max(1, $g1 - $g0) * ($p1 - $p0);
        }
    }
    return 100.0;
}

/** Cena vršičkov je na gram: »8,00 € / g«. */
add_filter('woocommerce_get_price_html', static function ($html, $product) {
    if ($html !== '' && $product instanceof WC_Product && zvij_is_gram_product($product)) {
        $html .= ' <span class="zv-per-gram">/ g</span>';
    }
    return $html;
}, 20, 2);

/** Drsnik gramov na strani izdelka, nad gumbom »Dodaj v grinder«. */
add_action('woocommerce_before_add_to_cart_button', static function (): void {
    global $product;
    if (! $product instanceof WC_Product || ! zvij_is_gram_product($product)) {
        return;
    }
    $gram = zvij_gram_variation($product);
    if (! $gram) {
        return;
    }
    $offer = zvij_gram_offer($gram);
    ?>
    <div class="zv-grams" data-grams data-variation="<?php echo esc_attr((string) $gram->get_id()); ?>" data-offer="<?php echo esc_attr(wp_json_encode($offer)); ?>">
      <div class="zv-grams__head">
        <label for="zv-grams-num"><?php esc_html_e('Koliko gramov?', 'zvij-core'); ?></label>
        <span class="zv-grams__num"><input id="zv-grams-num" type="number" min="1" max="999" step="1" value="1" inputmode="numeric" data-grams-num> g</span>
      </div>
      <input class="zv-grams__range" type="range" min="0" max="1000" step="1" value="0" data-grams-range data-scale="<?php echo esc_attr(wp_json_encode(zvij_gram_scale())); ?>" aria-label="<?php esc_attr_e('Grami', 'zvij-core'); ?>">
      <div class="zv-grams__marks" aria-hidden="true">
        <?php foreach ($offer['tiers'] as $tier) :
            $left = zvij_gram_position($tier['min']); ?>
          <span data-grams-mark="<?php echo esc_attr((string) $tier['min']); ?>" style="left:<?php echo esc_attr(number_format($left, 2, '.', '')); ?>%">
            <b><?php echo esc_html($tier['min'] . ' g'); ?></b>
            <small><?php echo esc_html(wp_strip_all_tags(wc_price($tier['unit']))); ?>/g</small>
          </span>
        <?php endforeach; ?>
      </div>
      <p class="zv-grams__sum" aria-live="polite">
        <strong data-grams-total><?php echo esc_html(wp_strip_all_tags(wc_price($offer['base']))); ?></strong>
        <span data-grams-unit><?php echo esc_html(wp_strip_all_tags(wc_price($offer['base']))); ?>/g</span>
        <span class="zv-grams__kr" data-grams-kr></span>
      </p>
      <p class="zv-grams__hint" data-grams-hint></p>
    </div>
    <?php
}, 5);

/** V košarici »SMOKEY CBD vršički — 6 g« namesto »… - 1 g« × 6. */
add_filter('woocommerce_cart_item_name', static function ($name, $item) {
    $product = $item['data'] ?? null;
    if (! $product instanceof WC_Product_Variation || ! zvij_is_gram_product($product)) {
        return $name;
    }
    $parent = wc_get_product($product->get_parent_id());
    $label = esc_html(($parent ? $parent->get_name() : $product->get_name()) . ' — ' . (int) $item['quantity'] . ' g');
    return preg_replace('/^(<a [^>]*>)?[^<]*/', '$1' . $label, (string) $name, 1);
}, 5, 2);

add_action('wp_enqueue_scripts', static function (): void {
    if (! is_product() || ! zvij_is_gram_product(get_queried_object_id())) {
        return;
    }
    $url = plugins_url('assets/grams.js', dirname(__FILE__));
    wp_enqueue_script('zvij-grams', $url, ['jquery'], '1.1.0', true);
});
