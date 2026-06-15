<?php
/**
 * Zvij Theme functions.
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');
    add_post_type_support('page', 'excerpt');

    register_nav_menus([
        'primary' => __('Primary menu', 'zvij-theme'),
    ]);
});

add_action('wp_enqueue_scripts', function (): void {
    wp_enqueue_style('zvij-theme-style', get_stylesheet_uri(), [], '0.6.0');
});

add_action('woocommerce_before_shop_loop_item_title', function (): void {
    global $product;

    if (! $product instanceof WC_Product) {
        return;
    }

    $terms = get_the_terms($product->get_id(), 'product_cat');
    if (empty($terms) || is_wp_error($terms)) {
        return;
    }

    echo '<span class="product-card__cat">' . esc_html($terms[0]->name) . '</span>';

    $first_purchase_badge = (string) get_post_meta($product->get_id(), '_zvij_first_purchase_badge', true);
    if ($first_purchase_badge !== '') {
        echo '<span class="product-card__badge">' . esc_html($first_purchase_badge) . '</span>';
    }
}, 9);

add_action('woocommerce_after_shop_loop_item', function (): void {
    global $product;

    if (! $product instanceof WC_Product) {
        return;
    }

    $dobroimetje_note = (string) get_post_meta($product->get_id(), '_zvij_dobroimetje_note', true);
    if ($dobroimetje_note !== '') {
        echo '<p class="product-card__credit">' . esc_html($dobroimetje_note) . '</p>';
    }
}, 7);

add_action('woocommerce_single_product_summary', function (): void {
    global $product;

    if (! $product instanceof WC_Product) {
        return;
    }

    $terms = get_the_terms($product->get_id(), 'product_cat');
    if (empty($terms) || is_wp_error($terms)) {
        return;
    }

    echo '<div class="single-product__chips">';
    foreach (array_slice($terms, 0, 3) as $term) {
        echo '<span>' . esc_html($term->name) . '</span>';
    }
    echo '</div>';
}, 4);

add_action('woocommerce_after_single_product_summary', function (): void {
    global $product;

    if (! $product instanceof WC_Product) {
        return;
    }

    $source_url = (string) get_post_meta($product->get_id(), 'imported_from_live_url', true);
    $needs_review = (string) get_post_meta($product->get_id(), 'legal_copy_review_needed', true);
    $youtube_url = (string) get_post_meta($product->get_id(), '_zvij_dubi_youtube_url', true);
    $packaging_note = (string) get_post_meta($product->get_id(), '_zvij_packaging_note', true);
    $dobroimetje_note = (string) get_post_meta($product->get_id(), '_zvij_dobroimetje_note', true);
    $is_dubi = str_contains(strtolower($product->get_name()), 'dubi');
    ?>
    <?php if ($dobroimetje_note !== '') : ?>
      <aside class="zvij-credit-note">
        <?php echo esc_html($dobroimetje_note); ?>
      </aside>
    <?php endif; ?>
    <?php if ($packaging_note !== '') : ?>
      <aside class="zvij-packaging-note">
        <?php echo esc_html($packaging_note); ?>
      </aside>
    <?php endif; ?>
    <section class="zvij-product-context">
      <div>
        <p class="card-kicker"><?php esc_html_e('Zakaj ta izdelek', 'zvij-theme'); ?></p>
        <h2><?php echo $is_dubi ? esc_html__('Urejen filter za vsakdanji setup.', 'zvij-theme') : esc_html__('Izbrani vršički, jasna mera.', 'zvij-theme'); ?></h2>
        <p><?php echo $is_dubi ? esc_html__('DUBI izdelki pokrijejo osnovo: dovolj zaloge, jasen namen in enostaven refill ritem.', 'zvij-theme') : esc_html__('SMOKEY, CHILLY in FRUTTY so postavljeni kot premium vršički z jasno količino, brez THC učinka in možnostjo čajne uporabe.', 'zvij-theme'); ?></p>
      </div>
      <div>
        <p class="card-kicker"><?php esc_html_e('Za koga je', 'zvij-theme'); ?></p>
        <h2><?php esc_html_e('Za ljudi, ki želijo manj improvizacije.', 'zvij-theme'); ?></h2>
        <p><?php esc_html_e('Stran mora kupcu hitro povedati, kaj izdelek je, kako se vključi v ritual in kdaj ga je smiselno ponovno naročiti.', 'zvij-theme'); ?></p>
      </div>
      <div>
        <p class="card-kicker"><?php esc_html_e('Kako se vključi v setup', 'zvij-theme'); ?></p>
        <h2><?php esc_html_e('Setup, refill, ponovi.', 'zvij-theme'); ?></h2>
        <p><?php esc_html_e('To je osnova za prihodnji sistem članstva, Zvij kode, dobroimetja in ponovitve zadnjega naročila.', 'zvij-theme'); ?></p>
      </div>
    </section>
    <?php if ($youtube_url !== '') : ?>
      <section class="zvij-product-video-panel">
        <div>
          <p class="card-kicker"><?php esc_html_e('Video predstavitev filtrov', 'zvij-theme'); ?></p>
          <h2><?php esc_html_e('DUBI v praksi', 'zvij-theme'); ?></h2>
          <p><?php esc_html_e('Video je prenesen kot referenca iz stare strani in ostaja del dev prototipa, dokler Jaka ne potrdi končne video vsebine.', 'zvij-theme'); ?></p>
        </div>
        <div class="zvij-video-frame">
          <iframe title="<?php esc_attr_e('DUBI filtri video predstavitev', 'zvij-theme'); ?>" src="https://www.youtube.com/embed/5oNlpY17v9w" loading="lazy" allowfullscreen></iframe>
        </div>
      </section>
    <?php endif; ?>
    <?php if ($source_url !== '' || $needs_review === 'true') : ?>
      <aside class="zvij-dev-note">
        <?php if ($needs_review === 'true') : ?>
          <strong><?php esc_html_e('DEV review:', 'zvij-theme'); ?></strong>
          <?php esc_html_e('opis izdelka potrebuje pravni/copy pregled pred objavo.', 'zvij-theme'); ?>
        <?php endif; ?>
        <?php if ($source_url !== '') : ?>
          <span><?php esc_html_e('Vir:', 'zvij-theme'); ?> <?php echo esc_html($source_url); ?></span>
        <?php endif; ?>
      </aside>
    <?php endif; ?>
    <?php
}, 20);
