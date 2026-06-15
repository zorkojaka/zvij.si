<?php
/**
 * Front page.
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

$pillars = [
    ['DUBI filtri', 'dubi-filtri', '42 ali 420 filtrov za urejen setup in jasen refill ritem.'],
    ['CBD/CBG vršički', 'cbd-vrsicki', 'SMOKEY, CHILLY in FRUTTY kot 1 g izbira ali 5 g paket.'],
    ['Zvij setup', 'zvij-setup', 'DUBI 42, rolca in sample za prvi dev nakup.'],
    ['Refill', 'trgovina', '5 g paket vršičkov vsebuje 5 posameznih 1 g pakiranj.'],
];
?>
<section class="hero hero--home">
  <div class="hero__body">
    <p class="eyebrow"><?php esc_html_e('Zvij.si Dev', 'zvij-theme'); ?></p>
    <h1><?php esc_html_e('Tvoj ritual. Tvoja mera. Tvoj setup.', 'zvij-theme'); ?></h1>
    <p class="hero-copy"><?php esc_html_e('Filtri, vršički, setupi in refilli za bolj urejen ritual.', 'zvij-theme'); ?></p>
    <div class="button-row">
      <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>"><?php esc_html_e('Poglej trgovino', 'zvij-theme'); ?></a>
      <a class="button button--ghost" href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>"><?php esc_html_e('Postani član', 'zvij-theme'); ?></a>
    </div>
  </div>
  <div class="ritual-note" aria-label="<?php esc_attr_e('Prototype note', 'zvij-theme'); ?>">
    <span><?php esc_html_e('dev prototip', 'zvij-theme'); ?></span>
    <strong><?php esc_html_e('setup pred checkoutom', 'zvij-theme'); ?></strong>
    <p><?php esc_html_e('Vršički, filtri in setupi z jasno mero. Čajna uporaba je opisana kot možnost, ne kot celotna identiteta izdelka.', 'zvij-theme'); ?></p>
  </div>
</section>

<section class="section-block">
  <div class="section-heading">
    <p class="eyebrow"><?php esc_html_e('Product pillars', 'zvij-theme'); ?></p>
    <h2><?php esc_html_e('Štiri poti v isti sistem.', 'zvij-theme'); ?></h2>
  </div>
  <div class="card-grid card-grid--four">
    <?php foreach ($pillars as [$title, $slug, $copy]) : ?>
      <article class="route-card">
        <span class="card-kicker"><?php echo esc_html($title); ?></span>
        <h3><a href="<?php echo esc_url(home_url('/' . $slug . '/')); ?>"><?php echo esc_html($title); ?></a></h3>
        <p><?php echo esc_html($copy); ?></p>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="feature-band feature-band--warm">
  <div class="feature-band__content">
    <p class="eyebrow"><?php esc_html_e('Prvi preizkus', 'zvij-theme'); ?></p>
    <h2><?php esc_html_e('Prvi FRUTTY za 4,20 €.', 'zvij-theme'); ?></h2>
    <p><?php esc_html_e('Lahek prvi preizkus. Če ti sede naš ritual, se naslednjič vrneš z dobroimetjem.', 'zvij-theme'); ?></p>
  </div>
  <a class="button button--light" href="<?php echo esc_url(home_url('/product/frutty-cbd-vrsicki-1-g/')); ?>"><?php esc_html_e('Poglej FRUTTY', 'zvij-theme'); ?></a>
</section>

<section class="split-section">
  <div>
    <p class="eyebrow"><?php esc_html_e('Član Zvij.si', 'zvij-theme'); ?></p>
    <h2><?php esc_html_e('Prvi nakup je preizkus. Naslednji naj bo refill.', 'zvij-theme'); ?></h2>
  </div>
  <div class="text-stack">
    <p><?php esc_html_e('Članstvo je praktičen del sistema: dobiš svojo Zvij kodo, vidiš dobroimetje in lažje prideš nazaj do naslednjega refilla.', 'zvij-theme'); ?></p>
    <p><?php esc_html_e('Cilj ni lovljenje akcij, ampak urejen refill sistem. Brez preprodaje, brez cash payout obljub, brez hrupa.', 'zvij-theme'); ?></p>
    <a class="text-link" href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>"><?php esc_html_e('Poglej članstvo', 'zvij-theme'); ?></a>
  </div>
</section>

<section class="feature-band">
  <div class="feature-band__content">
    <p class="eyebrow"><?php esc_html_e('Best starter setup', 'zvij-theme'); ?></p>
    <h2><?php esc_html_e('DUBI 42 + rolca + sample vršičkov.', 'zvij-theme'); ?></h2>
    <p><?php esc_html_e('Zvij setup paket je dev bundle za preverjanje prvega nakupa. Vsebina je označena kot dev, dokler Jaka ne potrdi končne sestave.', 'zvij-theme'); ?></p>
  </div>
  <a class="button button--light" href="<?php echo esc_url(home_url('/zvij-setup/')); ?>"><?php esc_html_e('Sestavi setup', 'zvij-theme'); ?></a>
</section>

<section class="split-section split-section--quiet">
  <div>
    <p class="eyebrow"><?php esc_html_e('CBD/CBG vršički', 'zvij-theme'); ?></p>
    <h2><?php esc_html_e('SMOKEY, CHILLY, FRUTTY.', 'zvij-theme'); ?></h2>
  </div>
  <div class="text-stack">
    <p><?php esc_html_e('Premium vršički v jasni meri: 1 g pakiranje ali 5 g paket kot 5 posameznih 1 g pakiranj. Čajna uporaba je opisana kot ena od možnosti.', 'zvij-theme'); ?></p>
    <a class="text-link" href="<?php echo esc_url(home_url('/cbd-vrsicki/')); ?>"><?php esc_html_e('Preberi o vršičkih', 'zvij-theme'); ?></a>
  </div>
</section>

<section class="footer-cta">
  <p class="eyebrow"><?php esc_html_e('Naslednji korak', 'zvij-theme'); ?></p>
  <h2><?php esc_html_e('Začni s svojim setupom.', 'zvij-theme'); ?></h2>
  <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>"><?php esc_html_e('Odpri trgovino', 'zvij-theme'); ?></a>
</section>
<?php
get_footer();
