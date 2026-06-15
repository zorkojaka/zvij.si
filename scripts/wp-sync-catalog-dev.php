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

function zvij_catalog_term_id(string $name, string $slug = ''): int {
    $term = $slug !== '' ? term_exists($slug, 'product_cat') : term_exists($name, 'product_cat');
    if (! $term) {
        $term = wp_insert_term($name, 'product_cat', $slug !== '' ? ['slug' => $slug] : []);
    }
    if (is_wp_error($term)) {
        throw new RuntimeException($term->get_error_message());
    }

    return (int) $term['term_id'];
}

/**
 * Central registry of product tags used by the Zvij.si Kit system.
 *
 * @return array<string,string> slug => display name
 */
function zvij_kit_tag_registry(): array {
    return [
        'black-kit' => 'Black Kit',
        'silver-kit' => 'Silver Kit',
        'gold-kit' => 'Gold Kit',
        'throwie' => 'Throwie',
        'reload' => 'Reload',
        'kit-addon' => 'Kit Add-on',
        'kit-component' => 'Kit Component',
        'dubi' => 'DUBI',
        'vrsicki' => 'Vršički',
    ];
}

function zvij_catalog_tag_id(string $slug): int {
    $names = zvij_kit_tag_registry();
    $name = $names[$slug] ?? ucfirst(str_replace('-', ' ', $slug));

    $term = term_exists($slug, 'product_tag');
    if (! $term) {
        $term = wp_insert_term($name, 'product_tag', ['slug' => $slug]);
    }
    if (is_wp_error($term)) {
        throw new RuntimeException($term->get_error_message());
    }

    return (int) (is_array($term) ? $term['term_id'] : $term);
}

/**
 * Replace a product's kit tags with the given slugs.
 *
 * @param array<int,string> $tag_slugs
 */
function zvij_catalog_apply_tags(int $product_id, array $tag_slugs): void {
    if ($product_id <= 0) {
        return;
    }
    $tag_ids = array_map('zvij_catalog_tag_id', array_values(array_unique($tag_slugs)));
    wp_set_object_terms($product_id, $tag_ids, 'product_tag', false);
}

/**
 * Build a draft kit-component product with shared defaults.
 *
 * @param array<int,string>   $tags
 * @param array<string,string> $meta
 */
function zvij_kit_component(string $title, string $slug, string $category, string $category_slug, array $tags, string $excerpt, string $content, array $meta = [], string $sku = '', array $legacy_slugs = []): array {
    return [
        'title' => $title,
        'slug' => $slug,
        'sku' => $sku,
        'legacy_slugs' => $legacy_slugs,
        'price' => '',
        'regular_price' => '',
        'category' => $category,
        'category_slug' => $category_slug,
        'status' => 'draft',
        'tags' => array_values(array_unique(array_merge($tags, ['kit-component']))),
        'excerpt' => $excerpt,
        'content' => $content,
        'meta' => array_merge(['_zvij_dev_placeholder' => 'true', '_zvij_kit_component' => 'true'], $meta),
    ];
}

/**
 * Knistermann-sourced kit components (draft until supplier/prices/photos confirmed).
 * Purchase prices are wholesale and stored in meta only — never as a public price.
 */
