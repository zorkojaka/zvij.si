<?php
if (! class_exists('WooCommerce')) {
    fwrite(STDERR, "WooCommerce is not active.\n");
    exit(1);
}

update_option('woocommerce_currency', 'EUR');
update_option('woocommerce_currency_pos', 'right_space');
update_option('woocommerce_price_thousand_sep', '.');
update_option('woocommerce_price_decimal_sep', ',');
update_option('woocommerce_price_num_decimals', '2');

function zvij_catalog_find_product(string $slug, array $legacy_slugs = [], array $legacy_source_urls = []): int {
    $ids = get_posts([
        'post_type' => 'product',
        'post_status' => ['publish', 'draft', 'private', 'pending'],
        'name' => $slug,
        'fields' => 'ids',
        'posts_per_page' => 1,
    ]);
    if ($ids) {
        return (int) $ids[0];
    }

    foreach ($legacy_slugs as $legacy_slug) {
        $ids = get_posts([
            'post_type' => 'product',
            'post_status' => ['publish', 'draft', 'private', 'pending'],
            'name' => $legacy_slug,
            'fields' => 'ids',
            'posts_per_page' => 1,
        ]);
        if ($ids) {
            return (int) $ids[0];
        }
    }

    foreach ($legacy_source_urls as $source_url) {
        $ids = get_posts([
            'post_type' => 'product',
            'post_status' => ['publish', 'draft', 'private', 'pending'],
            'fields' => 'ids',
            'posts_per_page' => 1,
            'meta_key' => 'imported_from_live_url',
            'meta_value' => $source_url,
        ]);
        if ($ids) {
            return (int) $ids[0];
        }
    }

    return 0;
}

function zvij_catalog_term_id(string $name): int {
    $term = term_exists($name, 'product_cat');
    if (! $term) {
        $term = wp_insert_term($name, 'product_cat');
    }
    if (is_wp_error($term)) {
        throw new RuntimeException($term->get_error_message());
    }

    return (int) $term['term_id'];
}

function zvij_catalog_featured_image_by_source(string $source_url): int {
    if ($source_url === '') {
        return 0;
    }

    $ids = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'fields' => 'ids',
        'posts_per_page' => 1,
        'meta_key' => '_zvij_source_image_url',
        'meta_value' => $source_url,
    ]);

    return $ids ? (int) $ids[0] : 0;
}

function zvij_catalog_product(array $data): int {
    $product_id = zvij_catalog_find_product(
        $data['slug'],
        $data['legacy_slugs'] ?? [],
        $data['legacy_source_urls'] ?? []
    );

    $post_data = [
        'post_type' => 'product',
        'post_status' => $data['status'],
        'post_title' => $data['title'],
        'post_name' => $data['slug'],
        'post_excerpt' => $data['excerpt'],
        'post_content' => $data['content'],
    ];

    if ($product_id > 0) {
        $post_data['ID'] = $product_id;
        wp_update_post($post_data);
    } else {
        $product_id = wp_insert_post($post_data, true);
        if (is_wp_error($product_id)) {
            throw new RuntimeException($product_id->get_error_message());
        }
    }

    $category_id = zvij_catalog_term_id($data['category']);
    wp_set_object_terms($product_id, [$category_id], 'product_cat', false);

    $product = wc_get_product($product_id);
    if (! $product) {
        $product = new WC_Product_Simple($product_id);
    }
    $product->set_regular_price($data['price']);
    $product->set_sale_price('');
    $product->set_price($data['price']);
    $product->set_catalog_visibility('visible');
    $product->set_stock_status('instock');
    $product->set_manage_stock(false);
    $product->save();

    if (! empty($data['image_source_url'])) {
        $attachment_id = zvij_catalog_featured_image_by_source($data['image_source_url']);
        if ($attachment_id > 0) {
            set_post_thumbnail($product_id, $attachment_id);
        }
    }

    foreach (($data['meta'] ?? []) as $key => $value) {
        update_post_meta($product_id, $key, $value);
    }
    update_post_meta($product_id, 'catalog_sync_status', 'synced_' . gmdate('Y-m-d'));
    update_post_meta($product_id, 'legal_copy_review_needed', $data['legal_review'] ?? 'false');

    return (int) $product_id;
}

$tea_intro = 'Predstavljen kot čaj za urejen ritual, jasno mero in izbiro brez THC učinka.';
$five_gram_note = '5 g paket vsebuje 5 posameznih 1 g pakiranj.';

