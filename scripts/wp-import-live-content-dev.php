<?php
if (! class_exists('WooCommerce')) {
    fwrite(STDERR, "WooCommerce is not active.\n");
    exit(1);
}

$source_base = 'https://zvij.si';

$price_map = [
    'dubi-420-aktivnih-ogljikovih-filtrov' => ['regular' => '75.00', 'sale' => '67.50', 'status' => 'publish', 'category' => 'DUBI filtri', 'legal_review' => 'false'],
    'dubi-aktivni-ogljikovi-filtri-42-kosov' => ['regular' => '7.75', 'sale' => '6.50', 'status' => 'publish', 'category' => 'DUBI filtri', 'legal_review' => 'false'],
    'chilly-premium-cbg' => ['regular' => '7.50', 'sale' => '6.50', 'status' => 'draft', 'category' => 'CBD čaj', 'legal_review' => 'true'],
    'smokey-premium-cbd' => ['regular' => '8.00', 'sale' => '7.20', 'status' => 'draft', 'category' => 'CBD čaj', 'legal_review' => 'true'],
    'frutty-cbd' => ['regular' => '5.00', 'sale' => '4.20', 'status' => 'draft', 'category' => 'CBD čaj', 'legal_review' => 'true'],
];

function zvij_live_get_json(string $url): array {
    $response = wp_remote_get($url, ['timeout' => 30]);
    if (is_wp_error($response)) {
        throw new RuntimeException($response->get_error_message());
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        throw new RuntimeException("HTTP $code for $url");
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (! is_array($data)) {
        throw new RuntimeException("Invalid JSON from $url");
    }

    return $data;
}

function zvij_clean_title(string $title): string {
    return html_entity_decode(wp_strip_all_tags($title), ENT_QUOTES, 'UTF-8');
}

function zvij_find_post_by_meta(string $post_type, string $meta_key, string $meta_value): int {
    $ids = get_posts([
        'post_type' => $post_type,
        'post_status' => ['publish', 'draft', 'private', 'pending'],
        'fields' => 'ids',
        'posts_per_page' => 1,
        'meta_key' => $meta_key,
        'meta_value' => $meta_value,
    ]);

    return $ids ? (int) $ids[0] : 0;
}

function zvij_import_featured_image(int $post_id, string $image_url): int {
    if ($image_url === '') {
        return 0;
    }

    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'fields' => 'ids',
        'posts_per_page' => 1,
        'meta_key' => '_zvij_source_image_url',
        'meta_value' => $image_url,
    ]);
    if ($existing) {
        set_post_thumbnail($post_id, (int) $existing[0]);
        return (int) $existing[0];
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = download_url($image_url, 30);
    if (is_wp_error($tmp)) {
        update_post_meta($post_id, 'image_import_error', $tmp->get_error_message());
        return 0;
    }

    $file = [
        'name' => basename((string) parse_url($image_url, PHP_URL_PATH)),
        'tmp_name' => $tmp,
    ];

    $attachment_id = media_handle_sideload($file, $post_id);
    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        update_post_meta($post_id, 'image_import_error', $attachment_id->get_error_message());
        return 0;
    }

    update_post_meta((int) $attachment_id, '_zvij_source_image_url', $image_url);
    set_post_thumbnail($post_id, (int) $attachment_id);
    delete_post_meta($post_id, 'image_import_error');

    return (int) $attachment_id;
}

function zvij_safe_dubi_excerpt(string $title): string {
    $count = str_contains($title, '420') ? '420' : '42';

    return '<p>DUBI aktivni ogljikovi filtri v pakiranju ' . esc_html($count) . ' kosov. Namenjeni so urejenemu setupu, stabilni pretočnosti in čistejšemu občutku pri ritualu.</p>'
        . '<p>Filtri so izdelani iz nebeljenega kartona, keramičnih čepkov in aktivnega ogljika iz kokosovih lupin.</p>';
}

$products = zvij_live_get_json($source_base . '/wp-json/wp/v2/product?per_page=20&_embed');
$imported_products = [];