function zvij_kit_components(): array {
    $kn = static fn (string $sku, string $purchase, string $name): array => [
        '_zvij_supplier' => 'Knistermann',
        '_zvij_supplier_sku' => $sku,
        '_zvij_supplier_name' => $name,
        '_zvij_b2b_price' => $purchase,
        '_zvij_image_status' => 'missing_image',
    ];

    return [
        // --- Vžigalniki ---
        zvij_kit_component('Cheap fajrji HEMP 2', 'cheap-fajrji-hemp-2', 'Vžigalniki', 'vzigalniki', ['throwie'],
            '<p>Draft komponenta: poceni FARO HEMP 2 fajrji za Throwie Kit.</p>',
            '<p>Ko zagusti: poceni, uporabni fajrji za kit ali paketni add-on. Test kvalitete je obvezen; če hitro crknejo, odpadejo. Potencialno 3-4 vžigalniki za 4,20 €.</p>',
            $kn('11651', '9.95 EUR per 50 display', 'FARO Elektronikfeuerzeuge, 50er Display, "HEMP 2"'),
            '11651',
            ['faro-cheap-lighters']),
        zvij_kit_component('Clipper Gold', 'clipper-gold', 'Vžigalniki', 'vzigalniki', ['gold-kit'],
            '<p>Draft komponenta: Clipper METALL GOLD za Gold Kit.</p>',
            '<p>Gold Kit kandidat. Gold je stil, ne cenovni razred. Ne dodajati poceni nalepke direktno na premium/kakovosten vžigalnik.</p>',
            $kn('7656', 'from 50.29 EUR per 12 display', 'Clipper Feuerzeuge groß, 12er Display, METALL GOLD'),
            '7656'),
        zvij_kit_component('Clipper Black', 'clipper-black', 'Vžigalniki', 'vzigalniki', ['black-kit'],
            '<p>Draft komponenta: Clipper METAL MATT ALL BLACK za Black Kit.</p>',
            '<p>Kandidat za Black Kit. Potrebna vizualna potrditev, da deluje dovolj čisto in diskretno.</p>',
            $kn('11208', '', 'Clipper METAL MATT ALL BLACK'),
            '11208',
            ['clipper-black-matt']),
        zvij_kit_component('Clipper Black Gradient', 'clipper-black-gradient', 'Vžigalniki', 'vzigalniki', ['black-kit'],
            '<p>Draft komponenta: Clipper BLACK GRADIENT kot alternativa za Black Kit.</p>',
            '<p>Alternativa, če all-black ne izgleda dovolj dobro ali ni dobavljiv.</p>',
            $kn('11125', '', 'Clipper BLACK GRADIENT'),
            '11125'),

        // --- Grinderji ---
        zvij_kit_component('Champ High Black Grinder 60 mm', 'champ-high-black-grinder-60-mm', 'Grinderji', 'grinderji', ['black-kit'],
            '<p>Draft komponenta: Champ High črn kovinski grinder za Black Kit.</p>',
            '<p>Metall Grinder/Polinator "Champ High", 4-part, Ø 60mm. Dobra marža, če kakovost drži. Retail kandidat: 16,90 ali 19,90 €.</p>',
            array_merge($kn('11528', '4.46-4.95 EUR', 'Metall Grinder/Polinator "Champ High", 4-part, Ø 60mm'), ['_zvij_retail_candidate' => '16.90 or 19.90 EUR']),
            '11528',
            ['grinder-champ-black']),
        zvij_kit_component('Zvij.si Mini Grinder 5 cm', 'zvij-mini-grinder-5-cm', 'Grinderji', 'grinderji', ['throwie', 'black-kit'],
            '<p>Draft komponenta: naročen mini grinder z logotipom Zvij.si.</p>',
            '<p>Naročenih 10 kosov z logotipom. Treba je pregledati kakovost logotipa ob prejemu. Če izgleda cheap/promocijsko, ne gre v hero vlogo. Retail kandidat: 9,90 ali 12,90 €.</p>',
            ['_zvij_supplier' => 'Other / China / Temu order', '_zvij_ordered_qty' => '10', '_zvij_b2b_price' => 'approx 2.25-3.00 EUR unit, based on corrected 60 EUR total order split', '_zvij_retail_candidate' => '9.90 or 12.90 EUR', '_zvij_image_status' => 'missing_image'],
            'ZVIJ-MINI-GRINDER-5CM',
            ['grinder-cheap']),
        zvij_kit_component('Gold grinder', 'gold-grinder-placeholder', 'Grinderji', 'grinderji', ['gold-kit'],
            '<p>Draft komponenta: zlat grinder za Gold Kit.</p>',
            '<p>TBD placeholder. Potrebujemo ujemajoč gold grinder. Ne uporabiti slabega modela samo zato, da zapolni kit.</p>',
            ['_zvij_supplier' => 'TBD', '_zvij_image_status' => 'missing_image'],
            'TBD-GOLD-GRINDER',
            ['grinder-gold']),
        zvij_kit_component('Silver grinder', 'silver-grinder-placeholder', 'Grinderji', 'grinderji', ['silver-kit'],
            '<p>Draft komponenta: srebrn grinder za Silver Kit.</p>',
            '<p>TBD placeholder. Potreben samo, če Silver Kit ostane v prvem valu.</p>',
            ['_zvij_supplier' => 'TBD', '_zvij_image_status' => 'missing_image'],
            'TBD-SILVER-GRINDER',
            ['grinder-silver']),

        // --- Rizle in rolce ---
        zvij_kit_component('IRIE XTRA Light King Size Slim', 'rizla-irie-xtra-light', 'Rizle', 'rizle', ['throwie'],
            '<p>Draft komponenta: cheap/420 vibe rizle za Throwie Kit.</p>',
            '<p>420/value kandidat. Bolj Throwie kot Black/Silver/Gold.</p>',
            $kn('10474', '', 'IRIE XTRA Light King Size Slim'),
            '10474'),
        zvij_kit_component('JaJa Noir Black', 'jaja-noir-black', 'Rizle', 'rizle', ['black-kit'],
            '<p>Draft komponenta: črne XXL rizle za Black Kit (quiet, ne cheap).</p>',
            '<p>Ni cheap/value. Kandidat za Black/Quiet setup.</p>',
            $kn('11150', '', 'JaJa Noir Black King Size XXL'),
            '11150',
            ['rizla-jaja-noir']),
        zvij_kit_component('Smoking Black Rolls', 'smoking-black-rolls', 'Rolce', 'rolce', ['black-kit'],
            '<p>Draft komponenta: črne rolce za Black Kit.</p>',
            '<p>Core Black/Quiet rolca. Ne mešati z RAW dodatki.</p>',
            $kn('2623', 'from 14.42 EUR, if confirmed', 'Smoking DeLuxe Slim Rolls Black'),
            '2623',
            ['rolca-smoking-deluxe-black']),
        zvij_kit_component('Smoking Brown Rolls', 'smoking-brown-rolls', 'Rolce', 'rolce', ['black-kit', 'silver-kit'],
            '<p>Draft komponenta: nebeljene rjave rolce za normal/quiet setup.</p>',
            '<p>Natural/quiet alternativa. Primerjati s Smoking Black.</p>',
            $kn('1521', '', 'Smoking Brown Rolls'),
            '1521',
            ['rolca-smoking-brown']),
        zvij_kit_component('Smoking Silver Rolls', 'smoking-silver-rolls', 'Rolce', 'rolce', ['silver-kit'],
            '<p>Draft komponenta: srebrne rolce za Silver Kit.</p>',
            '<p>Silver Kit obstaja samo, če najdemo matching Clipper/grinder/tube.</p>',
            $kn('1094', '', 'Smoking Silver Rolls'),
            '1094',
            ['rolca-smoking-master-silver']),
        zvij_kit_component('SmK Gold Rolls', 'smk-gold-rolls', 'Rolce', 'rolce', ['gold-kit'],
            '<p>Draft komponenta: zlate rolce za Gold Kit.</p>',
            '<p>Gold smer. Preveriti količino v paketu in občutek kvalitete.</p>',
            $kn('10515', '', 'SmK Gold Rolls'),
            '10515',
            ['rolca-smk-gold']),
        zvij_kit_component('SmK Gold Papers + Filter Tips', 'smk-gold-papers-filter-tips', 'Rizle', 'rizle', ['gold-kit'],
            '<p>Draft komponenta: SmK Gold papirčki s filter tips za Gold Kit.</p>',
            '<p>Gold Kit paper/filtertips alternativa. Ne mešati z RAW tube.</p>',
            $kn('10513', '', 'SmK Gold papers + filtertips'),
            '10513',
            ['rizla-smk-gold-papers']),

        // --- Embalaža in opcijski dodatki ---
        zvij_kit_component('Black Metal Joint Tube', 'black-metal-joint-tube', 'Setup dodatki', 'setup-dodatki', ['black-kit', 'kit-addon'],
            '<p>Draft dodatek: črn kovinski tulec za Black Kit.</p>',
            '<p>Optional add-on, ne core. Naročenih 5 črnih kovinskih tulcev. Finalno fotografirati skupaj z matching kitom.</p>',
            ['_zvij_supplier' => 'Other / China / Temu order', '_zvij_ordered_qty' => '5', '_zvij_b2b_price' => 'approx 2.00-2.50 EUR unit', '_zvij_retail_candidate' => '6.90 or 7.90 EUR', '_zvij_image_status' => 'missing_image'],
            'ZVIJ-TUBE-BLACK'),
        zvij_kit_component('Silver Metal Joint Tube', 'silver-metal-joint-tube', 'Setup dodatki', 'setup-dodatki', ['silver-kit', 'kit-addon'],
            '<p>Draft dodatek: srebrn kovinski tulec za Silver Kit.</p>',
            '<p>Optional add-on. Naročenih 5 srebrnih kovinskih tulcev. Finalno fotografirati skupaj z matching kitom.</p>',
            ['_zvij_supplier' => 'Other / China / Temu order', '_zvij_ordered_qty' => '5', '_zvij_b2b_price' => 'approx 2.00-2.50 EUR unit', '_zvij_retail_candidate' => '6.90 or 7.90 EUR', '_zvij_image_status' => 'missing_image'],
            'ZVIJ-TUBE-SILVER'),
        zvij_kit_component('Gold Metal Joint Tube', 'gold-metal-joint-tube', 'Setup dodatki', 'setup-dodatki', ['gold-kit', 'kit-addon'],
            '<p>Draft dodatek: zlat kovinski tulec za Gold Kit.</p>',
            '<p>Optional add-on. Naročenih 5 zlatih kovinskih tulcev. Finalno fotografirati skupaj z matching kitom.</p>',
            ['_zvij_supplier' => 'Other / China / Temu order', '_zvij_ordered_qty' => '5', '_zvij_b2b_price' => 'approx 2.00-2.50 EUR unit', '_zvij_retail_candidate' => '6.90 or 7.90 EUR', '_zvij_image_status' => 'missing_image'],
            'ZVIJ-TUBE-GOLD'),
        zvij_kit_component('Throwie Bag / setup pouch', 'throwie-bag', 'Embalaža', 'embalaza', ['black-kit', 'silver-kit', 'gold-kit', 'throwie'],
            '<p>Draft komponenta: vrečka z vrvico, ki gre v vsak kit.</p>',
            '<p>Fizična osnova kita: setup pouch / vrečka z vrvico / vse na enem mestu. Ne komunicirati kot »get rid of evidence«.</p>',
            ['_zvij_supplier' => 'TBD', '_zvij_image_status' => 'missing_image'],
            'TBD-THROWIE-BAG'),
        zvij_kit_component('Silver Clipper', 'clipper-silver', 'Vžigalniki', 'vzigalniki', ['silver-kit'],
            '<p>Draft komponenta: srebrn Clipper za Silver Kit.</p>',
            '<p>TBD placeholder. Potreben je matching silver lighter, če Silver Kit ostane v prvem valu.</p>',
            ['_zvij_supplier' => 'TBD', '_zvij_image_status' => 'missing_image'],
            'TBD-SILVER-CLIPPER'),
        zvij_kit_component('Matching rolling tray / podlaga', 'matching-rolling-tray-placeholder', 'Setup dodatki', 'setup-dodatki', ['kit-addon'],
            '<p>TBD/later placeholder: matching rolling tray ali podlaga.</p>',
            '<p>Samo če je vizualno čisto in poceni. Trenutni Knistermann trays so zavrnjeni kot preveč random/brand-heavy/dragi.</p>',
            ['_zvij_supplier' => 'TBD / Later', '_zvij_image_status' => 'missing_image'],
            'TBD-ROLLING-TRAY'),
    ];
}

