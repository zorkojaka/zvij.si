<?php
/**
 * Front page - Zvij.si visual shop prototype.
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

$dubi = zvij_home_product('dubi-42-aktivnih-ogljikovih-filtrov') ?: zvij_home_product('dubi-420-aktivnih-ogljikovih-filtrov');
$products = [
    'smokey' => zvij_home_product('smokey-cbd-vrsicki'),
    'chilly' => zvij_home_product('chilly-cbg-vrsicki'),
    'frutty' => zvij_home_product('frutty-cbd-vrsicki'),
    'dubi' => $dubi,
];

$tone_map = ['black' => 'dark', 'silver' => 'silver', 'gold' => 'gold'];
$kits = [];
foreach ((array) get_option('zvij_kits', []) as $kit_def) {
    $key = $kit_def['key'] ?? '';
    if (! isset($tone_map[$key])) {
        continue;
    }
    $kits[] = [
        'key'  => $key,
        'name' => ($kit_def['name'] ?? ucfirst($key) . ' Kit') . ' Zvij.si',
        'tone' => $tone_map[$key],
    ];
}
$hero_img = zvij_kit_flatlay_url('black');

$buy_btn = static function (?WC_Product $product, string $label = 'Poglej') : string {
    if (! $product instanceof WC_Product) {
        return '<a class="button" href="' . esc_url(home_url('/trgovina/')) . '">' . esc_html($label) . '</a>';
    }

    return '<a class="button" href="' . esc_url(get_permalink($product->get_id())) . '">' . esc_html($label) . '</a>';
};
?>

<section class="zv-hero zv-panel">
  <div class="zv-hero__copy">
    <h1>Tvoj vajb.<br>Tvoja rutina.<br>Tvoj lajf.<br><span>Tvoja pravila.</span></h1>
    <p>Zvij.si je tvoj domači kompanjon. Zrihtamo robo. Ti uživaš.</p>
    <div class="button-row">
      <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>">Poglej izdelke</a>
      <a class="button button--ghost" href="<?php echo esc_url(home_url('/zvij-kit/')); ?>">Sestavi svoj kit</a>
    </div>
  </div>
  <div class="zv-hero__visual">
    <?php if ($hero_img !== '') : ?>
      <img src="<?php echo esc_url($hero_img); ?>" alt="Zvij.si kit" loading="eager">
    <?php endif; ?>
  </div>
</section>

<section class="zv-service-strip" aria-label="Prednosti">
  <div><b>Hitra dostava</b><span>1-2 dni</span></div>
  <div><b>Diskretno pakiranje</b><span>Brez oznak</span></div>
  <div><b>Zate. Vedno.</b><span>Domača podpora</span></div>
</section>

<section class="zv-home-grid" id="ponudba">
  <article class="zv-card zv-card--wide">
    <div>
      <p class="zv-kicker">CBD/CBG</p>
      <h2>vršički</h2>
      <p>Tri sorte. Dve količini. Tvoja izbira.</p>
      <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>">Poglej vse</a>
    </div>
    <div class="zv-product-row">
      <?php foreach (['smokey', 'chilly', 'frutty'] as $slug) : ?>
        <?php $p = $products[$slug]; ?>
        <figure>
          <?php $img = zvij_home_product_img_url($p); ?>
          <?php if ($img !== '') : ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($p instanceof WC_Product ? $p->get_name() : strtoupper($slug)); ?>" loading="lazy"><?php endif; ?>
          <figcaption><?php echo esc_html(strtoupper($slug)); ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="zv-card">
    <p class="zv-kicker">DUBI</p>
    <h2>filtri</h2>
    <p>Boljši dim. Boljši občutek. Vsak dan.</p>
    <?php $img = zvij_home_product_img_url($dubi); ?>
    <?php if ($img !== '') : ?><img class="zv-card__image" src="<?php echo esc_url($img); ?>" alt="DUBI filtri" loading="lazy"><?php endif; ?>
    <?php echo $buy_btn($dubi, 'Poglej filtre'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
  </article>

  <article class="zv-card zv-card--kit">
    <div class="zv-card__head">
      <div>
        <p class="zv-kicker">Kiti Zvij.si</p>
        <h2>En kit, tvoj stil.</h2>
        <p>Izberi barvo. Ostalo je že sestavljeno.</p>
      </div>
      <a class="button button--ghost" href="<?php echo esc_url(home_url('/zvij-kit/')); ?>">Poglej kite</a>
    </div>
    <div class="zv-kit-tabs">
      <?php foreach ($kits as $i => $kit) : ?>
        <?php $img = zvij_kit_flatlay_url($kit['key']); ?>
        <a class="zv-kit-tab zv-kit-tab--<?php echo esc_attr($kit['tone']); ?>" href="<?php echo esc_url(home_url('/zvij-kit/')); ?>">
          <?php if ($img !== '') : ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($kit['name']); ?>" loading="lazy"><?php endif; ?>
          <span><?php echo esc_html($kit['name']); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="zv-card">
    <p class="zv-kicker">Reload</p>
    <h2>ko zmanjka</h2>
    <p>Ne sestavljaš znova. Samo dopolniš.</p>
    <?php $tw = zvij_kit_flatlay_url('throwie'); ?>
    <?php if ($tw !== '') : ?><img class="zv-card__image" src="<?php echo esc_url($tw); ?>" alt="Reload bundle" loading="lazy"><?php endif; ?>
    <a class="button" href="<?php echo esc_url(home_url('/reload/')); ?>">Poglej reload</a>
  </article>
</section>

<section class="zv-benefits">
  <div><b>Domača podpora</b><span>Tukaj smo zate.</span></div>
  <div><b>Diskretno pakiranje</b><span>Brez oznak.</span></div>
  <div><b>Hitra dostava</b><span>1-2 dni.</span></div>
  <div><b>Član Zvij.si</b><span>Ugodnosti za člane.</span></div>
</section>

<section class="zv-member zv-panel">
  <div class="zv-member__card">zvij.si<br><span>ČLAN</span></div>
  <div>
    <p class="zv-kicker">Član Zvij.si</p>
    <h2 class="zv-member__slogan">Zvijače za zvijače.</h2>
    <p>Lažji ponovni nakupi in ugodnosti za člane. Ko postaneš član, se pridružiš tudi naši e-listi.</p>
  </div>
  <a class="button" href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>">Postani član</a>
</section>

<section class="zv-trust">
  <div><b>Diskretno</b><span>Tvoj paket, tvoja stvar.</span></div>
  <div><b>Domača roba</b><span>Iz Slovenije.</span></div>
  <div><b>Sledi nam</b><span>@zvij.si</span></div>
</section>

<?php
get_footer();
