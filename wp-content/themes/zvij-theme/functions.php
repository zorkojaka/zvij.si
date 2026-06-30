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
    wp_enqueue_style('zvij-theme-style', get_stylesheet_uri(), [], '0.9.8');

    if (is_page('zvij-kit')) {
        wp_enqueue_script('zvij-kits', get_template_directory_uri() . '/assets/kits.js', [], '0.9.0', true);
    }

    if (is_front_page()) {
        if (function_exists('WC')) {
            wp_enqueue_script('wc-add-to-cart');
        }
        wp_enqueue_script('zvij-home', get_template_directory_uri() . '/assets/home.js', ['jquery'], '0.9.8', true);
    }
});

add_action('woocommerce_product_options_general_product_data', function (): void {
    woocommerce_wp_text_input([
        'id' => '_zvij_homepage_carousel_order',
        'label' => __('Homepage carousel order', 'zvij-theme'),
        'desc_tip' => true,
        'description' => __('Lower numbers appear first in the homepage carousel. Fallback is product menu order, then publish date.', 'zvij-theme'),
        'type' => 'number',
        'custom_attributes' => [
            'step' => '1',
            'min' => '0',
        ],
    ]);
});

add_action('woocommerce_admin_process_product_object', function (WC_Product $product): void {
    if (! isset($_POST['_zvij_homepage_carousel_order'])) {
        return;
    }

    $raw = wc_clean(wp_unslash($_POST['_zvij_homepage_carousel_order']));
    if ($raw === '') {
        $product->delete_meta_data('_zvij_homepage_carousel_order');
        return;
    }

    $product->update_meta_data('_zvij_homepage_carousel_order', (string) max(0, (int) $raw));
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
        <p><?php echo $is_dubi ? esc_html__('DUBI izdelki pokrijejo osnovo: dovolj zaloge, jasen namen in enostaven reload ritem.', 'zvij-theme') : esc_html__('SMOKEY, CHILLY in FRUTTY so postavljeni kot premium vršički z jasno količino, brez THC učinka in možnostjo čajne uporabe.', 'zvij-theme'); ?></p>
      </div>
      <div>
        <p class="card-kicker"><?php esc_html_e('Za koga je', 'zvij-theme'); ?></p>
        <h2><?php esc_html_e('Za ljudi, ki želijo manj improvizacije.', 'zvij-theme'); ?></h2>
        <p><?php esc_html_e('Stran mora kupcu hitro povedati, kaj izdelek je, kako se vključi v ritual in kdaj ga je smiselno ponovno naročiti.', 'zvij-theme'); ?></p>
      </div>
      <div>
        <p class="card-kicker"><?php esc_html_e('Kako se vključi v setup', 'zvij-theme'); ?></p>
        <h2><?php esc_html_e('Setup, reload, ponovi.', 'zvij-theme'); ?></h2>
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
        <div class="zvij-video-media">
          <div class="zvij-video-frame zvij-video-frame--premium">
            <iframe title="<?php esc_attr_e('DUBI filtri video predstavitev', 'zvij-theme'); ?>" src="https://www.youtube.com/embed/5oNlpY17v9w" loading="lazy" allowfullscreen></iframe>
          </div>
          <p class="zvij-video-caption"><?php esc_html_e('DUBI 42 in DUBI 420 — packshot in setup kontekst. Brez zdravstvenih trditev.', 'zvij-theme'); ?></p>
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

/**
 * Resolve a kit item (product slug) into a small view model for the showcase.
 *
 * @return array{title:string,url:string,image:string,available:bool,price:float,sku:string,id:int}
 */
function zvij_kit_item_view(string $slug): array {
    $view = ['title' => $slug, 'url' => '', 'image' => '', 'available' => false, 'price' => 0.0, 'sku' => '', 'id' => 0];

    $page = get_page_by_path($slug, OBJECT, 'product');
    if (! $page) {
        $page = get_page_by_path($slug, OBJECT, 'product_variation');
    }
    if (! $page) {
        return $view;
    }

    $product = function_exists('wc_get_product') ? wc_get_product($page->ID) : null;
    if (! $product instanceof WC_Product) {
        return $view;
    }

    $view['id'] = (int) $product->get_id();
    $view['title'] = $product->get_name();
    $view['sku'] = (string) $product->get_sku();
    $view['price'] = (float) $product->get_price();
    $view['available'] = $product->get_status() === 'publish';
    if ($view['available']) {
        $view['url'] = (string) get_permalink($product->get_id());
    }

    $image_id = $product->get_image_id();
    if ($image_id) {
        $view['image'] = (string) wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail');
    }

    return $view;
}

/**
 * Render one selectable kit product chip.
 *
 * @param array{title:string,url:string,image:string,available:bool,price:float,sku:string,id:int} $view
 */
function zvij_kit_chip_markup(string $label, array $view, bool $selected, bool $addon = false): string {
    $classes = 'kit-chip' . ($selected ? ' is--selected' : '') . ($view['available'] ? '' : ' kit-chip--soon') . ($addon ? ' kit-chip--addon' : '');
    $sku = $view['sku'] !== '' ? $view['sku'] : (string) $view['id'];

    $out = '<button type="button" class="' . esc_attr($classes) . '"'
        . ' data-kit-product'
        . ' data-default="' . ($selected ? '1' : '0') . '"'
        . ' data-label="' . esc_attr($label) . '"'
        . ' data-product-id="' . esc_attr((string) $view['id']) . '"'
        . ' data-sku="' . esc_attr($sku) . '"'
        . ' data-price="' . esc_attr((string) $view['price']) . '"'
        . ' aria-pressed="' . ($selected ? 'true' : 'false') . '">';
    $out .= '<span class="kit-chip__label">' . esc_html($label) . '</span>';
    $out .= '<span class="kit-chip__state" aria-hidden="true"></span>';
    $out .= '</button>';

    return $out;
}

/**
 * Build the "media" cell for a kit item: product image or a styled initial.
 */
function zvij_kit_media_markup(array $view, string $label): string {
    if ($view['image'] !== '') {
        return '<span class="kit-item__media"><img src="' . esc_url($view['image']) . '" alt="" loading="lazy" /></span>';
    }

    $initial = function_exists('mb_substr') ? mb_substr($label, 0, 1) : substr($label, 0, 1);

    return '<span class="kit-item__media kit-item__media--placeholder"><span>' . esc_html(strtoupper($initial)) . '</span></span>';
}

/**
 * Render the Zvij.si Kit showcase from the `zvij_kits` option.
 * One full-width horizontal row per kit: wide visual on top, selectable
 * product chips below, order panel on the right. Showcase only — selection is
 * client-side, no real bundle/cart logic yet.
 */
function zvij_render_kit_showcase(): void {
    $kits = get_option('zvij_kits', []);
    if (! is_array($kits) || $kits === []) {
        return;
    }

    $kit_images = [
        'black' => 'black-kit-flatlay.png',
        'silver' => 'silver-kit-flatlay.png',
        'gold' => 'gold-kit-flatlay.png',
        'throwie' => 'throwie-kit-flatlay.png',
    ];
    ?>
    <section class="section-block kit-showcase" id="kiti" aria-labelledby="kit-showcase-title">
      <div class="section-heading">
        <p class="eyebrow"><?php esc_html_e('Zvij.si Kit', 'zvij-theme'); ?></p>
        <h2 id="kit-showcase-title"><?php esc_html_e('Vse, kar rabiš, da si zviješ.', 'zvij-theme'); ?></h2>
        <p class="kit-showcase__lead"><?php esc_html_e('Tu je paket. Odstrani, kar nočeš. Black, Silver in Gold so stil, ne cenovni razred. Throwie je utility kit za s sabo. DUBI filtri so v vsakem kitu, vršički so opcijski dodatek.', 'zvij-theme'); ?></p>
      </div>

      <?php foreach ($kits as $kit) :
          $kit_key = sanitize_html_class($kit['key'] ?? 'kit');

          $core_items = (array) ($kit['items'] ?? []);
          $addon_views = array_filter(
              array_map('zvij_kit_item_view', (array) ($kit['addons'] ?? [])),
              static fn ($v) => $v['available'] || $v['id'] > 0
          );
          $headline = (string) ($kit['headline'] ?? $kit['tagline'] ?? '');
          $subline = (string) ($kit['subline'] ?? $kit['position'] ?? '');
          $default_count = count($core_items);
          $image_file = $kit_images[$kit_key] ?? '';
          $image_path = $image_file !== '' ? get_template_directory() . '/assets/images/kits/' . $image_file : '';
          $image_url = ($image_path !== '' && file_exists($image_path))
              ? get_template_directory_uri() . '/assets/images/kits/' . $image_file
              : '';
      ?>
        <article class="kit-row kit-row--<?php echo esc_attr($kit_key); ?>" data-kit="<?php echo esc_attr($kit['slug'] ?? ''); ?>">
          <div class="kit-visual kit-visual--<?php echo esc_attr($kit_key); ?><?php echo $image_url !== '' ? ' kit-visual--has-image' : ''; ?>" role="img" aria-label="<?php echo esc_attr(sprintf(__('%s — sestavljena slika kita', 'zvij-theme'), (string) ($kit['name'] ?? ''))); ?>">
            <?php if ($image_url !== '') : ?>
              <img src="<?php echo esc_url($image_url); ?>" alt="" loading="lazy" />
            <?php else : ?>
              <span class="kit-visual__shape kit-visual__shape--one"></span>
              <span class="kit-visual__shape kit-visual__shape--two"></span>
              <span class="kit-visual__shape kit-visual__shape--three"></span>
              <span class="kit-visual__note"><?php echo esc_html(sprintf(__('%s image coming soon', 'zvij-theme'), (string) ($kit['name'] ?? 'Kit'))); ?></span>
            <?php endif; ?>
          </div>

          <div class="kit-info">
            <div class="kit-copy">
              <span class="card-kicker"><?php echo esc_html($kit['name'] ?? ''); ?></span>
              <h3><?php echo esc_html($headline); ?></h3>
              <?php if ($subline !== '') : ?>
                <p><?php echo esc_html($subline); ?></p>
              <?php endif; ?>
              <p class="kit-order__count"><span data-kit-count><?php echo (int) $default_count; ?></span> <?php esc_html_e('izbranih izdelkov', 'zvij-theme'); ?></p>

              <div class="kit-products-row">
                <span class="kit-row-label"><?php esc_html_e('Vključuje:', 'zvij-theme'); ?></span>
                <div class="kit-products" role="group" aria-label="<?php esc_attr_e('Izdelki v kitu', 'zvij-theme'); ?>">
                  <?php foreach ($core_items as $item) :
                      $label = (string) ($item['label'] ?? '');
                      $view = zvij_kit_item_view((string) ($item['slug'] ?? ''));
                      echo zvij_kit_chip_markup($label, $view, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                  endforeach; ?>
                </div>
              </div>

              <?php if ($addon_views !== []) : ?>
                <div class="kit-addons-row">
                  <span class="kit-row-label"><?php esc_html_e('Dodaj:', 'zvij-theme'); ?></span>
                  <div class="kit-products kit-products--addons" role="group" aria-label="<?php esc_attr_e('Opcijski vršički', 'zvij-theme'); ?>">
                    <?php foreach ($addon_views as $addon) :
                        $addon_label = preg_replace('/\s+vršički\s+/', ' ', $addon['title']);
                        $addon_label = preg_replace('/\s+(CBD|CBG)\s+/', ' ', (string) $addon_label);
                        $addon_label = preg_replace('/\s+1\s+g$/', ' 1g', (string) $addon_label);
                        echo zvij_kit_chip_markup((string) $addon_label, $addon, false, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <div class="kit-actions">
              <button type="button" class="button kit-order__cta" data-kit-order><?php esc_html_e('Naroči kit', 'zvij-theme'); ?></button>
              <button type="button" class="text-link kit-order__reset" data-kit-reset><?php esc_html_e('Ponastavi', 'zvij-theme'); ?></button>
              <p class="kit-order__status" data-kit-status role="status" hidden></p>
              <p class="kit-order__total"><?php esc_html_e('Cena se izračuna iz izbranih izdelkov.', 'zvij-theme'); ?></p>
            </div>
          </div>
        </article>
      <?php endforeach; ?>

      <p class="kit-showcase__note"><?php esc_html_e('Komponente kitov so v dev fazi označene kot »kmalu«, dokler niso potrjeni dobavitelj, cene in fotografije. Naročanje kita je placeholder, dokler checkout ni pripravljen.', 'zvij-theme'); ?></p>
    </section>
    <?php
}

/* ---------------------------------------------------------------------------
   Homepage (front page) helpers — variant A, izdelek-first
--------------------------------------------------------------------------- */

function zvij_home_product(string $slug): ?WC_Product {
    $page = get_page_by_path($slug, OBJECT, 'product');
    if (! $page) {
        return null;
    }
    $product = function_exists('wc_get_product') ? wc_get_product($page->ID) : null;
    return $product instanceof WC_Product ? $product : null;
}

function zvij_home_money($amount): string {
    if (! function_exists('wc_price')) {
        return number_format((float) $amount, 2, ',', '.') . ' €';
    }
    return trim(html_entity_decode(wp_strip_all_tags(wc_price((float) $amount))));
}

function zvij_home_product_img_url(?WC_Product $product): string {
    if (! $product instanceof WC_Product) {
        return '';
    }
    $image_id = $product->get_image_id();
    return $image_id ? (string) wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail') : '';
}

/**
 * URL of a temporary kit flat-lay image, only if the file actually exists.
 * key: black|silver|gold|throwie
 */
function zvij_kit_flatlay_url(string $key): string {
    $file = $key . '-kit-flatlay.png';
    $path = get_template_directory() . '/assets/images/kits/' . $file;
    return file_exists($path) ? get_template_directory_uri() . '/assets/images/kits/' . $file : '';
}

/**
 * Configurable homepage block image slot.
 * Drop a real photo at wp-content/themes/zvij-theme/assets/images/home/<name>.{jpg,jpeg,png,webp}
 * and it overrides the temporary fallback — no code change required.
 * Returns '' when no slot file exists (caller decides the temporary fallback).
 */
function zvij_home_block_img(string $name): string {
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $rel = '/assets/images/home/' . $name . '.' . $ext;
        if (file_exists(get_template_directory() . $rel)) {
            return get_template_directory_uri() . $rel;
        }
    }
    return '';
}

function zvij_homepage_carousel_sort_value(WC_Product $product): array {
    $custom = $product->get_meta('_zvij_homepage_carousel_order', true);
    $has_custom = $custom !== '' && is_numeric($custom);

    return [
        $has_custom ? 0 : 1,
        $has_custom ? (int) $custom : (int) get_post_field('menu_order', $product->get_id()),
        (string) get_post_field('post_date', $product->get_id()),
    ];
}

function zvij_homepage_carousel_product_visible(WC_Product $product): bool {
    if ($product->get_status() !== 'publish') {
        return false;
    }
    if ((string) $product->get_price() === '') {
        return false;
    }
    if (! $product->is_purchasable()) {
        return false;
    }
    if (! $product->is_in_stock() && ! $product->backorders_allowed()) {
        return false;
    }
    $private_markers = strtolower($product->get_name() . ' ' . $product->get_slug());
    foreach (['sample', 'internal', 'tbd'] as $marker) {
        if (str_contains($private_markers, $marker)) {
            return false;
        }
    }

    if ($product->is_type('variable') && zvij_homepage_carousel_variations($product) === []) {
        return false;
    }

    return true;
}

function zvij_homepage_carousel_products(): array {
    if (! function_exists('wc_get_products')) {
        return [];
    }

    $products = wc_get_products([
        'status' => 'publish',
        'limit' => 24,
        'tag' => ['homepage-carousel'],
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
    $products = array_values(array_filter($products, 'zvij_homepage_carousel_product_visible'));

    usort($products, static function (WC_Product $a, WC_Product $b): int {
        return zvij_homepage_carousel_sort_value($a) <=> zvij_homepage_carousel_sort_value($b);
    });

    return $products;
}

function zvij_homepage_carousel_label(WC_Product $product): string {
    $terms = get_the_terms($product->get_id(), 'product_cat');
    if (! empty($terms) && ! is_wp_error($terms)) {
        return (string) $terms[0]->name;
    }

    return $product->is_type('variable') ? __('Izberi količino', 'zvij-theme') : __('Izdelek', 'zvij-theme');
}

function zvij_homepage_carousel_variations(WC_Product $product): array {
    if (! $product->is_type('variable')) {
        return [];
    }

    $items = [];
    foreach ($product->get_children() as $variation_id) {
        $variation = wc_get_product($variation_id);
        if (! $variation instanceof WC_Product_Variation) {
            continue;
        }
        if (! zvij_homepage_carousel_product_visible($variation)) {
            continue;
        }

        $attrs = $variation->get_variation_attributes();
        $label = '';
        foreach ($attrs as $value) {
            if ($value !== '') {
                $label = (string) $value;
                break;
            }
        }

        $items[] = [
            'id' => $variation->get_id(),
            'label' => $label !== '' ? $label : $variation->get_name(),
            'price' => wp_strip_all_tags($variation->get_price_html()),
            'attrs' => $attrs,
        ];
    }

    return $items;
}

function zvij_homepage_carousel_add_button(WC_Product $product, int $position): string {
    if ($product->is_type('variable')) {
        return sprintf(
            '<button class="zv-carousel__add" type="button" data-variable-add data-product-id="%1$s" data-variation-id="" data-carousel-position="%2$s" data-carousel-source="homepage" disabled>%3$s</button>',
            esc_attr((string) $product->get_id()),
            esc_attr((string) $position),
            esc_html__('Dodaj v košarico', 'zvij-theme')
        );
    }

    return sprintf(
        '<a href="%1$s" data-quantity="1" data-product_id="%2$s" data-product-id="%2$s" data-variation-id="" data-carousel-position="%3$s" data-carousel-source="homepage" rel="nofollow" class="zv-carousel__add ajax_add_to_cart add_to_cart_button">%4$s</a>',
        esc_url($product->add_to_cart_url()),
        esc_attr((string) $product->get_id()),
        esc_attr((string) $position),
        esc_html__('Dodaj v košarico', 'zvij-theme')
    );
}

function zvij_render_homepage_product_carousel(): void {
    $products = zvij_homepage_carousel_products();

    if ($products === []) {
        if (current_user_can('manage_woocommerce')) {
            echo '<section class="zv-carousel-empty"><p>' . esc_html__('Homepage carousel is hidden because no published products have the homepage-carousel tag.', 'zvij-theme') . '</p></section>';
        }
        return;
    }
    ?>
    <section class="zv-carousel" data-zv-carousel aria-labelledby="zv-carousel-title">
      <div class="zv-carousel__header">
        <div>
          <p class="zv-carousel__label"><?php esc_html_e('IZBRANO ZA TVOJ SETUP', 'zvij-theme'); ?></p>
          <h2 id="zv-carousel-title"><?php esc_html_e('Poglej, kaj se trenutno zvija.', 'zvij-theme'); ?></h2>
          <p><?php esc_html_e('Filtri, vršički in stvari, ki sodijo zraven.', 'zvij-theme'); ?></p>
        </div>
        <div class="zv-carousel__controls">
          <button type="button" data-carousel-prev aria-label="<?php esc_attr_e('Prejšnji izdelki', 'zvij-theme'); ?>">‹</button>
          <button type="button" data-carousel-next aria-label="<?php esc_attr_e('Naslednji izdelki', 'zvij-theme'); ?>">›</button>
        </div>
      </div>
      <div class="zv-carousel__viewport" data-carousel-viewport tabindex="0">
        <div class="zv-carousel__track" data-carousel-track>
          <?php foreach ($products as $i => $product) : ?>
            <?php
            $position = $i + 1;
            $variations = zvij_homepage_carousel_variations($product);
            ?>
            <article class="zv-carousel-card" data-carousel-card data-product-id="<?php echo esc_attr((string) $product->get_id()); ?>" data-carousel-position="<?php echo esc_attr((string) $position); ?>" data-carousel-source="homepage">
              <a class="zv-carousel-card__image" href="<?php echo esc_url(get_permalink($product->get_id())); ?>" aria-label="<?php echo esc_attr($product->get_name()); ?>">
                <?php echo $product->get_image('woocommerce_thumbnail', ['loading' => $i === 0 ? 'eager' : 'lazy']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php if ($product->is_on_sale()) : ?><span class="zv-carousel-card__badge"><?php esc_html_e('Akcija', 'zvij-theme'); ?></span><?php endif; ?>
              </a>
              <div class="zv-carousel-card__body">
                <p class="zv-carousel-card__cat"><?php echo esc_html(zvij_homepage_carousel_label($product)); ?></p>
                <h3><a href="<?php echo esc_url(get_permalink($product->get_id())); ?>"><?php echo esc_html($product->get_name()); ?></a></h3>
                <p class="zv-carousel-card__price" data-price-out><?php echo wp_kses_post($product->get_price_html()); ?></p>
                <?php if ($product->is_type('variable')) : ?>
                  <div class="zv-carousel-card__hint"><?php esc_html_e('Izberi količino', 'zvij-theme'); ?></div>
                  <div class="zv-carousel-card__vars" role="group" aria-label="<?php esc_attr_e('Količina', 'zvij-theme'); ?>">
                    <?php foreach ($variations as $variation) : ?>
                      <button type="button"
                        data-variation-choice
                        data-variation-id="<?php echo esc_attr((string) $variation['id']); ?>"
                        data-product-id="<?php echo esc_attr((string) $product->get_id()); ?>"
                        data-price="<?php echo esc_attr($variation['price']); ?>"
                        data-attrs="<?php echo esc_attr(wp_json_encode($variation['attrs'])); ?>">
                        <?php echo esc_html($variation['label']); ?>
                      </button>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <?php echo zvij_homepage_carousel_add_button($product, $position); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <p class="zv-carousel-card__status" data-cart-status aria-live="polite"></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
}