$catalog = [
    [
        'title' => 'SMOKEY CBD čaj 1 g',
        'slug' => 'smokey-cbd-caj-1-g',
        'legacy_slugs' => ['smokey-premium-cbd'],
        'legacy_source_urls' => ['https://zvij.si/izdelek/smokey-premium-cbd/'],
        'price' => '7.20',
        'category' => 'CBD čaj',
        'status' => 'publish',
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/06/smokey-frontside.png',
        'excerpt' => '<p>SMOKEY CBD čaj v 1 g pakiranju. ' . $tea_intro . '</p>',
        'content' => '<p>SMOKEY je preimenovan iz starega izdelka SMOKEY Premium CBD v bolj jasen dev katalog: CBD čaj 1 g.</p><p>Uporaba copyja ostaja pri ritualu, meri in urejeni izbiri. Brez zdravstvenih obljub in brez HHC ponudbe.</p>',
        'meta' => [
            '_zvij_former_name' => 'SMOKEY Premium CBD',
            '_zvij_public_mapping' => 'CBD NM = SMOKEY',
            '_zvij_packaging_logic' => '1 x 1 g package',
        ],
    ],
    [
        'title' => 'SMOKEY CBD čaj 5 g',
        'slug' => 'smokey-cbd-caj-5-g',
        'price' => '32.90',
        'category' => 'CBD čaj',
        'status' => 'publish',
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/06/smokey-frontside.png',
        'excerpt' => '<p>SMOKEY CBD čaj v 5 g paketu. ' . $five_gram_note . '</p>',
        'content' => '<p>Večji SMOKEY paket za jasen refill ritem in urejeno zalogo.</p><p><strong>' . $five_gram_note . '</strong></p>',
        'meta' => [
            '_zvij_packaging_logic' => '5 x 1 g packages',
            '_zvij_packaging_note' => $five_gram_note,
        ],
    ],
    [
        'title' => 'CHILLY CBG čaj 1 g',
        'slug' => 'chilly-cbg-caj-1-g',
        'legacy_slugs' => ['chilly-premium-cbg'],
        'legacy_source_urls' => ['https://zvij.si/izdelek/chilly-premium-cbg/'],
        'price' => '6.50',
        'category' => 'CBD čaj',
        'status' => 'publish',
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/06/chilly-frontside.png',
        'excerpt' => '<p>CHILLY CBG čaj v 1 g pakiranju. ' . $tea_intro . '</p>',
        'content' => '<p>CHILLY je preimenovan iz starega izdelka CHILLY Premium CBG v bolj jasen dev katalog: CBG čaj 1 g.</p><p>Copy ostaja pri ritualu, jasni meri in urejeni izbiri.</p>',
        'meta' => [
            '_zvij_former_name' => 'CHILLY Premium CBG',
            '_zvij_public_mapping' => 'CBG NM = CHILLY',
            '_zvij_packaging_logic' => '1 x 1 g package',
        ],
    ],
    [
        'title' => 'CHILLY CBG čaj 5 g',
        'slug' => 'chilly-cbg-caj-5-g',
        'price' => '29.90',
        'category' => 'CBD čaj',
        'status' => 'publish',
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/06/chilly-frontside.png',
        'excerpt' => '<p>CHILLY CBG čaj v 5 g paketu. ' . $five_gram_note . '</p>',
        'content' => '<p>Večji CHILLY paket za jasen refill ritem in urejeno zalogo.</p><p><strong>' . $five_gram_note . '</strong></p>',
        'meta' => [
            '_zvij_packaging_logic' => '5 x 1 g packages',
            '_zvij_packaging_note' => $five_gram_note,
        ],
    ],
    [
        'title' => 'FRUTTY CBD čaj 1 g',
        'slug' => 'frutty-cbd-caj-1-g',
        'legacy_slugs' => ['frutty-cbd'],
        'legacy_source_urls' => ['https://zvij.si/izdelek/frutty-cbd/'],
        'price' => '4.20',
        'category' => 'CBD čaj',
        'status' => 'publish',
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/06/frutty-frontside.png',
        'excerpt' => '<p>FRUTTY CBD čaj v 1 g pakiranju. ' . $tea_intro . '</p>',
        'content' => '<p>FRUTTY je preimenovan iz starega izdelka FRUTTY CBD oziroma Bubble Gum v bolj jasen dev katalog: CBD čaj 1 g.</p><p>Copy ostaja pri ritualu, meri in urejeni izbiri.</p>',
        'meta' => [
            '_zvij_former_name' => 'FRUTTY CBD / Bubble Gum',
            '_zvij_public_mapping' => 'Bubble Gum = FRUTTY',
            '_zvij_packaging_logic' => '1 x 1 g package',
        ],
    ],
    [
        'title' => 'FRUTTY CBD čaj 5 g',
        'slug' => 'frutty-cbd-caj-5-g',
        'price' => '19.90',
        'category' => 'CBD čaj',
        'status' => 'publish',
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/06/frutty-frontside.png',
        'excerpt' => '<p>FRUTTY CBD čaj v 5 g paketu. ' . $five_gram_note . '</p>',
        'content' => '<p>Večji FRUTTY paket za jasen refill ritem in urejeno zalogo.</p><p><strong>' . $five_gram_note . '</strong></p>',
        'meta' => [
            '_zvij_packaging_logic' => '5 x 1 g packages',
            '_zvij_packaging_note' => $five_gram_note,
        ],
    ],
    [
        'title' => 'DUBI 42 aktivnih ogljikovih filtrov',
        'slug' => 'dubi-42-aktivnih-ogljikovih-filtrov',
        'legacy_slugs' => ['dubi-aktivni-ogljikovi-filtri-42-kosov'],
        'legacy_source_urls' => ['https://zvij.si/izdelek/dubi-aktivni-ogljikovi-filtri-42-kosov/'],
        'price' => '6.50',
        'category' => 'DUBI filtri',
        'status' => 'publish',
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/10/dubi-front.png',
        'excerpt' => '<p>DUBI aktivni ogljikovi filtri v vrečki 42 kosov.</p>',
        'content' => '<p>Osnovno DUBI pakiranje za urejen setup in jasno zalogo.</p><p>42 filtrov na vrečko.</p>',
        'meta' => [
            '_zvij_packaging_logic' => '42 filters per bag',
            '_zvij_dubi_youtube_url' => 'https://www.youtube.com/watch?v=5oNlpY17v9w',
        ],
    ],
    [
        'title' => 'DUBI 420 aktivnih ogljikovih filtrov',
        'slug' => 'dubi-420-aktivnih-ogljikovih-filtrov',
        'legacy_source_urls' => ['https://zvij.si/izdelek/dubi-420-aktivnih-ogljikovih-filtrov/'],
        'price' => '67.50',
        'category' => 'DUBI filtri',
        'status' => 'publish',
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/10/dubi-front.png',
        'excerpt' => '<p>DUBI aktivni ogljikovi filtri v večjem paketu 420 kosov.</p>',
        'content' => '<p>Večji DUBI paket za refill ritem in manj ponovnega naročanja.</p><p>420 filtrov na vrečko.</p>',
        'meta' => [
            '_zvij_packaging_logic' => '420 filters per bag',
            '_zvij_dubi_youtube_url' => 'https://www.youtube.com/watch?v=5oNlpY17v9w',
        ],
    ],
    [
        'title' => 'Sample paket',
        'slug' => 'sample-paket',
        'price' => '4.20',
        'category' => 'Zvij setup',
        'status' => 'publish',
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/10/dubi-front.png',
        'excerpt' => '<p>DEV placeholder za sample ponudbo. Vsebina paketa še ni potrjena.</p>',
        'content' => '<p>Sample paket je objavljen samo na dev kot placeholder, da lahko preverimo tok trgovine in kartice izdelkov.</p><p>Končna vsebina mora biti potrjena pred produkcijo.</p>',
        'meta' => [
            '_zvij_dev_placeholder' => 'true',
            '_zvij_packaging_logic' => 'contents not confirmed',
        ],
    ],
    [
        'title' => 'Zvij setup paket',
        'slug' => 'zvij-setup-paket',
        'legacy_slugs' => ['dev-placeholder-zvij-setup-paket'],
        'price' => '19.90',
        'category' => 'Zvij setup',
        'status' => 'publish',
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/10/dubi-front.png',
        'excerpt' => '<p>DEV bundle: DUBI 42 + rolca + sample CBD/CBG čaja.</p>',
        'content' => '<p>Zvij setup paket je dev bundle za preverjanje prvega nakupa.</p><p>Predvidena vsebina: DUBI 42, rolca in sample CBD/CBG čaja. Končna vsebina mora biti potrjena.</p>',
        'meta' => [
            '_zvij_dev_bundle' => 'true',
            '_zvij_packaging_logic' => 'DUBI 42 + rolca + sample CBD/CBG tea',
        ],
    ],
];

$synced = [];
foreach ($catalog as $product_data) {
    $product_id = zvij_catalog_product($product_data);
    $synced[] = $product_data['title'] . ' #' . $product_id . ' ' . $product_data['price'] . ' EUR';
}

foreach (['smokey-cbd-caj-10-g', 'chilly-cbg-caj-10-g', 'frutty-cbd-caj-10-g'] as $planned_slug) {
    $product_id = zvij_catalog_find_product($planned_slug);
    if ($product_id > 0) {
        wp_update_post(['ID' => $product_id, 'post_status' => 'draft']);
        update_post_meta($product_id, 'catalog_sync_status', 'planned_not_active_' . gmdate('Y-m-d'));
    }
}

echo "Synced catalog products:\n- " . implode("\n- ", $synced) . "\n";
