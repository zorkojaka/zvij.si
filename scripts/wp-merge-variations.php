<?php
/**
 * Združi istovrstne izdelke v en izdelek z variacijami (Jaka, 16. 10. 2026).
 *
 *   DUBI 42 + 420                      → »DUBI filtri z aktivnim ogljem«, Pakiranje
 *   Ziggi Original/Natural/Hemp Slim   → »Ziggi Classic Slim«, Papir
 *   Ziggi Original/Natural Roll        → »Ziggi Roll + Tips + Tray«, Papir
 *   Ziggi Special Edition (3 dizajni)  → »Ziggi Special Edition«, Dizajn
 *   Zvij.si JollySafe (3 barve)        → »Zvij.si JollySafe«, Barva
 *
 * Prvi izdelek v skupini postane nadrejeni (obdrži ID). Vsak prejšnji izdelek
 * postane variacija s svojo ceno, SKU, zalogo, sliko, opisom, nabavno ceno,
 * velikostjo škatle in izjemo kristalov. Variacija dobi STARI slug izdelka
 * (kiti v `zvij_kits` jih iščejo po slugu); stari naslovi strani preusmerijo
 * na novi izdelek prek _wp_old_slug. Ostali izdelki gredo v koš (ne izbrišejo se).
 *
 * Idempotentno: skupina, katere nadrejeni izdelek je že variabilen, se preskoči.
 * Zagon: docker exec -u 33 <wordpress> php /var/www/html/scripts/wp-merge-variations.php [--dry-run]
 */
if (PHP_SAPI !== 'cli') { exit(1); }
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'zvij.si';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/wp-load.php';
$dry = in_array('--dry-run', $argv, true);

$groups = [
    [
        'name' => 'DUBI filtri z aktivnim ogljem', 'slug' => 'dubi-filtri-z-aktivnim-ogljem', 'attr' => 'Pakiranje',
        'short' => 'DUBI filtri z aktivnim ogljem ø 7 mm — vrečka 42 kosov ali velik paket 420 kosov.',
        'desc' => '<p>DUBI filtri z aktivnim ogljem ø 7 mm vstaviš namesto običajnega tulca. Izberi vrečko za preizkus ali velik paket, če filtre uporabljaš vsak dan.</p>',
        'members' => [
            71 => ['42 kosov', '<p>Vrečka 42 filtrov — osnovno pakiranje za setup in reload.</p>'],
            69 => ['420 kosov', '<p>Velik paket: 10 vrečk po 42 filtrov (420 kosov) za urejeno zalogo.</p>'],
        ],
    ],
    [
        'name' => 'Ziggi Classic Slim', 'slug' => 'ziggi-classic-slim', 'attr' => 'Papir',
        'short' => 'King Size Slim zavitek s pokrovčkom in filter konicami — bel, nebeljen ali konopljin papir.',
        'desc' => '<p>Ziggi v klasični Slim izvedbi: tanek papir King Size Slim (110 × 44 mm, 14 g/m²), zavitek s pokrovčkom in filter konice v istem paketu. Izberi papir: Original (bel), Natural (nebeljen) ali Hemp (iz konopljinih vlaken).</p>',
        'members' => [
            435 => ['Original (bel)', null],
            437 => ['Natural (nebeljen)', null],
            436 => ['Hemp (konoplja)', null],
        ],
    ],
    [
        'name' => 'Ziggi Roll + Tips + Tray', 'slug' => 'ziggi-roll-tips-tray', 'attr' => 'Papir',
        'short' => 'Zvitek papirja, filter konice in podlaga v enem paketu — bel ali nebeljen papir.',
        'desc' => '<p>Papir v zvitku (širina 44 mm), ki ga odviješ na svojo mero, plus filter konice in podlaga (tray) v isti embalaži. Izberi bel ali nebeljen papir.</p>',
        'members' => [
            442 => ['Original (bel)', null],
            443 => ['Natural (nebeljen)', null],
        ],
    ],
    [
        'name' => 'Ziggi Special Edition', 'slug' => 'ziggi-special-edition', 'attr' => 'Dizajn',
        'short' => 'Posebne izdaje Ziggi Original King Size Slim s filter konicami — izberi dizajn.',
        'desc' => '<p>Ziggi Original Special Edition: enak Slim format in vsebina kot Original (King Size Slim, 14 g/m², filter konice), druga grafika. Zbirateljska serija — ko poide, poide.</p>',
        'members' => [
            438 => ['Mystery Mix', null],
            439 => ['Mycelium Mystique', null],
            440 => ["Rocket's Odyssey", null],
        ],
    ],
    [
        'name' => 'Zvij.si JollySafe', 'slug' => 'zvij-si-jollysafe', 'attr' => 'Barva',
        'short' => 'Kovinski tulec z navojem in verižico — črn, srebrn ali zlat.',
        'desc' => '<p>Kovinski tulec z navojem, tesnilom in verižico. Setup ostane suh in diskreten, verižica pa ga drži pri ključih ali torbi. Na voljo v črni, srebrni in zlati barvi.</p>',
        'members' => [
            243 => ['Črna', null],
            244 => ['Srebrna', null],
            245 => ['Zlata', null],
        ],
    ],
];

$copy_meta = ['_zvij_cost', '_zvij_box_qty', '_zvij_kristali', '_zvij_reload_days'];

