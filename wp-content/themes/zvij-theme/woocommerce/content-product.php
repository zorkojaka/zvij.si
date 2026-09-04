<?php
/**
 * Zvij.si product card for shop/archive/related loops.
 *
 * Nadomešča WooCommerce content-product.php (@version 9.4.0) z eksplicitno
 * strukturo po vzoru domačega karusela: slikovni pas čez celo širino kartice
 * zgoraj (značke kot overlay), telo s podatki spodaj, nakupni blok poravnan
 * na dno prek grid vrstic — brez auto marginov, ker Chromium z njimi napačno
 * izračuna višino grid vrstice v ul.products.
 */

defined('ABSPATH') || exit;

global $product;

if (! is_a($product, WC_Product::class) || ! $product->is_visible()) {
    return;
}

$zvij_permalink = get_the_permalink();

$zvij_terms = get_the_terms($product->get_id(), 'product_cat');
$zvij_cat_label = (empty($zvij_terms) || is_wp_error($zvij_terms)) ? '' : $zvij_terms[0]->name;

$zvij_first_purchase_badge = (string) get_post_meta($product->get_id(), '_zvij_first_purchase_badge', true);
$zvij_dobroimetje_note = function_exists('zvij_credit_public_note')
    ? zvij_credit_public_note($product)
    : (string) get_post_meta($product->get_id(), '_zvij_dobroimetje_note', true);
?>
<li <?php wc_product_class('zv-pcard', $product); ?>>
  <a class="zv-pcard__media" href="<?php echo esc_url($zvij_permalink); ?>" aria-hidden="true" tabindex="-1">
    <?php woocommerce_show_product_loop_sale_flash(); ?>
    <?php if ($zvij_first_purchase_badge !== '') : ?>
      <span class="product-card__badge"><?php echo esc_html($zvij_first_purchase_badge); ?></span>
    <?php endif; ?>
    <?php echo woocommerce_get_product_thumbnail(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
  </a>
  <div class="zv-pcard__body">
    <div class="zv-pcard__info">
      <?php if ($zvij_cat_label !== '') : ?>
        <span class="product-card__cat"><?php echo esc_html($zvij_cat_label); ?></span>
      <?php endif; ?>
      <h2 class="woocommerce-loop-product__title"><a href="<?php echo esc_url($zvij_permalink); ?>"><?php the_title(); ?></a></h2>
      <?php if ($zvij_dobroimetje_note !== '') : ?>
        <p class="product-card__credit"><?php echo esc_html($zvij_dobroimetje_note); ?></p>
      <?php endif; ?>
    </div>
    <div class="zv-pcard__buy">
      <?php zvij_product_loop_buy_block($product); ?>
    </div>
  </div>
</li>