/**
 * Showcase definition for the Zvij.si Kit system, consumed by the theme via the
 * `zvij_kits` option. Items reference products by slug; the theme resolves them.
 */
function zvij_kit_definitions(): array {
    $addons = ['frutty-cbd-vrsicki-1-g', 'smokey-cbd-vrsicki-1-g', 'chilly-cbg-vrsicki-1-g'];

    return [
        [
            'key' => 'black', 'name' => 'Black Kit', 'slug' => 'black-kit', 'tag' => 'black-kit',
            'tagline' => 'Quiet. Diskretno. Nekričeče.',
            'position' => 'Črn, diskreten setup, ki ne izstopa. Stil, ne cenovni razred.',
            'items' => [
                ['slug' => 'black-metal-joint-tube', 'label' => 'Black Metal Joint Tube'],
                ['slug' => 'clipper-black', 'label' => 'Clipper Black'],
                ['slug' => 'champ-high-black-grinder-60-mm', 'label' => 'Champ High Black Grinder'],
                ['slug' => 'smoking-black-rolls', 'label' => 'Smoking Black Rolls'],
                ['slug' => 'dubi-42-aktivnih-ogljikovih-filtrov', 'label' => 'DUBI 42'],
            ],
            'addons' => $addons,
        ],
        [
            'key' => 'silver', 'name' => 'Silver Kit', 'slug' => 'silver-kit', 'tag' => 'silver-kit',
            'tagline' => 'Čisto. Nevtralno. Varno darilo.',
            'position' => 'Čist, nevtralen setup — varna izbira za darilo. Stil, ne cenovni razred.',
            'items' => [
                ['slug' => 'silver-metal-joint-tube', 'label' => 'Silver Metal Joint Tube'],
                ['slug' => 'clipper-silver', 'label' => 'Silver Clipper'],
                ['slug' => 'silver-grinder-placeholder', 'label' => 'Silver grinder'],
                ['slug' => 'smoking-silver-rolls', 'label' => 'Smoking Silver Rolls'],
                ['slug' => 'dubi-42-aktivnih-ogljikovih-filtrov', 'label' => 'DUBI 42'],
            ],
            'addons' => $addons,
        ],
        [
            'key' => 'gold', 'name' => 'Gold Kit', 'slug' => 'gold-kit', 'tag' => 'gold-kit',
            'tagline' => 'Toplo. Darilno. Special.',
            'position' => 'Topel, darilni »special« setup. Stil, ne cenovni razred.',
            'items' => [
                ['slug' => 'gold-metal-joint-tube', 'label' => 'Gold Metal Joint Tube'],
                ['slug' => 'clipper-gold', 'label' => 'Gold Clipper'],
                ['slug' => 'gold-grinder-placeholder', 'label' => 'Gold grinder'],
                ['slug' => 'smk-gold-rolls', 'label' => 'SmK Gold Rolls'],
                ['slug' => 'dubi-42-aktivnih-ogljikovih-filtrov', 'label' => 'DUBI 42'],
            ],
            'addons' => $addons,
        ],
        [
            'key' => 'throwie', 'name' => 'Throwie Kit', 'slug' => 'throwie', 'tag' => 'throwie',
            'tagline' => 'Ko zagusti. Poceni, uporabno, za v torbo.',
            'position' => 'Setup pouch / utility — poceni in uporaben kit za s sabo.',
            'items' => [
                ['slug' => 'throwie-bag', 'label' => 'Throwie Bag'],
                ['slug' => 'cheap-fajrji-hemp-2', 'label' => 'Cheap fajrji HEMP 2'],
                ['slug' => 'zvij-mini-grinder-5-cm', 'label' => 'Zvij.si Mini Grinder'],
                ['slug' => 'rizla-irie-xtra-light', 'label' => 'IRIE XTRA Light'],
                ['slug' => 'dubi-42-aktivnih-ogljikovih-filtrov', 'label' => 'DUBI 42'],
            ],
            'addons' => $addons,
        ],
    ];
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

    $category_id = zvij_catalog_term_id($data['category'], $data['category_slug'] ?? '');
    wp_set_object_terms($product_id, [$category_id], 'product_cat', false);

    if (isset($data['tags'])) {
        zvij_catalog_apply_tags($product_id, (array) $data['tags']);
    }

    $product = wc_get_product($product_id);
    if (! $product) {
        $product = new WC_Product_Simple($product_id);
    }
    if (! empty($data['sku'])) {
        try {
            $product->set_sku((string) $data['sku']);
        } catch (WC_Data_Exception $e) {
            update_post_meta($product_id, '_zvij_sku_sync_error', $e->getMessage());
        }
    }
    $product->set_regular_price($data['regular_price'] ?? $data['price']);
    $product->set_sale_price($data['sale_price'] ?? '');
    $product->set_price(($data['sale_price'] ?? '') !== '' ? $data['sale_price'] : ($data['regular_price'] ?? $data['price']));
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

function zvij_catalog_page(array $data): int {
    $page = null;
    foreach (array_merge([$data['slug']], $data['legacy_slugs'] ?? []) as $slug) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if ($page) {
            break;
        }
    }

    $post_data = [
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => $data['title'],
        'post_name' => $data['slug'],
        'post_excerpt' => $data['excerpt'],
        'post_content' => $data['content'],
    ];

    if ($page) {
        $post_data['ID'] = $page->ID;
        return (int) wp_update_post($post_data);
    }

    return (int) wp_insert_post($post_data);
}

