<?php
/**
 * WooCommerce shop and product archive template.
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>
<section class="shop-hero">
  <p class="eyebrow"><?php esc_html_e('Trgovina', 'zvij-theme'); ?></p>
  <h1><?php woocommerce_page_title(); ?></h1>
  <p><?php esc_html_e('DEV prototip trgovine. Izdelki so placeholderji, dokler Jaka ne potrdi podatkov, cen, pravnih besedil in checkout pravil.', 'zvij-theme'); ?></p>
</section>

<?php if (woocommerce_product_loop()) : ?>
  <div class="shop-toolbar">
    <?php woocommerce_result_count(); ?>
    <?php woocommerce_catalog_ordering(); ?>
  </div>
  <?php woocommerce_product_loop_start(); ?>
    <?php while (have_posts()) : ?>
      <?php the_post(); ?>
      <?php wc_get_template_part('content', 'product'); ?>
    <?php endwhile; ?>
  <?php woocommerce_product_loop_end(); ?>
  <?php woocommerce_pagination(); ?>
<?php else : ?>
  <section class="empty-shop">
    <h2><?php esc_html_e('Trgovina je trenutno v prototipu.', 'zvij-theme'); ?></h2>
    <p><?php esc_html_e('Javni izdelki se prikažejo tukaj, ko so placeholderji ali realni podatki objavljeni.', 'zvij-theme'); ?></p>
  </section>
<?php endif; ?>
<?php
get_footer();
