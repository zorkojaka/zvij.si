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
$kit_filters = [
    'black-kit' => 'Black Kit',
    'silver-kit' => 'Silver Kit',
    'gold-kit' => 'Gold Kit',
    'throwie' => 'Throwie',
    'reload' => 'Reload',
];
?>
<nav class="kit-filter" aria-label="<?php esc_attr_e('Filtri kitov', 'zvij-theme'); ?>">
  <a class="kit-filter__chip<?php echo (is_shop() && ! is_tax('product_tag')) ? ' is--active' : ''; ?>" href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"><?php esc_html_e('Vse', 'zvij-theme'); ?></a>
  <?php foreach ($kit_filters as $tag_slug => $tag_label) :
      $term = get_term_by('slug', $tag_slug, 'product_tag');
      if (! $term) {
          continue;
      }
      $active = is_tax('product_tag', $tag_slug);
  ?>
    <a class="kit-filter__chip<?php echo $active ? ' is--active' : ''; ?>" href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($tag_label); ?></a>
  <?php endforeach; ?>
</nav>

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
