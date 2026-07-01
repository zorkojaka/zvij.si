<?php
/**
 * Site header.
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
  <a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>">
    <img class="site-brand__logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/logo-small.png'); ?>" alt="<?php esc_attr_e('zvij.si', 'zvij-theme'); ?>">
  </a>
  <nav class="site-nav" aria-label="<?php esc_attr_e('Main menu', 'zvij-theme'); ?>">
    <ul>
      <li><a href="<?php echo esc_url(home_url('/')); ?>">Domov</a></li>
      <li><a href="<?php echo esc_url(home_url('/trgovina/')); ?>">Shop</a></li>
      <li><a href="<?php echo esc_url(home_url('/kiti/')); ?>">Kiti</a></li>
      <li><a href="<?php echo esc_url(home_url('/reload/')); ?>">Reload</a></li>
      <li><a href="<?php echo esc_url(home_url('/o-nas/')); ?>">O nas</a></li>
    </ul>
  </nav>
  <div class="site-actions" aria-label="<?php esc_attr_e('Hitre akcije', 'zvij-theme'); ?>">
    <a href="<?php echo esc_url(home_url('/?s=')); ?>" aria-label="<?php esc_attr_e('Iskanje', 'zvij-theme'); ?>">&#128269;</a>
    <a href="<?php echo esc_url(home_url('/moj-racun/')); ?>" aria-label="<?php esc_attr_e('Moj račun', 'zvij-theme'); ?>">&#128100;</a>
    <a class="site-cart" href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/kosarica/')); ?>" aria-label="<?php esc_attr_e('Košarica', 'zvij-theme'); ?>">
      <span>&#128717;</span><b><?php echo esc_html(function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0); ?></b>
    </a>
  </div>
</header>
<main class="site-main">