function zvij_catalog_sync_pages_and_menu(): void {
    $pages = [
        'domov' => [
            'title' => 'Domov',
            'slug' => 'domov',
            'excerpt' => 'Tvoj ritual. Tvoja mera. Tvoj setup.',
            'content' => 'Urejena trgovina za filtre, vršičke, setup pakete in reload.',
        ],
        'trgovina' => [
            'title' => 'Trgovina',
            'slug' => 'trgovina',
            'excerpt' => 'DUBI filtri, CBD/CBG vršički, setupi, setup dodatki in reload.',
            'content' => '<p>Dev trgovina za trenutni Zvij.si katalog: DUBI filtri, SMOKEY CBD vršički, CHILLY CBG vršički, FRUTTY CBD vršički, sample paket, Zvij setup paket in prihodnji Setup dodatki.</p><p>Setup dodatki so za zdaj draft izdelki, dokler Jaka ne potrdi dobavitelja, cen in fotografij.</p>',
        ],
        'clan-zvij-si' => [
            'title' => 'Član Zvij.si',
            'slug' => 'clan-zvij-si',
            'excerpt' => 'Zvijače za zvijače.',
            'content' => '<p><strong>Zvijače za zvijače.</strong></p><p>Zvij koda, dobroimetje in reload sistem za tiste, ki želijo svoj setup urejen tudi naslednjič.</p><p>Prvi nakup naj bo lahek preizkus. Naslednja naročila dobijo dobroimetje, ki pomaga graditi bolj urejen reload ritem.</p><p>Zvij koda je osebna oznaka člana. Dobroimetje je namenjeno naslednjemu nakupu v trgovini, ne izplačilu.</p><p>To ni MLM, ni preprodaja in ni lovljenje akcij. Gre za notranji member sistem: manj iskanja, lažja ponovitev naročila, jasnejši setup.</p>',
        ],
        'dubi-filtri' => [
            'title' => 'DUBI filtri',
            'slug' => 'dubi-filtri',
            'excerpt' => 'DUBI 42 in DUBI 420 za bolj urejen setup.',
            'content' => '<p>DUBI 42 je osnovna vrečka za prvi setup ali manjšo zalogo. DUBI 420 je večji reload paket za ljudi, ki nočejo stalno preverjati, koliko filtrov je še ostalo.</p><p>Logika je preprosta: manj improvizacije, jasna količina in bolj urejen ritem naročanja.</p><h2>Video predstavitev filtrov</h2><p><iframe title="DUBI filtri video predstavitev" src="https://www.youtube.com/embed/5oNlpY17v9w" width="560" height="315" frameborder="0" allowfullscreen="allowfullscreen"></iframe></p>',
        ],
        'cbd-vrsicki' => [
            'title' => 'CBD vršički',
            'slug' => 'cbd-vrsicki',
            'legacy_slugs' => ['cbd-caj'],
            'excerpt' => 'SMOKEY CBD, CHILLY CBG in FRUTTY CBD v 1 g in 5 g izbiri.',
            'content' => '<p>Zvij.si dev katalog trenutno uporablja tri linije izbranih vršičkov: SMOKEY CBD, CHILLY CBG in FRUTTY CBD.</p><p>Osnovno pakiranje je 1 g. 5 g paket vsebuje 5 posameznih 1 g pakiranj, zato ni potrebno novo pakiranje za večjo količino.</p><p>Vršički so predstavljeni z legalno previdno komunikacijo: jasna mera, ritual, brez THC učinka in čajna uporaba kot ena izmed možnosti.</p>',
        ],
        'zvij-setup' => [
            'title' => 'Zvij setup',
            'slug' => 'zvij-setup',
            'excerpt' => 'Starter setup: filtri, vršički in reload razmišljanje.',
            'content' => '<p>Zvij setup poveže prvi nakup z bolj jasnim ritmom: DUBI 42, rolca, sample vršičkov in pot na naslednji reload.</p><p>Cilj ni kaos v košarici, ampak preprost začetek: izbereš osnovo, vidiš mero, naslednjič se vrneš z dobroimetjem.</p>',
        ],
        'kontakt' => [
            'title' => 'Kontakt',
            'slug' => 'kontakt',
            'excerpt' => 'Vprašanja o naročilu, izdelkih ali članstvu.',
            'content' => '<p>Piši nam glede naročila, izdelkov, članstva ali dev kataloga.</p><p>Najbolj koristno je, da v sporočilu poveš, kateri izdelek ali naročilo imaš v mislih. Odgovor naj bo direkten, brez težkega korporativnega tona.</p>',
        ],
    ];

    $page_ids = [];
    foreach ($pages as $key => $page_data) {
        $page_ids[$key] = zvij_catalog_page($page_data);
    }

    update_option('show_on_front', 'page');
    update_option('page_on_front', $page_ids['domov']);
    if (class_exists('WooCommerce')) {
        update_option('woocommerce_shop_page_id', $page_ids['trgovina']);
    }

    $menu_name = 'Glavni meni';
    $menu = wp_get_nav_menu_object($menu_name);
    $menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu($menu_name);
    foreach ((array) wp_get_nav_menu_items($menu_id) as $item) {
        wp_delete_post($item->ID, true);
    }

    foreach (['domov', 'trgovina', 'clan-zvij-si', 'dubi-filtri', 'cbd-vrsicki', 'zvij-setup', 'kontakt'] as $key) {
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object-id' => $page_ids[$key],
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ]);
    }

    $locations = get_theme_mod('nav_menu_locations', []);
    $locations['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}

$old_cbd_term = get_term_by('slug', 'cbd-caj', 'product_cat') ?: get_term_by('name', 'CBD čaj', 'product_cat');
if ($old_cbd_term) {
    wp_update_term((int) $old_cbd_term->term_id, 'product_cat', [
        'name' => 'CBD/CBG vršički',
        'slug' => 'cbd-cbg-vrsicki',
    ]);
}

$old_refill_term = get_term_by('slug', 'refill', 'product_cat') ?: get_term_by('name', 'Refill', 'product_cat');
if ($old_refill_term) {
    wp_update_term((int) $old_refill_term->term_id, 'product_cat', [
        'name' => 'Reload',
        'slug' => 'reload',
    ]);
}

$old_refill_placeholder = get_page_by_title('DEV placeholder: Refill paket', OBJECT, 'product');
if ($old_refill_placeholder) {
    wp_update_post([
        'ID' => $old_refill_placeholder->ID,
        'post_title' => 'DEV placeholder: Reload paket',
        'post_name' => 'dev-placeholder-reload-paket',
        'post_excerpt' => 'Reload paket',
        'post_content' => 'Placeholder za ponovitev zaloge. Realni intervali in vsebina še niso potrjeni.',
    ]);
    wp_set_object_terms((int) $old_refill_placeholder->ID, 'Reload', 'product_cat', false);
}

zvij_catalog_sync_pages_and_menu();

$vrsicki_intro = 'Izbrani vršički za urejen ritual, jasno mero in izbiro brez THC učinka. Primerno za čajno uporabo.';
$five_gram_note = '5 g paket vsebuje 5 posameznih 1 g pakiranj.';
zvij_catalog_term_id('Setup dodatki', 'setup-dodatki');

$catalog = [
    [
        'title' => 'SMOKEY CBD vršički 1 g',
        'slug' => 'smokey-cbd-vrsicki-1-g',
        'sku' => 'SMOKEY-CBD-1G',
        'legacy_slugs' => ['smokey-premium-cbd', 'smokey-cbd-caj-1-g'],
        'legacy_source_urls' => ['https://zvij.si/izdelek/smokey-premium-cbd/'],
        'price' => '8.00',
        'regular_price' => '8.00',
        'category' => 'CBD/CBG vršički',
        'category_slug' => 'cbd-cbg-vrsicki',
        'status' => 'publish',
        'tags' => ['vrsicki', 'reload', 'kit-addon', 'black-kit', 'silver-kit', 'gold-kit'],
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/06/smokey-frontside.png',
        'excerpt' => '<p>SMOKEY CBD vršički v 1 g pakiranju. ' . $vrsicki_intro . '</p>',
        'content' => '<p>SMOKEY je linija CBD vršičkov v 1 g pakiranju.</p><p>Pakirano v 1 g enotah. Copy ostaja pri ritualu, meri in urejeni izbiri.</p>',
        'meta' => [
            '_zvij_former_name' => 'SMOKEY Premium CBD',
            '_zvij_public_mapping' => 'CBD NM = SMOKEY',
            '_zvij_packaging_logic' => '1 x 1 g package',
            '_zvij_dobroimetje_note' => 'Član prejme 0,80 € za naslednji reload.',
        ],
    ],
    [
        'title' => 'SMOKEY CBD vršički 5 g',
        'slug' => 'smokey-cbd-vrsicki-5-g',
        'sku' => 'SMOKEY-CBD-5X1G',
        'legacy_slugs' => ['smokey-cbd-caj-5-g'],
        'price' => '39.90',
        'regular_price' => '39.90',
        'category' => 'CBD/CBG vršički',
        'category_slug' => 'cbd-cbg-vrsicki',
        'status' => 'publish',
        'tags' => ['vrsicki', 'reload'],
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/06/smokey-frontside.png',
        'excerpt' => '<p>SMOKEY CBD vršički v 5 g paketu. ' . $five_gram_note . '</p>',
        'content' => '<p>Večji SMOKEY paket za jasen reload ritem in urejeno zalogo.</p><p><strong>' . $five_gram_note . '</strong></p><p>Primerno za čajno uporabo.</p>',
        'meta' => [
            '_zvij_packaging_logic' => '5 x 1 g packages',
            '_zvij_packaging_note' => $five_gram_note,
            '_zvij_dobroimetje_note' => 'Član prejme 4,00 € za naslednji reload.',
        ],
    ],
    [
        'title' => 'CHILLY CBG vršički 1 g',
        'slug' => 'chilly-cbg-vrsicki-1-g',
        'sku' => 'CHILLY-CBG-1G',
        'legacy_slugs' => ['chilly-premium-cbg', 'chilly-cbg-caj-1-g'],
        'legacy_source_urls' => ['https://zvij.si/izdelek/chilly-premium-cbg/'],
        'price' => '7.50',
        'regular_price' => '7.50',
        'category' => 'CBD/CBG vršički',
        'category_slug' => 'cbd-cbg-vrsicki',
        'status' => 'publish',
        'tags' => ['vrsicki', 'reload', 'kit-addon', 'black-kit', 'silver-kit', 'gold-kit'],
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/06/chilly-frontside.png',
        'excerpt' => '<p>CHILLY CBG vršički v 1 g pakiranju. ' . $vrsicki_intro . '</p>',
        'content' => '<p>CHILLY je linija CBG vršičkov v 1 g pakiranju.</p><p>Pakirano v 1 g enotah. Copy ostaja pri ritualu, jasni meri in urejeni izbiri.</p>',
        'meta' => [
            '_zvij_former_name' => 'CHILLY Premium CBG',
            '_zvij_public_mapping' => 'CBG NM = CHILLY',
            '_zvij_packaging_logic' => '1 x 1 g package',
            '_zvij_dobroimetje_note' => 'Član prejme 1,00 € za naslednji reload.',
        ],
    ],
    [
        'title' => 'CHILLY CBG vršički 5 g',
        'slug' => 'chilly-cbg-vrsicki-5-g',
        'sku' => 'CHILLY-CBG-5X1G',
        'legacy_slugs' => ['chilly-cbg-caj-5-g'],
        'price' => '36.90',
        'regular_price' => '36.90',
        'category' => 'CBD/CBG vršički',
        'category_slug' => 'cbd-cbg-vrsicki',
        'status' => 'publish',
        'tags' => ['vrsicki', 'reload'],
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/06/chilly-frontside.png',
        'excerpt' => '<p>CHILLY CBG vršički v 5 g paketu. ' . $five_gram_note . '</p>',
        'content' => '<p>Večji CHILLY paket za jasen reload ritem in urejeno zalogo.</p><p><strong>' . $five_gram_note . '</strong></p><p>Primerno za čajno uporabo.</p>',
        'meta' => [
            '_zvij_packaging_logic' => '5 x 1 g packages',
            '_zvij_packaging_note' => $five_gram_note,
            '_zvij_dobroimetje_note' => 'Član prejme 4,50 € za naslednji reload.',
        ],
    ],
    [
        'title' => 'FRUTTY CBD vršički 1 g',
        'slug' => 'frutty-cbd-vrsicki-1-g',
        'sku' => 'FRUTTY-CBD-1G',
        'legacy_slugs' => ['frutty-cbd', 'frutty-cbd-caj-1-g'],
        'legacy_source_urls' => ['https://zvij.si/izdelek/frutty-cbd/'],
        'price' => '4.20',
        'regular_price' => '5.00',
        'sale_price' => '4.20',
        'category' => 'CBD/CBG vršički',
        'category_slug' => 'cbd-cbg-vrsicki',
        'status' => 'publish',
        'tags' => ['vrsicki', 'reload', 'kit-addon', 'throwie'],
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/06/frutty-frontside.png',
        'excerpt' => '<p>FRUTTY CBD vršički v 1 g pakiranju. Prvi preizkus: 4,20 €.</p>',
        'content' => '<p>FRUTTY je linija CBD vršičkov, prej vezana na ime Bubble Gum.</p><p>Pakirano v 1 g enotah. Prvi FRUTTY je lahek prvi preizkus; naslednji nakup gre v dobroimetje logiko.</p><p>Primerno za čajno uporabo.</p>',
        'meta' => [
            '_zvij_former_name' => 'FRUTTY CBD / Bubble Gum',
            '_zvij_public_mapping' => 'Bubble Gum = FRUTTY',
            '_zvij_packaging_logic' => '1 x 1 g package',
            '_zvij_first_purchase_badge' => 'Prvič 4,20 €',
            '_zvij_dobroimetje_note' => 'Član prejme 0,80 € za naslednji reload.',
        ],
    ],
    [
        'title' => 'FRUTTY CBD vršički 5 g',
        'slug' => 'frutty-cbd-vrsicki-5-g',
        'sku' => 'FRUTTY-CBD-5X1G',
        'legacy_slugs' => ['frutty-cbd-caj-5-g'],
        'price' => '24.90',
        'regular_price' => '24.90',
        'category' => 'CBD/CBG vršički',
        'category_slug' => 'cbd-cbg-vrsicki',
        'status' => 'publish',
        'tags' => ['vrsicki', 'reload'],
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/06/frutty-frontside.png',
        'excerpt' => '<p>FRUTTY CBD vršički v 5 g paketu. ' . $five_gram_note . '</p>',
        'content' => '<p>Večji FRUTTY paket za jasen reload ritem in urejeno zalogo.</p><p><strong>' . $five_gram_note . '</strong></p><p>Primerno za čajno uporabo.</p>',
        'meta' => [
            '_zvij_packaging_logic' => '5 x 1 g packages',
            '_zvij_packaging_note' => $five_gram_note,
            '_zvij_dobroimetje_note' => 'Član prejme 3,50 € za naslednji reload.',
        ],
    ],
    [
        'title' => 'DUBI 42 aktivnih ogljikovih filtrov',
        'slug' => 'dubi-42-aktivnih-ogljikovih-filtrov',
        'sku' => 'TIP-167',
        'legacy_slugs' => ['dubi-aktivni-ogljikovi-filtri-42-kosov'],
        'legacy_source_urls' => ['https://zvij.si/izdelek/dubi-aktivni-ogljikovi-filtri-42-kosov/'],
        'price' => '7.99',
        'regular_price' => '7.99',
        'category' => 'DUBI filtri',
        'status' => 'publish',
        'tags' => ['dubi', 'reload', 'black-kit', 'silver-kit', 'gold-kit', 'throwie'],
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/10/dubi-front.png',
        'excerpt' => '<p>DUBI aktivni ogljikovi filtri v vrečki 42 kosov.</p>',
        'content' => '<p>Osnovno DUBI pakiranje za setup in reload.</p><p>42 filtrov. Malo pakiranje za setup in reload.</p>',
        'meta' => [
            '_zvij_packaging_logic' => '42 filters per bag',
            '_zvij_kit_role' => 'core kit component',
            '_zvij_dubi_youtube_url' => 'https://www.youtube.com/watch?v=5oNlpY17v9w',
            '_zvij_dobroimetje_note' => 'Član prejme 1,25 € za naslednji reload.',
        ],
    ],
    [
        'title' => 'DUBI 420 aktivnih ogljikovih filtrov',
        'slug' => 'dubi-420-aktivnih-ogljikovih-filtrov',
        'sku' => 'TIP-194',
        'legacy_source_urls' => ['https://zvij.si/izdelek/dubi-420-aktivnih-ogljikovih-filtrov/'],
        'price' => '75.00',
        'regular_price' => '75.00',
        'category' => 'DUBI filtri',
        'status' => 'publish',
        'tags' => ['dubi', 'reload'],
        'image_source_url' => 'https://zvij.si/wp-content/uploads/2023/10/dubi-front.png',
        'excerpt' => '<p>DUBI aktivni ogljikovi filtri v večjem paketu 420 kosov.</p>',
        'content' => '<p>Večji DUBI paket za urejen bulk/reload.</p><p>10 malih pakiranj v enem večjem paketu. Ni premium zato, ker je 420; je bolj urejen bulk/reload.</p>',
        'meta' => [
            '_zvij_packaging_logic' => '10 small packs in one larger package',
            '_zvij_kit_role' => 'reload/bulk product',
            '_zvij_dubi_youtube_url' => 'https://www.youtube.com/watch?v=5oNlpY17v9w',
            '_zvij_dobroimetje_note' => 'Član prejme 7,50 € za naslednji reload.',
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
        'excerpt' => '<p>DEV bundle: DUBI 42 + rolca + sample CBD/CBG vršičkov.</p>',
        'content' => '<p>Zvij setup paket je dev bundle za preverjanje prvega nakupa.</p><p>Predvidena vsebina: DUBI 42, rolca in sample CBD/CBG vršičkov. Končna vsebina mora biti potrjena.</p>',
        'meta' => [
            '_zvij_dev_bundle' => 'true',
            '_zvij_packaging_logic' => 'DUBI 42 + rolca + sample CBD/CBG vršički',
        ],
    ],
    [
        'title' => 'Rezervni vžigalnik',
        'slug' => 'rezervni-vzigalnik',
        'price' => '',
        'regular_price' => '',
        'category' => 'Setup dodatki',
        'category_slug' => 'setup-dodatki',
        'status' => 'draft',
        'excerpt' => '<p>Draft dodatek: poceni rezervni vžigalnik kot backup ali paketni add-on.</p>',
        'content' => '<p>Rezervni vžigalnik je zamišljen kot praktičen backup kos za paket ali ko ga potrebuješ. Za zdaj brez potrjenega dobavitelja, fotografij in končne cene.</p><p>Branding naj bo lahek: Zvij.si nalepka ali QR/member kartica v paketu, ne custom print.</p>',
        'meta' => [
            '_zvij_dev_placeholder' => 'true',
            '_zvij_price_target' => '1.50-2.50 EUR',
            '_zvij_accessory_role' => 'backup / when needed / package add-on',
            '_zvij_packaging_logic' => 'Zvij.si sticker or QR/member card, not custom print yet',
        ],
    ],
    [
        'title' => 'Clipper standard',
        'slug' => 'clipper-standard',
        'price' => '',
        'regular_price' => '',
        'category' => 'Setup dodatki',
        'category_slug' => 'setup-dodatki',
        'status' => 'draft',
        'excerpt' => '<p>Draft dodatek: standardni Clipper kot glavni normalni vžigalnik za setup.</p>',
        'content' => '<p>Standardni Clipper naj najprej ohrani originalno Clipper identiteto. Zvij.si plast pride skozi QR/member kartico v paketu, ne skozi prezgodnje custom brandiranje.</p>',
        'meta' => [
            '_zvij_dev_placeholder' => 'true',
            '_zvij_price_target' => '2.90-4.20 EUR',
            '_zvij_accessory_role' => 'main normal lighter',
            '_zvij_packaging_logic' => 'original Clipper identity + Zvij.si QR/member card in package',
        ],
    ],
    [
        'title' => 'Premium Clipper',
        'slug' => 'premium-clipper',
        'price' => '',
        'regular_price' => '',
        'category' => 'Setup dodatki',
        'category_slug' => 'setup-dodatki',
        'status' => 'draft',
        'excerpt' => '<p>Draft dodatek: premium Clipper kot osebni setup predmet, darilo ali pride item.</p>',
        'content' => '<p>Premium Clipper ne sme dobiti poceni nalepke direktno na vžigalnik. Če gre v Zvij.si smer, naj pride skozi boljšo embalažo, kartico ali kasnejšo omejeno serijo.</p><p>Možna prihodnja smer: Clipper x Zvij.si limited series.</p>',
        'meta' => [
            '_zvij_dev_placeholder' => 'true',
            '_zvij_price_target' => '14.90-29.90 EUR',
            '_zvij_accessory_role' => 'premium personal setup object / gift / pride item',
            '_zvij_packaging_logic' => 'premium packaging/card; no cheap sticker directly on premium lighter',
        ],
    ],
];

$catalog = array_merge($catalog, zvij_kit_components());

$synced = [];
foreach ($catalog as $product_data) {
    $product_id = zvij_catalog_product($product_data);
    $synced[] = $product_data['title'] . ' #' . $product_id . ' ' . $product_data['price'] . ' EUR';
}

// Kit / reload tags for existing published products (components are tagged inline above).
$kit_tag_map = [
    'dubi-42-aktivnih-ogljikovih-filtrov' => ['dubi', 'reload', 'black-kit', 'silver-kit', 'gold-kit', 'throwie'],
    'dubi-420-aktivnih-ogljikovih-filtrov' => ['dubi', 'reload'],
    'smokey-cbd-vrsicki-1-g' => ['vrsicki', 'reload', 'kit-addon', 'black-kit', 'silver-kit', 'gold-kit'],
    'chilly-cbg-vrsicki-1-g' => ['vrsicki', 'reload', 'kit-addon', 'black-kit', 'silver-kit', 'gold-kit'],
    'frutty-cbd-vrsicki-1-g' => ['vrsicki', 'reload', 'kit-addon', 'throwie'],
    'smokey-cbd-vrsicki-5-g' => ['vrsicki', 'reload'],
    'chilly-cbg-vrsicki-5-g' => ['vrsicki', 'reload'],
    'frutty-cbd-vrsicki-5-g' => ['vrsicki', 'reload'],
];
foreach ($kit_tag_map as $slug => $tags) {
    $product_id = zvij_catalog_find_product($slug);
    if ($product_id > 0) {
        zvij_catalog_apply_tags($product_id, $tags);
    }
}

// Persist the kit showcase definition for the theme.
update_option('zvij_kits', zvij_kit_definitions(), false);

foreach (['smokey-cbd-caj-10-g', 'chilly-cbg-caj-10-g', 'frutty-cbd-caj-10-g', 'smokey-cbd-vrsicki-10-g', 'chilly-cbg-vrsicki-10-g', 'frutty-cbd-vrsicki-10-g'] as $planned_slug) {
    $product_id = zvij_catalog_find_product($planned_slug);
    if ($product_id > 0) {
        wp_update_post(['ID' => $product_id, 'post_status' => 'draft']);
        update_post_meta($product_id, 'catalog_sync_status', 'planned_not_active_' . gmdate('Y-m-d'));
    }
}

foreach ([
    'DEV placeholder: CBD čaj' => 'DEV placeholder: CBD/CBG vršički',
    'CBD čaj' => 'CBD/CBG vršički',
] as $old_title => $new_title) {
    $old_product = get_page_by_title($old_title, OBJECT, 'product');
    if ($old_product) {
        wp_update_post([
            'ID' => $old_product->ID,
            'post_title' => $new_title,
            'post_name' => sanitize_title($new_title),
            'post_status' => 'draft',
        ]);
        wp_set_object_terms($old_product->ID, 'CBD/CBG vršički', 'product_cat', false);
    }
}

echo "Synced catalog products:\n- " . implode("\n- ", $synced) . "\n";
