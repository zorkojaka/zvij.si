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
    ['CBD čaj', 'cbd-caj', 'SMOKEY, CHILLY in FRUTTY kot 1 g izbira ali 5 g paket.'],
    ['Zvij setup', 'zvij-setup', 'Sample in setup paket za prvi dev nakup.'],
    ['Refill', 'trgovina', '5 g čaj je izpolnjen kot 5 posameznih 1 g pakiranj.'],
];
?>
<section class="hero hero--home">
  <div class="hero__body">
    <p class="eyebrow"><?php esc_html_e('Zvij.si Dev', 'zvij-theme'); ?></p>
    <h1><?php esc_html_e('Tvoj ritual. Tvoja mera. Tvoj setup.', 'zvij-theme'); ?></h1>
    <p class="hero-copy"><?php esc_html_e('Urejena trgovina za izdelke, refille in članstvo okoli tvojega rituala.', 'zvij-theme'); ?></p>
    <div class="button-row">
      <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>"><?php esc_html_e('Poglej trgovino', 'zvij-theme'); ?></a>
      <a class="button button--ghost" href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>"><?php esc_html_e('Postani član', 'zvij-theme'); ?></a>
    </div>
  </div>
  <div class="ritual-note" aria-label="<?php esc_attr_e('Prototype note', 'zvij-theme'); ?>">
    <span><?php esc_html_e('dev prototip', 'zvij-theme'); ?></span>
    <strong><?php esc_html_e('setup pred checkoutom', 'zvij-theme'); ?></strong>
    <p><?php esc_html_e('Realne dev cene so vnesene. CBD/CBG izdelki ostajajo opisani kot čaj in brez zdravstvenih obljub.', 'zvij-theme'); ?></p>
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

<section class="split-section">
  <div>
    <p class="eyebrow"><?php esc_html_e('Član Zvij.si', 'zvij-theme'); ?></p>
    <h2><?php esc_html_e('Zvij koda, dobroimetje in refilli kot prihodnji notranji sloj.', 'zvij-theme'); ?></h2>
  </div>
  <div class="text-stack">
    <p><?php esc_html_e('Članstvo je zamišljeno kot praktičen del sistema: dobiš svojo Zvij kodo, vidiš dobroimetje in lažje prideš nazaj do naslednjega refilla.', 'zvij-theme'); ?></p>
    <p><?php esc_html_e('Referral in dobroimetje logika sta še v specifikaciji. Brez preprodaje, brez cash payout obljub, brez hrupa.', 'zvij-theme'); ?></p>
    <a class="text-link" href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>"><?php esc_html_e('Poglej članstvo', 'zvij-theme'); ?></a>
  </div>
</section>

<section class="feature-band">
  <div class="feature-band__content">
    <p class="eyebrow"><?php esc_html_e('Best starter setup', 'zvij-theme'); ?></p>
    <h2><?php esc_html_e('DUBI 42 + rolca + sample čaja.', 'zvij-theme'); ?></h2>
    <p><?php esc_html_e('Zvij setup paket je dev bundle za preverjanje prvega nakupa. Vsebina je označena kot dev, dokler Jaka ne potrdi končne sestave.', 'zvij-theme'); ?></p>
  </div>
  <a class="button button--light" href="<?php echo esc_url(home_url('/zvij-setup/')); ?>"><?php esc_html_e('Sestavi setup', 'zvij-theme'); ?></a>
</section>

<section class="split-section split-section--quiet">
  <div>
    <p class="eyebrow"><?php esc_html_e('CBD čaj', 'zvij-theme'); ?></p>
    <h2><?php esc_html_e('Mirnejši ritual brez THC učinka.', 'zvij-theme'); ?></h2>
  </div>
  <div class="text-stack">
    <p><?php esc_html_e('SMOKEY, CHILLY in FRUTTY so v dev katalogu predstavljeni kot čaj. 1 g je osnovno pakiranje; 5 g paket se izpolni kot 5 posameznih 1 g pakiranj.', 'zvij-theme'); ?></p>
    <a class="text-link" href="<?php echo esc_url(home_url('/cbd-caj/')); ?>"><?php esc_html_e('Preberi o CBD čaju', 'zvij-theme'); ?></a>
  </div>
</section>

<section class="footer-cta">
  <p class="eyebrow"><?php esc_html_e('Naslednji korak', 'zvij-theme'); ?></p>
  <h2><?php esc_html_e('Začni s svojim setupom.', 'zvij-theme'); ?></h2>
  <a class="button" href="<?php echo esc_url(home_url('/trgovina/')); ?>"><?php esc_html_e('Odpri trgovino', 'zvij-theme'); ?></a>
</section>
<?php
get_footer();
