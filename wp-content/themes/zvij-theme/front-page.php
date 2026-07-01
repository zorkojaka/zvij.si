<?php
/**
 * Front page - Zvij.si visual shop prototype.
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

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

<?php
if (function_exists('zvij_render_homepage_product_carousel')) {
    zvij_render_homepage_product_carousel();
}
?>

<?php
// Image-led Kiti and Reload blocks. Product-family cards are handled by the
// homepage carousel above.
$kiti_href  = home_url('/kiti/');
$kit_colors = [];
foreach ($kits as $kit) {
    $cimg = zvij_home_block_img('kiti-' . $kit['key']);
    $kit_colors[] = [
        'key'  => $kit['key'],
        'name' => $kit['name'],
        'img'  => $cimg !== '' ? $cimg : zvij_kit_flatlay_url($kit['key']),
    ];
}
$kiti_default = $kit_colors[0]['img'] ?? '';

$reload_bg = zvij_home_block_img('reload');
$reload_bg = $reload_bg !== '' ? $reload_bg : zvij_kit_flatlay_url('throwie');
?>
<section class="zv-editorial" id="ponudba">
  <article class="zv-edit zv-edit--full zv-edit--kit" data-kitsel>
    <?php if ($kiti_default !== '') : ?><img class="zv-edit__bg" data-kit-visual src="<?php echo esc_url($kiti_default); ?>" alt="Kiti Zvij.si" loading="lazy"><?php endif; ?>
    <div class="zv-edit__shade zv-edit__shade--strong"></div>
    <div class="zv-edit__copy">
      <p class="zv-edit__kicker">Kiti Zvij.si</p>
      <h2>En kit. Tvoj stil.</h2>
      <p class="zv-edit__sub">Black · Silver · Gold</p>
      <div class="zv-edit__colors">
        <?php foreach ($kit_colors as $i => $c) : ?>
          <button type="button" class="zv-color-dot zv-color-dot--<?php echo esc_attr($c['key']); ?><?php echo $i === 0 ? ' on' : ''; ?>" data-color data-img="<?php echo esc_url($c['img']); ?>" data-href="<?php echo esc_url($kiti_href); ?>" data-name="<?php echo esc_attr($c['name']); ?>" aria-label="<?php echo esc_attr($c['name']); ?>"></button>
        <?php endforeach; ?>
      </div>
      <a class="button button--on-image" data-kit-link href="<?php echo esc_url($kiti_href); ?>">Poglej kite</a>
    </div>
  </article>

  <article class="zv-edit zv-edit--full zv-edit--reload">
    <?php if ($reload_bg !== '') : ?><img class="zv-edit__bg" src="<?php echo esc_url($reload_bg); ?>" alt="Reload" loading="lazy"><?php endif; ?>
    <div class="zv-edit__shade zv-edit__shade--strong"></div>
    <div class="zv-edit__copy">
      <h2>Ko zmanjka, samo dopolni.</h2>
      <p class="zv-edit__tags">DUBI · Vršički · Rizle / rolce · Vžigalniki · Ostalo</p>
      <a class="button button--on-image" href="<?php echo esc_url(home_url('/reload/')); ?>">Poglej reload</a>
    </div>
  </article>
</section>

<section class="zv-benefits">
  <div><b>Domača podpora</b><span>Tukaj smo zate.</span></div>
  <div><b>Diskretno pakiranje</b><span>Brez oznak.</span></div>
  <div><b>Hitra dostava</b><span>1-2 dni.</span></div>
</section>

<section class="zv-member zv-panel">
  <div class="zv-member__card">zvij.si<br><span>ČLAN</span></div>
  <div>
    <p class="zv-kicker">Član Zvij.si</p>
    <h2 class="zv-member__slogan">Postani član Zvij.si</h2>
    <p>Prejmi kodo za prvi nakup. Na mail ti bomo občasno poslali tudi kakšno Zvijačo za zvijače.</p>
  </div>
  <div class="zv-member__form">
    <?php echo function_exists('zvij_membership_form') ? zvij_membership_form(['source' => 'homepage']) : '<a class="button" href="' . esc_url(home_url('/clan-zvij-si/')) . '">Postani član</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
  </div>
</section>

<section class="zv-trust">
  <div><b>Domača podpora</b><span>Tukaj smo zate.</span></div>
  <div><b>Diskretno pakiranje</b><span>Brez oznak.</span></div>
  <div><b>Hitra dostava</b><span>1-2 dni.</span></div>
</section>

<?php
get_footer();
