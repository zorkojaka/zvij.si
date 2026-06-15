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
    wp_enqueue_style('zvij-theme-style', get_stylesheet_uri(), [], '0.8.0');

    if (is_front_page() || is_page('zvij-kit')) {
        wp_enqueue_script(
            'zvij-kits',
            get_template_directory_uri() . '/assets/kits.js',
            [],
            '0.8.0',
            true
        );
    }
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
function zvij_kit_chip_markup(string $label, array $view, bool $selected): string {
    $classes = 'kit-chip' . ($selected ? ' is--selected' : '') . ($view['available'] ? '' : ' kit-chip--soon');
    $sku = $view['sku'] !== '' ? $view['sku'] : (string) $view['id'];

    $out = '<button type="button" class="' . esc_attr($classes) . '"'
        . ' data-kit-product'
        . ' data-default="' . ($selected ? '1' : '0') . '"'
        . ' data-label="' . esc_attr($label) . '"'
        . ' data-product-id="' . esc_attr((string) $view['id']) . '"'
        . ' data-sku="' . esc_attr($sku) . '"'
        . ' data-price="' . esc_attr((string) $view['price']) . '"'
        . ' aria-pressed="' . ($selected ? 'true' : 'false') . '">';
    $out .= zvij_kit_media_markup($view, $label);
    $out .= '<span class="kit-chip__label">' . esc_html($label) . '</span>';
    if (! $view['available']) {
        $out .= '<span class="kit-chip__soon">' . esc_html__('kmalu', 'zvij-theme') . '</span>';
    }
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
          // Core products are selected by default; optional add-ons are opt-in.
          $default_count = count($core_items);
      ?>
        <article class="kit-row kit-row--<?php echo esc_attr($kit_key); ?>" data-kit="<?php echo esc_attr($kit['slug'] ?? ''); ?>">
          <header class="kit-row__head">
            <span class="card-kicker"><?php echo esc_html($kit['name'] ?? ''); ?></span>
            <h3><?php echo esc_html($kit['tagline'] ?? ''); ?></h3>
            <?php if (! empty($kit['position'])) : ?>
              <p><?php echo esc_html($kit['position']); ?></p>
            <?php endif; ?>
          </header>

          <div class="kit-row__body">
            <div class="kit-row__main">
              <div class="kit-visual kit-visual--<?php echo esc_attr($kit_key); ?>" role="img" aria-label="<?php echo esc_attr(sprintf(__('%s — sestavljena slika kita', 'zvij-theme'), (string) ($kit['name'] ?? ''))); ?>">
                <span class="kit-visual__tag"><?php echo esc_html($kit['name'] ?? ''); ?></span>
                <span class="kit-visual__note"><?php esc_html_e('Sestavljena slika kita — kmalu', 'zvij-theme'); ?></span>
              </div>

              <p class="kit-products__hint"><?php esc_html_e('Izdelki na sliki. Klikni, da kateri ni vključen.', 'zvij-theme'); ?></p>
              <div class="kit-products" role="group" aria-label="<?php esc_attr_e('Izdelki v kitu', 'zvij-theme'); ?>">
                <?php foreach ($core_items as $item) :
                    $label = (string) ($item['label'] ?? '');
                    $view = zvij_kit_item_view((string) ($item['slug'] ?? ''));
                    echo zvij_kit_chip_markup($label, $view, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                endforeach; ?>
              </div>

              <?php if ($addon_views !== []) : ?>
                <p class="kit-products__hint kit-products__hint--addons"><?php esc_html_e('Opcijski vršički — klikni, da jih dodaš', 'zvij-theme'); ?></p>
                <div class="kit-products kit-products--addons" role="group" aria-label="<?php esc_attr_e('Opcijski vršički', 'zvij-theme'); ?>">
                  <?php foreach ($addon_views as $addon) :
                      echo zvij_kit_chip_markup($addon['title'], $addon, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                  endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <aside class="kit-order-panel">
              <div class="kit-order-panel__inner">
                <p class="kit-order__count"><span data-kit-count><?php echo (int) $default_count; ?></span> <?php esc_html_e('izbranih izdelkov', 'zvij-theme'); ?></p>
                <p class="kit-order__total"><?php esc_html_e('Cena se izračuna iz izbranih izdelkov.', 'zvij-theme'); ?></p>
                <button type="button" class="button kit-order__cta" data-kit-order><?php esc_html_e('Naroči kit', 'zvij-theme'); ?></button>
                <button type="button" class="text-link kit-order__reset" data-kit-reset><?php esc_html_e('Ponastavi kit', 'zvij-theme'); ?></button>
                <p class="kit-order__status" data-kit-status role="status" hidden></p>
              </div>
            </aside>
          </div>
        </article>
      <?php endforeach; ?>

      <p class="kit-showcase__note"><?php esc_html_e('Komponente kitov so v dev fazi označene kot »kmalu«, dokler niso potrjeni dobavitelj, cene in fotografije. Naročanje kita je placeholder, dokler checkout ni pripravljen.', 'zvij-theme'); ?></p>
    </section>
    <?php
}
