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
<?php if (! zvij_shop_grouped_view()) : ?>
<nav class="kit-filter" aria-label="<?php esc_attr_e('Vrste izdelkov', 'zvij-theme'); ?>">
  <a class="kit-filter__chip<?php echo (is_shop() && $current_group === '') ? ' is--active' : ''; ?>" <?php echo (is_shop() && $current_group === '') ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Vse', 'zvij-theme'); ?></a>
  <?php foreach (zvij_shop_groups() as $key => $group) :
      $active = $current_group === $key || is_product_category($group['categories']);
  ?>
    <a class="kit-filter__chip<?php echo $active ? ' is--active' : ''; ?>" <?php echo $active ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('zvij_group', $key, $shop_url)); ?>"><?php echo esc_html($group['label']); ?></a>
  <?php endforeach; ?>
</nav>
<?php endif; ?>

<?php
// Vodič po formatih na kategorijah, kjer je relevanten (v razdelkih je pri Ziggi).
if (is_tax('product_cat', ['rizle', 'rolce']) || zvij_current_shop_group() === 'ziggi') {
    echo zvij_paper_format_guide(); // phpcs:ignore WordPress.Security.EscapeOutput -- lastni markup
}
?>

<?php if (woocommerce_product_loop() && zvij_shop_grouped_view()) : ?>
  <?php
  // Vsi izdelki na eni strani, razdeljeni po skupinah; plavajoča vrstica vodi do razdelkov.
  $zvij_sections = array_fill_keys(array_keys(zvij_shop_groups()), []);
  $zvij_sections['ostalo'] = [];
  while (have_posts()) {
      the_post();
      $key = zvij_shop_group_of(get_the_ID());
      $zvij_sections[$key !== '' ? $key : 'ostalo'][] = get_the_ID();
  }
  $zvij_sections = array_filter($zvij_sections);
    ?>
  <nav class="zv-shop-jump" aria-label="<?php esc_attr_e('Skoči na skupino izdelkov', 'zvij-theme'); ?>" data-shop-jump>
    <?php foreach ($zvij_sections as $key => $ids) :
        $group = zvij_shop_groups()[$key] ?? ['label' => __('Ostalo', 'zvij-theme'), 'short' => __('Ostalo', 'zvij-theme')];
        $icon = get_the_post_thumbnail_url($ids[0], 'thumbnail');
    ?>
      <a class="zv-shop-jump__item" href="#skupina-<?php echo esc_attr($key); ?>" data-jump="<?php echo esc_attr($key); ?>">
        <?php if ($icon) : ?><img src="<?php echo esc_url($icon); ?>" alt="" width="44" height="44" loading="lazy"><?php endif; ?>
        <span><?php echo esc_html($group['short']); ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <?php foreach ($zvij_sections as $key => $ids) :
      $group = zvij_shop_groups()[$key] ?? ['label' => __('Ostalo', 'zvij-theme')];
  ?>
    <section class="zv-shop-group" id="skupina-<?php echo esc_attr($key); ?>" aria-labelledby="skupina-<?php echo esc_attr($key); ?>-naslov" data-shop-group="<?php echo esc_attr($key); ?>">
      <h2 class="zv-shop-group__title" id="skupina-<?php echo esc_attr($key); ?>-naslov"><?php echo esc_html($group['label']); ?> <span><?php echo esc_html((string) count($ids)); ?></span></h2>
      <?php woocommerce_product_loop_start(); ?>
        <?php foreach ($ids as $id) :
            $GLOBALS['post'] = get_post($id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
            setup_postdata($GLOBALS['post']);
            wc_get_template_part('content', 'product');
        endforeach; ?>
      <?php woocommerce_product_loop_end(); ?>
    </section>
  <?php endforeach; ?>
  <?php wp_reset_postdata(); ?>
<?php elseif (woocommerce_product_loop()) : ?>
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
