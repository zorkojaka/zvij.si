<?php
/**
 * Front page — Zvij.si homepage, variant A (izdelek-first).
 *
 * Order: hero, DUBI + CBD/CBG vršički, Zvij.si Kit (en setup, tri barve),
 * Throwie, Reload, Član teaser, FRUTTY, zaključni CTA.
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

$d42  = zvij_home_product('dubi-42-aktivnih-ogljikovih-filtrov');
$d420 = zvij_home_product('dubi-420-aktivnih-ogljikovih-filtrov');

// CBD/CBG vršički are variable products (sorta = product, pakiranje 1 g / 5 g = variation).
// Variable products can't use simple AJAX add-to-cart, so the homepage selector resolves the
// variation and links to the product page with the size preselected.
$vrs_parents = [
    'SMOKEY' => 'smokey-cbd-vrsicki',
    'CHILLY' => 'chilly-cbg-vrsicki',
    'FRUTTY' => 'frutty-cbd-vrsicki',
];
$vrs_map = [];
$vrs_default_img = '';
$vrs_default_price = '—';
foreach ($vrs_parents as $sorta => $slug) {
    $parent = zvij_home_product($slug);
    if (! $parent) {
        continue;
    }
    $permalink = (string) get_permalink($parent->get_id());
    foreach ($parent->get_children() as $vid) {
        $v = wc_get_product($vid);
        if (! $v) {
            continue;
        }
        $attrs = $v->get_variation_attributes();
        $pak = trim((string) ($attrs['attribute_pakiranje'] ?? ''));
        $kol = (strpos($pak, '5') !== false) ? '5g' : '1g';
        $img = zvij_home_product_img_url($v);
        if ($img === '') {
            $img = zvij_home_product_img_url($parent);
        }
        $vrs_map[$sorta . '|' . $kol] = [
            'url'   => esc_url_raw(add_query_arg('attribute_pakiranje', $pak, $permalink)),
            'price' => zvij_home_money($v->get_price()),
            'img'   => $img,
        ];
        if ($sorta === 'FRUTTY' && $kol === '1g') {
            $vrs_default_img = $img;
            $vrs_default_price = zvij_home_money($v->get_price());
        }
    }
}
$vrs_default_url = $vrs_map['FRUTTY|1g']['url'] ?? home_url('/product/frutty-cbd-vrsicki/');

$kits = (array) get_option('zvij_kits', []);
$kit_by_key = [];
foreach ($kits as $k) {
    if (! empty($k['key'])) {
        $kit_by_key[$k['key']] = $k;
    }
}
$colors = [];
foreach (['black' => '#1f1b17', 'silver' => '#c9ccd0', 'gold' => '#d2a24f'] as $key => $swatch) {
    $kit = $kit_by_key[$key] ?? null;
    $term = function_exists('get_term_by') ? get_term_by('slug', ($kit['tag'] ?? $key . '-kit'), 'product_tag') : null;
    $colors[] = [
        'key'   => $key,
        'name'  => $kit['name'] ?? ucfirst($key) . ' Kit',
        'swatch' => $swatch,
        'img'   => zvij_kit_flatlay_url($key),
        'href'  => ($term && ! is_wp_error($term)) ? get_term_link($term) : home_url('/trgovina/'),
    ];
}
$throwie_term = function_exists('get_term_by') ? get_term_by('slug', 'throwie', 'product_tag') : null;
$throwie_href = ($throwie_term && ! is_wp_error($throwie_term)) ? get_term_link($throwie_term) : home_url('/trgovina/');
$hero_img = zvij_kit_flatlay_url('black');

/**
 * Render a buy button wired for WooCommerce AJAX add-to-cart.
 */
$buy_btn = static function (?WC_Product $product): string {
    if (! $product instanceof WC_Product) {
        return '<span class="zh-add zh-add--off">Kmalu</span>';
    }
    $id = $product->get_id();
    return '<a href="?add-to-cart=' . esc_attr($id) . '" data-product_id="' . esc_attr($id) . '" data-quantity="1" rel="nofollow" class="zh-add ajax_add_to_cart add_to_cart_button"><i class="ti ti-shopping-bag-plus" aria-hidden="true"></i> ' . esc_html__('Dodaj', 'zvij-theme') . '</a>';
};
?>

