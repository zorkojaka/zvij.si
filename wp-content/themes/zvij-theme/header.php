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
    <img class="site-brand__logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/logo-zvij-outline.svg'); ?>" width="96" height="87" alt="<?php esc_attr_e('zvij.si', 'zvij-theme'); ?>">
  </a>
  <nav class="site-nav" aria-label="<?php esc_attr_e('Main menu', 'zvij-theme'); ?>">
    <ul>
      <li><a href="<?php echo esc_url(home_url('/')); ?>">Domov</a></li>
      <li><a href="<?php echo esc_url(home_url('/trgovina/')); ?>">Trgovina</a></li>
      <li><a href="<?php echo esc_url(home_url('/kiti/')); ?>">Kiti</a></li>
      <?php if (! function_exists('zvij_reload_is_public') || zvij_reload_is_public()) : ?>
        <li><a href="<?php echo esc_url(home_url('/reload/')); ?>">Reload</a></li>
      <?php endif; ?>
      <li><a href="<?php echo esc_url(home_url('/o-nas/')); ?>">O nas</a></li>
    </ul>
  </nav>
  <div class="site-actions" aria-label="<?php esc_attr_e('Hitre akcije', 'zvij-theme'); ?>">
    <button class="site-ico zv-theme-toggle" type="button" aria-label="<?php esc_attr_e('Vklopi temno temo', 'zvij-theme'); ?>" title="<?php esc_attr_e('Vklopi temno temo', 'zvij-theme'); ?>" aria-pressed="false" hidden><span aria-hidden="true"></span></button>
    <a class="site-ico" href="<?php echo esc_url(home_url('/?s=')); ?>" aria-label="<?php esc_attr_e('Iskanje', 'zvij-theme'); ?>">
      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.6-3.6"/></svg>
    </a>
    <a class="site-ico" href="<?php echo esc_url(home_url('/moj-racun/')); ?>" aria-label="<?php esc_attr_e('Moj račun', 'zvij-theme'); ?>">
      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
    </a>
    <a class="site-ico site-cart" href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/kosarica/')); ?>" aria-label="<?php esc_attr_e('Grinder', 'zvij-theme'); ?>">
      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 7h16l-1.4 11.2a2 2 0 0 1-2 1.8H7.4a2 2 0 0 1-2-1.8z"/><path d="M9 7V5.5a3 3 0 0 1 6 0V7"/></svg>
      <b><?php echo esc_html(function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0); ?></b>
    </a>
    <?php if (is_user_logged_in()) :
      $balance = function_exists('zvij_credit_balance') ? zvij_credit_balance(wp_get_current_user()->user_email) : 0;
      $balance_label = function_exists('zvij_kristali_izpis') ? zvij_kristali_izpis($balance) : $balance . ' kristalov';
    ?>
    <a class="button button--lime site-member-balance" style="display:inline-flex;min-width:0;width:auto;white-space:nowrap;padding:8px;font-size:14px;line-height:1.2" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" title="Prijavljen član: tvoj račun in kristali" aria-label="<?php echo esc_attr('Prijavljen član. ' . $balance_label . '. Odpri moj račun.'); ?>"><?php echo esc_html($balance_label); ?></a>
    <?php else : ?>
    <a class="button button--lime site-join" href="<?php echo esc_url(home_url('/clan-zvij-si/')); ?>"><?php esc_html_e('Postani član', 'zvij-theme'); ?></a>
    <?php endif; ?>
  </div>
</header>
<main class="site-main">
