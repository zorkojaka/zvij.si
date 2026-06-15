<?php
/**
 * Front page.
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

$pillars = [
    ['DUBI filtri', 'dubi-filtri', '42 ali 420 filtrov za urejen setup in jasen reload ritem.'],
    ['CBD/CBG vršički', 'cbd-vrsicki', 'SMOKEY, CHILLY in FRUTTY kot 1 g izbira ali 5 g paket.'],
    ['Zvij setup', 'zvij-setup', 'DUBI 42, rolca in sample za prvi dev nakup.'],
    ['Reload', 'trgovina', '5 g paket vršičkov vsebuje 5 posameznih 1 g pakiranj.'],
];
?>
<section class="hero hero--home">
  <div class="hero__body">
    <p class="eyebrow"><?php esc_html_e('Zvij.si Kit', 'zvij-theme'); ?></p>
    <h1><?php esc_html_e('Vse, kar rabiš, da si zviješ.', 'zvij-theme'); ?></h1>
    <p class="hero-copy"><?php esc_html_e('Black, Silver, Gold ali Throwie — sestavljen setup z DUBI filtri. Vršički po želji.', 'zvij-theme'); ?></p>
    <div class="button-row">
      <a class="button" href="#kiti"><?php esc_html_e('Poglej kite', 'zvij-theme'); ?></a>
      <a class="button button--ghost" href="<?php echo esc_url(home_url('/trgovina/')); ?>"><?php esc_html_e('Poglej trgovino', 'zvij-theme'); ?></a>
    </div>
  </div>
  <div class="ritual-note" aria-label="<?php esc_attr_e('Prototype note', 'zvij-theme'); ?>">
    <span><?php esc_html_e('Zvij.si Kit', 'zvij-theme'); ?></span>
    <strong><?php esc_html_e('stil, ne cenovni razred', 'zvij-theme'); ?></strong>
    <p><?php esc_html_e('Black, Silver in Gold so stil. Throwie je utility setup za s sabo. DUBI filtri so v vsakem kitu.', 'zvij-theme'); ?></p>
  </div>
</section>

<?php zvij_render_kit_showcase(); ?>

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
    <h2><?php esc_html_e('Član Zvij.si', 'zvij-theme'); ?></h2>
    <p class="section-tagline"><?php esc_html_e('Zvijače za zvijače.', 'zvij-theme'); ?></p>
  </div>
  <div class="text-stack">
    <p><?php esc_html_e('Prvi nakup je lahek preizkus. Članstvo je praktičen del sistema: dobiš svojo Zvij kodo, vidiš dobroimetje in lažje prideš nazaj na naslednji reload.', 'zvij-theme'); ?></p>
    <p><?php esc_html_e('Cilj ni lovljenje akcij, ampak urejen reload sistem. Brez preprodaje, brez cash payout obljub, brez hrupa.', 'zvij-theme'); ?></p>
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
    <p class="eyebrow"><?php esc_html_e('Setup dodatki', 'zvij-theme'); ?></p>
    <h2><?php esc_html_e('Majhne stvari, ki jih imaš vedno pri sebi.', 'zvij-theme'); ?></h2>
  </div>
  <div class="text-stack">
    <p><?php esc_html_e('Od rezervnega vžigalnika do premium Clipperja — majhne stvari, ki jih imaš vedno pri sebi.', 'zvij-theme'); ?></p>
    <p><?php esc_html_e('Dodatki ostajajo del setup kulture: uporabni, odrasli in dovolj premišljeni, da podprejo Član Zvij.si sistem.', 'zvij-theme'); ?></p>
    <a class="text-link" href="<?php echo esc_url(home_url('/trgovina/')); ?>"><?php esc_html_e('Poglej shop prototip', 'zvij-theme'); ?></a>
  </div>
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