<section class="zh-hero">
  <div class="zh-hero-text">
    <p class="eyebrow"><?php esc_html_e('Zvij.si', 'zvij-theme'); ?></p>
    <h1><?php esc_html_e('Tvoj ritual.', 'zvij-theme'); ?><br><?php esc_html_e('Tvoja mera.', 'zvij-theme'); ?><br><?php esc_html_e('Tvoj setup.', 'zvij-theme'); ?></h1>
    <p class="hero-copy"><?php esc_html_e('Urejen setup za tvoj ritual — DUBI filtri, CBD/CBG vršički in vse okrog, z lažjim reloadom.', 'zvij-theme'); ?></p>
    <div class="button-row">
      <a class="button" href="#ponudba"><?php esc_html_e('Poglej ponudbo', 'zvij-theme'); ?></a>
      <a class="button button--member" href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>"><?php esc_html_e('Postani član', 'zvij-theme'); ?></a>
    </div>
  </div>
  <figure class="zh-hero-art">
    <?php if ($hero_img !== '') : ?>
      <img src="<?php echo esc_url($hero_img); ?>" alt="<?php esc_attr_e('Zvij.si setup', 'zvij-theme'); ?>" loading="eager" />
    <?php else : ?>
      <div class="zh-hero-fallback"><i class="ti ti-package" aria-hidden="true"></i></div>
    <?php endif; ?>
    <figcaption><?php esc_html_e('Vse, kar rabiš, da si zviješ.', 'zvij-theme'); ?></figcaption>
  </figure>
</section>

<section class="section-block" id="ponudba">
  <p class="eyebrow"><?php esc_html_e('Nosilna izdelka', 'zvij-theme'); ?></p>
  <div class="zh-offer">

    <div class="zh-card" data-zh-simple>
      <div class="zh-shot">
        <?php $d42_img = zvij_home_product_img_url($d42); ?>
        <?php if ($d42_img !== '') : ?><img src="<?php echo esc_url($d42_img); ?>" alt="DUBI filtri" loading="lazy" /><?php else : ?><i class="ti ti-asterisk" aria-hidden="true"></i><?php endif; ?>
      </div>
      <div class="zh-cardbody">
        <h3><?php esc_html_e('DUBI filtri', 'zvij-theme'); ?></h3>
        <p class="zh-ben"><?php esc_html_e('Boljši občutek. Urejen reload.', 'zvij-theme'); ?></p>
        <p class="zh-lab"><?php esc_html_e('Različica', 'zvij-theme'); ?></p>
        <div class="zh-opts">
          <button type="button" class="zh-opt on" data-opt data-pid="<?php echo esc_attr($d42 ? $d42->get_id() : ''); ?>" data-price="<?php echo esc_attr($d42 ? zvij_home_money($d42->get_price()) : ''); ?>" aria-pressed="true">DUBI 42</button>
          <button type="button" class="zh-opt" data-opt data-pid="<?php echo esc_attr($d420 ? $d420->get_id() : ''); ?>" data-price="<?php echo esc_attr($d420 ? zvij_home_money($d420->get_price()) : ''); ?>" aria-pressed="false">DUBI 420</button>
        </div>
        <div class="zh-buy">
          <span class="zh-price" data-price-out><?php echo esc_html($d42 ? zvij_home_money($d42->get_price()) : '—'); ?></span>
          <?php echo $buy_btn($d42); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
      </div>
    </div>

    <div class="zh-card" data-zh-vrs>
      <div class="zh-shot">
        <?php if ($vrs_default_img !== '') : ?><img data-vrs-img src="<?php echo esc_url($vrs_default_img); ?>" alt="CBD/CBG vršički" loading="lazy" /><?php else : ?><i class="ti ti-leaf" aria-hidden="true"></i><?php endif; ?>
      </div>
      <div class="zh-cardbody">
        <h3><?php esc_html_e('CBD/CBG vršički', 'zvij-theme'); ?></h3>
        <p class="zh-ben"><?php esc_html_e('Jasna izbira. 1 g ali 5 g.', 'zvij-theme'); ?></p>
        <p class="zh-lab"><?php esc_html_e('Sorta', 'zvij-theme'); ?></p>
        <div class="zh-opts">
          <button type="button" class="zh-opt" data-sorta="SMOKEY">SMOKEY</button>
          <button type="button" class="zh-opt" data-sorta="CHILLY">CHILLY</button>
          <button type="button" class="zh-opt on" data-sorta="FRUTTY" aria-pressed="true">FRUTTY</button>
        </div>
        <p class="zh-lab"><?php esc_html_e('Količina', 'zvij-theme'); ?></p>
        <div class="zh-opts">
          <button type="button" class="zh-opt on" data-kol="1g" aria-pressed="true">1 g</button>
          <button type="button" class="zh-opt" data-kol="5g">5 g</button>
        </div>
        <div class="zh-buy">
          <span class="zh-price" data-price-out><?php echo esc_html($vrs_default_price); ?></span>
          <a class="zh-add" data-vrs-link href="<?php echo esc_url($vrs_default_url); ?>"><i class="ti ti-shopping-bag-plus" aria-hidden="true"></i> <?php esc_html_e('Izberi', 'zvij-theme'); ?></a>
        </div>
        <script type="application/json" id="zh-vrs-map"><?php echo wp_json_encode($vrs_map); ?></script>
      </div>
    </div>

  </div>