foreach ($products as $source_product) {
    $slug = (string) ($source_product['slug'] ?? '');
    if (! isset($price_map[$slug])) {
        continue;
    }

    $source_url = (string) ($source_product['link'] ?? '');
    $title = zvij_clean_title((string) ($source_product['title']['rendered'] ?? $slug));
    $settings = $price_map[$slug];
    $image_url = (string) ($source_product['_embedded']['wp:featuredmedia'][0]['source_url'] ?? '');
    $content = (string) ($source_product['content']['rendered'] ?? '');
    $excerpt = (string) ($source_product['excerpt']['rendered'] ?? '');

    if (str_starts_with($slug, 'dubi-')) {
        $excerpt = zvij_safe_dubi_excerpt($title);
    }

    $product_id = zvij_find_post_by_meta('product', 'imported_from_live_url', $source_url);
    if ($product_id === 0) {
        $product_id = wp_insert_post([
            'post_type' => 'product',
            'post_status' => $settings['status'],
            'post_title' => $title,
            'post_name' => $slug,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
        ], true);

        if (is_wp_error($product_id)) {
            throw new RuntimeException($product_id->get_error_message());
        }
    } else {
        wp_update_post([
            'ID' => $product_id,
            'post_status' => $settings['status'],
            'post_title' => $title,
            'post_name' => $slug,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
        ]);
    }

    $term = term_exists($settings['category'], 'product_cat');
    if (! $term) {
        $term = wp_insert_term($settings['category'], 'product_cat');
    }
    if (! is_wp_error($term)) {
        wp_set_object_terms($product_id, [(int) $term['term_id']], 'product_cat', false);
    }

    $product = wc_get_product($product_id);
    if (! $product) {
        $product = new WC_Product_Simple($product_id);
    }
    $product->set_regular_price($settings['regular']);
    $product->set_sale_price($settings['sale']);
    $product->set_price($settings['sale'] !== '' ? $settings['sale'] : $settings['regular']);
    $product->set_catalog_visibility('visible');
    $product->set_stock_status('instock');
    $product->save();

    if ($image_url !== '') {
        zvij_import_featured_image($product_id, $image_url);
    }

    update_post_meta($product_id, 'imported_from_live_url', $source_url);
    update_post_meta($product_id, 'import_status', 'imported_from_live_' . gmdate('Y-m-d'));
    update_post_meta($product_id, 'legal_copy_review_needed', $settings['legal_review']);
    update_post_meta($product_id, '_zvij_source_image_url', $image_url);
    if (str_starts_with($slug, 'dubi-')) {
        update_post_meta($product_id, '_zvij_dubi_youtube_url', 'https://www.youtube.com/watch?v=5oNlpY17v9w');
    }

    $imported_products[] = $title . ' (' . $settings['status'] . ')';
}

$posts = zvij_live_get_json($source_base . '/wp-json/wp/v2/posts?per_page=20&_embed');
$imported_posts = [];

foreach ($posts as $source_post) {
    $slug = (string) ($source_post['slug'] ?? '');
    $source_url = (string) ($source_post['link'] ?? '');
    $title = zvij_clean_title((string) ($source_post['title']['rendered'] ?? $slug));
    $content = (string) ($source_post['content']['rendered'] ?? '');
    $excerpt = (string) ($source_post['excerpt']['rendered'] ?? '');
    $image_url = (string) ($source_post['_embedded']['wp:featuredmedia'][0]['source_url'] ?? '');
    $date = (string) ($source_post['date'] ?? current_time('mysql'));

    $post_id = zvij_find_post_by_meta('post', 'imported_from_live_url', $source_url);
    $post_data = [
        'post_type' => 'post',
        'post_status' => 'draft',
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => '<p><strong>DEV opomba:</strong> Zgodovinski uvoz iz stare strani. Vsebina mora skozi pravni in copy review pred objavo.</p>' . $content,
        'post_excerpt' => $excerpt,
        'post_date' => gmdate('Y-m-d H:i:s', strtotime($date)),
        'post_date_gmt' => gmdate('Y-m-d H:i:s', strtotime($date)),
    ];

    if ($post_id === 0) {
        $post_id = wp_insert_post($post_data, true);
        if (is_wp_error($post_id)) {
            throw new RuntimeException($post_id->get_error_message());
        }
    } else {
        $post_data['ID'] = $post_id;
        wp_update_post($post_data);
    }

    if ($image_url !== '') {
        zvij_import_featured_image($post_id, $image_url);
    }

    update_post_meta($post_id, 'imported_from_live_url', $source_url);
    update_post_meta($post_id, 'import_status', 'draft_needs_copy_review_' . gmdate('Y-m-d'));
    update_post_meta($post_id, 'legal_copy_review_needed', 'true');
    update_post_meta($post_id, '_zvij_source_image_url', $image_url);

    $imported_posts[] = $title . ' (draft)';
}

echo "Imported products:\n- " . implode("\n- ", $imported_products) . "\n";
echo "Imported posts:\n- " . implode("\n- ", $imported_posts) . "\n";
