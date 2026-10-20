<?php
/**
 * Moj račun → nadzorna plošča: kratek pozdrav, nato kartice (stopnja,
 * kristali, izzivi, zadnje naročilo, Zvij koda) iz zvij-core.
 *
 * Prepisuje woocommerce/templates/myaccount/dashboard.php (4.4.0).
 *
 * @version 4.4.0
 */

if (! defined('ABSPATH')) {
    exit;
}

$zv_links = [
    'orders'       => __('Naročila', 'zvij-theme'),
    'edit-address' => __('Naslovi', 'zvij-theme'),
    'edit-account' => __('Podrobnosti računa', 'zvij-theme'),
];
?>
<div class="zv-acc-grid">
  <section class="zv-acc-card zv-acc-card--wide zv-acc-hello">
    <?php if (function_exists('zvij_account_placeholder')) { echo zvij_account_placeholder(__('pozdrav', 'zvij-theme')); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <div class="zv-acc-body">
      <h2><?php echo esc_html(sprintf(__('Živijo, %s', 'zvij-theme'), $current_user->display_name)); ?></h2>
      <p><?php esc_html_e('Tvoja stopnja, kristali in povabila na enem mestu.', 'zvij-theme'); ?></p>
      <nav class="zv-acc-links" aria-label="<?php esc_attr_e('Hitre povezave', 'zvij-theme'); ?>">
        <?php foreach ($zv_links as $endpoint => $label) : ?>
          <a href="<?php echo esc_url(wc_get_endpoint_url($endpoint)); ?>"><?php echo esc_html($label); ?></a>
        <?php endforeach; ?>
        <a class="zv-acc-links__logout" href="<?php echo esc_url(wc_logout_url()); ?>"><?php esc_html_e('Odjava', 'zvij-theme'); ?></a>
      </nav>
    </div>
  </section>
  <?php
  /** @since 2.6.0 */
  do_action('woocommerce_account_dashboard');
  /** @deprecated 2.6.0 */
  do_action('woocommerce_before_my_account');
  /** @deprecated 2.6.0 */
  do_action('woocommerce_after_my_account');
  ?>
</div>