</section>

<section class="section-block" data-kitsel>
  <p class="eyebrow"><?php esc_html_e('Zvij.si Kit', 'zvij-theme'); ?></p>
  <h2 class="zh-h2"><?php esc_html_e('En setup. Izberi barvo.', 'zvij-theme'); ?></h2>
  <p class="zh-sub"><?php esc_html_e('Isti premišljen komplet v Black, Silver ali Gold.', 'zvij-theme'); ?></p>
  <div class="zh-kit">
    <figure class="zh-kit-art">
      <?php $first = $colors[0]; ?>
      <?php if ($first['img'] !== '') : ?>
        <img data-kit-visual src="<?php echo esc_url($first['img']); ?>" alt="<?php echo esc_attr($first['name']); ?>" loading="lazy" />
      <?php else : ?>
        <div data-kit-visual class="zh-kit-fallback" style="background:<?php echo esc_attr($first['swatch']); ?>"></div>
      <?php endif; ?>
      <figcaption data-kit-cap><?php echo esc_html($first['name']); ?></figcaption>
    </figure>
    <div class="zh-kit-side">
      <p class="zh-lab"><?php esc_html_e('Izberi barvo', 'zvij-theme'); ?></p>
      <div class="zh-colors">
        <?php foreach ($colors as $i => $c) : ?>
          <button type="button" class="zh-color<?php echo $i === 0 ? ' on' : ''; ?>" data-color
            data-img="<?php echo esc_url($c['img']); ?>"
            data-href="<?php echo esc_url($c['href']); ?>"
            data-name="<?php echo esc_attr($c['name']); ?>"
            data-bg="<?php echo esc_attr($c['swatch']); ?>">
            <b style="background:<?php echo esc_attr($c['swatch']); ?>"></b><?php echo esc_html(str_replace(' Kit', '', $c['name'])); ?>
          </button>
        <?php endforeach; ?>
      </div>
      <ul class="zh-kit-list">
        <li><i class="ti ti-check" aria-hidden="true"></i><?php esc_html_e('Tulec, vžigalnik, grinder', 'zvij-theme'); ?></li>
        <li><i class="ti ti-check" aria-hidden="true"></i><?php esc_html_e('Rolca v barvi kita', 'zvij-theme'); ?></li>
        <li><i class="ti ti-check" aria-hidden="true"></i><?php esc_html_e('DUBI 42 filter', 'zvij-theme'); ?></li>
      </ul>
      <a class="button" data-kit-link href="<?php echo esc_url($first['href']); ?>"><?php esc_html_e('Poglej kit', 'zvij-theme'); ?></a>
    </div>
  </div>

  <div class="zh-throwie">
    <div class="zh-throwie-art">
      <?php $tw = zvij_kit_flatlay_url('throwie'); ?>
      <?php if ($tw !== '') : ?><img src="<?php echo esc_url($tw); ?>" alt="Throwie Kit" loading="lazy" /><?php else : ?><i class="ti ti-briefcase" aria-hidden="true"></i><?php endif; ?>
    </div>
    <div class="zh-throwie-body">
      <h3><?php esc_html_e('Throwie Kit', 'zvij-theme'); ?></h3>
      <p><?php esc_html_e('Osnovni setup. Brez kompliciranja.', 'zvij-theme'); ?></p>
    </div>
    <a class="button button--ghost" href="<?php echo esc_url($throwie_href); ?>"><?php esc_html_e('Poglej Throwie', 'zvij-theme'); ?></a>
  </div>
