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
<main class="site-main">
  <section class="home-panel" aria-label="<?php esc_attr_e('Development homepage', 'zvij-theme'); ?>">
    <h1><?php esc_html_e('Zvij.si dev', 'zvij-theme'); ?></h1>
    <p><?php esc_html_e('Clean WordPress and WooCommerce development environment.', 'zvij-theme'); ?></p>
  </section>
</main>
<?php wp_footer(); ?>
</body>
</html>
