<?php
/**
 * Main theme template.
 */

if (! defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
  <a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
  <nav class="site-nav" aria-label="<?php esc_attr_e('Main menu', 'zvij-theme'); ?>">
    <?php
    wp_nav_menu([
        'theme_location' => 'primary',
        'container' => false,
        'fallback_cb' => false,
        'depth' => 1,
    ]);
    ?>
  </nav>
</header>
<main class="site-main">
  <?php if (is_front_page()) : ?>
    <section class="hero" aria-label="<?php esc_attr_e('Zvij.si Dev homepage', 'zvij-theme'); ?>">
      <p class="eyebrow"><?php esc_html_e('Zvij.si Dev', 'zvij-theme'); ?></p>
      <h1><?php esc_html_e('Tvoj ritual. Tvoja mera. Tvoj setup.', 'zvij-theme'); ?></h1>
      <p class="hero-copy"><?php esc_html_e('Mirna razvojna osnova za izdelke, refille in clanstvo Zvij.si. Brez velikih obljub, samo bolj urejen sistem okoli rituala.', 'zvij-theme'); ?></p>
      <div class="hero-actions">
        <a href="<?php echo esc_url(home_url('/trgovina/')); ?>"><?php esc_html_e('Poglej trgovino', 'zvij-theme'); ?></a>
        <a href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>"><?php esc_html_e('Postani clan', 'zvij-theme'); ?></a>
      </div>
    </section>

    <section class="route-grid" aria-label="<?php esc_attr_e('Zvij.si routes', 'zvij-theme'); ?>">
      <?php
      $routes = [
          ['Član Zvij.si', 'clan-zvij-si', 'Zvij koda, dobroimetje in refilli kot miren notranji sloj sistema.'],
          ['DUBI filtri', 'dubi-filtri', 'Osnovni kos za cistejsi setup in bolj predvidljivo pripravo.'],
          ['CBD čaj', 'cbd-caj', 'Posuseni konopljini vrsicki za caj, ko zelis ritual ohraniti mirnejsi.'],
          ['Zvij setup', 'zvij-setup', 'Paketni pogled na pripomocke, refille in rutino okoli prvega nakupa.'],
      ];

      foreach ($routes as [$title, $slug, $copy]) :
      ?>
        <article class="route-card">
          <h2><a href="<?php echo esc_url(home_url('/' . $slug . '/')); ?>"><?php echo esc_html($title); ?></a></h2>
          <p><?php echo esc_html($copy); ?></p>
        </article>
      <?php endforeach; ?>
    </section>
  <?php else : ?>
    <section class="content-panel">
      <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
          <article <?php post_class(); ?>>
            <h1><?php the_title(); ?></h1>
            <div class="entry-content">
              <?php the_content(); ?>
            </div>
          </article>
        <?php endwhile; ?>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</main>
<?php wp_footer(); ?>
</body>
</html>