</section>

<section class="section-block">
  <div class="zh-reload">
    <p class="eyebrow eyebrow--warm"><?php esc_html_e('Reload', 'zvij-theme'); ?></p>
    <h2 class="zh-h2"><?php esc_html_e('Ko zmanjka, samo dopolni.', 'zvij-theme'); ?></h2>
    <p class="zh-sub"><?php esc_html_e('Ne sestavljaš znova. Izbereš samo, kaj ti manjka.', 'zvij-theme'); ?></p>
    <div class="zh-rtiles">
      <div class="zh-rtile"><i class="ti ti-asterisk" aria-hidden="true"></i><span>DUBI</span></div>
      <div class="zh-rtile"><i class="ti ti-leaf" aria-hidden="true"></i><span><?php esc_html_e('Vršički', 'zvij-theme'); ?></span></div>
      <div class="zh-rtile"><i class="ti ti-news" aria-hidden="true"></i><span><?php esc_html_e('Rizle / rolce', 'zvij-theme'); ?></span></div>
      <div class="zh-rtile"><i class="ti ti-flame" aria-hidden="true"></i><span><?php esc_html_e('Vžigalniki', 'zvij-theme'); ?></span></div>
      <div class="zh-rtile"><i class="ti ti-dots" aria-hidden="true"></i><span><?php esc_html_e('Ostalo', 'zvij-theme'); ?></span></div>
    </div>
    <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>"><i class="ti ti-refresh" aria-hidden="true"></i> <?php esc_html_e('Hiter reload', 'zvij-theme'); ?></a>
  </div>

  <div class="zh-member">
    <div>
      <h2 class="zh-h2"><?php esc_html_e('Član Zvij.si', 'zvij-theme'); ?> <span class="zh-tag"><?php esc_html_e('v pripravi', 'zvij-theme'); ?></span></h2>
      <p class="zh-sub"><?php esc_html_e('Sistem za ponovne nakupe pride kmalu. Najprej izdelki in reload.', 'zvij-theme'); ?></p>
    </div>
    <a class="button button--member" href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>"><?php esc_html_e('Spoznaj Član Zvij.si', 'zvij-theme'); ?></a>
  </div>

  <div class="zh-frutty">
    <div>
      <h3><?php esc_html_e('Prvi FRUTTY za 4,20 €', 'zvij-theme'); ?></h3>
      <p><?php esc_html_e('Lahek prvi preizkus.', 'zvij-theme'); ?></p>
    </div>
    <a class="button button--light" href="<?php echo esc_url(home_url('/product/frutty-cbd-vrsicki-1-g/')); ?>"><?php esc_html_e('Poglej FRUTTY', 'zvij-theme'); ?></a>
  </div>
</section>

<section class="footer-cta">
  <h2><?php esc_html_e('Začni s svojim setupom.', 'zvij-theme'); ?></h2>
  <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>"><?php esc_html_e('Odpri trgovino', 'zvij-theme'); ?></a>
</section>
<?php
get_footer();
