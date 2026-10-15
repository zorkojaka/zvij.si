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
  <h1><?php woocommerce_page_title(); ?></h1>
  <p><?php esc_html_e('Izboljšaj svojo rutino. Izberi, kar rabiš.', 'zvij-theme'); ?></p>
</section>

<?php
$shop_url = get_permalink(wc_get_page_id('shop'));
$current_group = zvij_current_shop_group();
?>
<nav class="kit-filter" aria-label="<?php esc_attr_e('Vrste izdelkov', 'zvij-theme'); ?>">
  <a class="kit-filter__chip<?php echo (is_shop() && $current_group === '') ? ' is--active' : ''; ?>" <?php echo (is_shop() && $current_group === '') ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Vse', 'zvij-theme'); ?></a>
  <?php foreach (zvij_shop_groups() as $key => $group) :
      $active = $current_group === $key || is_product_category($group['categories']);
  ?>
    <a class="kit-filter__chip<?php echo $active ? ' is--active' : ''; ?>" <?php echo $active ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('zvij_group', $key, $shop_url)); ?>"><?php echo esc_html($group['label']); ?></a>
  <?php endforeach; ?>
</nav>

<?php
// Vodič po formatih na kategorijah, kjer je relevanten.
if (is_tax('product_cat', ['rizle', 'rolce'])) {
    echo zvij_paper_format_guide(); // phpcs:ignore WordPress.Security.EscapeOutput -- lastni markup
}
?>

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