foreach ($groups as $group) {
    $ids = array_keys($group['members']);
    $parent_id = $ids[0];
    $parent = wc_get_product($parent_id);
    if (! $parent) {
        echo "PRESKOČIM {$group['name']}: ni izdelka #{$parent_id}\n";
        continue;
    }
    if ($parent->is_type('variable')) {
        echo "že združeno: {$group['name']} (#{$parent_id})\n";
        continue;
    }

    // 1. Podatki vseh izdelkov, preden kar koli spremenimo.
    $data = [];
    $gallery = [];
    $tags = [];
    foreach ($group['members'] as $id => [$value, $description]) {
        $p = wc_get_product($id);
        if (! $p || ! $p->is_type('simple')) {
            throw new RuntimeException("#{$id} ni preprost izdelek — ustavljeno, nič ni spremenjeno");
        }
        $meta = [];
        foreach ($copy_meta as $key) {
            $v = get_post_meta($id, $key, true);
            if ($v !== '') { $meta[$key] = $v; }
        }
        $data[$id] = [
            'value' => $value, 'slug' => $p->get_slug(), 'name' => $p->get_name(),
            'regular' => $p->get_regular_price(), 'sale' => $p->get_sale_price(), 'sku' => $p->get_sku(),
            'manage' => $p->get_manage_stock(), 'qty' => $p->get_stock_quantity(), 'status' => $p->get_stock_status(),
            'image' => $p->get_image_id(), 'description' => $description ?? $p->get_description(), 'meta' => $meta,
            'weight' => $p->get_weight(),
        ];
        $gallery = array_merge($gallery, [(int) $p->get_image_id()], array_map('intval', $p->get_gallery_image_ids()));
        $tags = array_merge($tags, wp_get_post_terms($id, 'product_tag', ['fields' => 'ids']));
        printf("  #%d %s → %s: %s € · SKU %s · zaloga %s\n", $id, $p->get_name(), $value, $p->get_price(), $p->get_sku(), var_export($p->get_stock_quantity(), true));
    }
    echo "{$group['name']} (#{$parent_id}) — " . count($data) . " variacij\n";
    if ($dry) {
        continue;
    }

    // 2. SKU-ji se morajo sprostiti, preden jih dobijo variacije; ostali izdelki v koš.
    foreach ($ids as $id) {
        $p = wc_get_product($id);
        $p->set_sku('');
        $p->save();
        if ($id !== $parent_id) {
            wp_trash_post($id);
        }
    }

    // 3. Nadrejeni izdelek postane variabilen.
    wp_set_object_terms($parent_id, 'variable', 'product_type');
    wc_delete_product_transients($parent_id);
    clean_post_cache($parent_id);
    $parent = new WC_Product_Variable($parent_id);
    $attribute = new WC_Product_Attribute();
    $attribute->set_name($group['attr']);
    $attribute->set_options(array_column($data, 'value'));
    $attribute->set_visible(true);
    $attribute->set_variation(true);
    $parent->set_attributes([$attribute]);
    $parent->set_name($group['name']);
    $parent->set_short_description($group['short']);
    $parent->set_description($group['desc']);
    $parent->set_manage_stock(false);
    $parent->set_regular_price('');
    $parent->set_sale_price('');
    $parent->set_image_id($data[$parent_id]['image']);
    $parent->set_gallery_image_ids(array_values(array_diff(array_unique(array_filter($gallery)), [(int) $data[$parent_id]['image']])));
    $parent->set_default_attributes([sanitize_title($group['attr']) => $data[$parent_id]['value']]);
    foreach ($copy_meta as $key) {
        $parent->delete_meta_data($key);
    }
    $parent->save();
    wp_set_object_terms($parent_id, array_values(array_unique(array_map('intval', $tags))), 'product_tag');
    wp_update_post(['ID' => $parent_id, 'post_name' => $group['slug']]); // shrani stari slug kot _wp_old_slug
    foreach ($data as $id => $row) {
        if ($id !== $parent_id && ! in_array($row['slug'], get_post_meta($parent_id, '_wp_old_slug'), true)) {
            add_post_meta($parent_id, '_wp_old_slug', $row['slug']);
        }
    }

    // 4. Variacije.
    $order = 0;
    foreach ($data as $id => $row) {
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($parent_id);
        $variation->set_attributes([sanitize_title($group['attr']) => $row['value']]);
        $variation->set_regular_price($row['regular']);
        $variation->set_sale_price($row['sale']);
        $variation->set_sku($row['sku']);
        $variation->set_manage_stock($row['manage']);
        if ($row['manage']) {
            $variation->set_stock_quantity($row['qty']);
        }
        $variation->set_stock_status($row['status']);
        $variation->set_image_id($row['image']);
        $variation->set_description($row['description']);
        $variation->set_weight($row['weight']);
        $variation->set_menu_order($order++);
        foreach ($row['meta'] as $key => $value) {
            $variation->update_meta_data($key, $value);
        }
        $variation->update_meta_data('_zvij_merged_from', $id);
        $variation_id = $variation->save();
        global $wpdb;
        $wpdb->update($wpdb->posts, ['post_name' => $row['slug']], ['ID' => $variation_id]);
        clean_post_cache($variation_id);
        printf("    variacija #%d %s (slug %s)\n", $variation_id, $row['value'], $row['slug']);
    }
    WC_Product_Variable::sync($parent_id);
    wc_delete_product_transients($parent_id);
    echo "  → " . get_permalink($parent_id) . "\n";
}
echo $dry ? "SUHI TEK — nič spremenjeno\n" : "KONČANO\n";
