#!/usr/bin/env bash
set -Eeuo pipefail

COMPOSE_PROJECT_NAME="${ZVIJ_COMPOSE_PROJECT_NAME:-zvij-dev}"
ENV_FILE="${ZVIJ_ENV_FILE:-/var/www/dev.inteligent.si/.env}"

compose_args=(--project-name "$COMPOSE_PROJECT_NAME")
if [ -f "$ENV_FILE" ]; then
  compose_args+=(--env-file "$ENV_FILE")
fi

wp() {
  docker compose "${compose_args[@]}" run --rm wp-cli "$@"
}

wp option update home 'https://dev.inteligent.si'
wp option update siteurl 'https://dev.inteligent.si'
wp option update blog_public 0
wp option update admin_email 'jaka@zvij.si'
wp theme activate zvij-theme
wp plugin activate zvij-core

if ! wp plugin is-installed woocommerce >/dev/null 2>&1; then
  wp plugin install woocommerce
fi
wp plugin activate woocommerce
wp option update woocommerce_coming_soon no
wp option update woocommerce_store_pages_only no

wp eval '
$pages = [
    ["Domov", "domov", "Tvoj ritual. Tvoja mera. Tvoj setup.", "Urejena trgovina za izdelke, reload in članstvo okoli tvojega rituala."],
    ["Trgovina", "trgovina", "<p>DEV prototip trgovine za DUBI filtre, CBD/CBG vršičke, setup pakete in reload logiko. Izdelki so začasni placeholderji, dokler niso potrjeni podatki, cene, pravna besedila in checkout pravila.</p>", "Razvojna trgovina za Zvij.si pakete, reload in članstvo."],
    ["Član Zvij.si", "clan-zvij-si", "<p>Član Zvij.si je prihodnji notranji sloj: Zvij koda, dobroimetje, reload in ponovitev naročila brez ponovnega iskanja.</p><p>Sistem ostaja brez preprodaje, brez cash payout obljub in brez MLM jezika. Dobroimetje je zamišljeno kot vrednost za naslednji reload.</p>", "Zvij koda, dobroimetje in reload navada kot prihodnji sistem."],
    ["DUBI filtri", "dubi-filtri", "<p>DUBI filtri so osnova za bolj urejen setup. Stran trenutno služi kot vizualni in vsebinski okvir za produktne podatke, ki jih mora Jaka še potrditi.</p><p>Naslednji korak: realne fotografije, pakiranja, zaloga, cena in reload interval.</p>", "Osnovni kos za bolj urejen setup in reload ritem."],
    ["CBD vršički", "cbd-vrsicki", "<p>CBD/CBG vršički so predstavljeni kot izbrani vršički z jasno mero. Čajna uporaba je lahko omenjena kot ena možnost, ne kot celotna identiteta izdelka.</p><p>Copy ostaja pri ritualu, meri in brez THC učinka.</p>", "SMOKEY, CHILLY in FRUTTY kot izbrani vršički."],
    ["Zvij setup", "zvij-setup", "<p>Zvij setup poveže izdelke, reload in navado okoli prvega nakupa. Prototip trenutno kaže smer: DUBI filtri, rolca, jasna zaloga in pot na naslednji reload.</p>", "Paketni pogled na izdelke, reload in miren ritual."],
    ["Kontakt", "kontakt", "<p>Kontaktna dev stran za Zvij.si. Produkcijski kontaktni obrazci, pravna besedila in podporni tokovi se dodajo po potrditvi vsebine.</p>", "Kontaktna točka za dev prototip."],
];

$page_ids = [];
foreach ($pages as [$title, $slug, $content, $excerpt]) {
    $page = get_page_by_path($slug, OBJECT, "page");
    $postarr = [
        "post_title" => $title,
        "post_name" => $slug,
        "post_content" => $content,
        "post_excerpt" => $excerpt,
        "post_status" => "publish",
        "post_type" => "page",
    ];

    if ($page) {
        $postarr["ID"] = $page->ID;
        $page_ids[$slug] = wp_update_post($postarr);
    } else {
        $page_ids[$slug] = wp_insert_post($postarr);
    }
}

update_option("show_on_front", "page");
update_option("page_on_front", $page_ids["domov"]);

if (class_exists("WooCommerce")) {
    update_option("woocommerce_shop_page_id", $page_ids["trgovina"]);
}

$menu_name = "Glavni meni";
$menu = wp_get_nav_menu_object($menu_name);
$menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu($menu_name);

foreach ((array) wp_get_nav_menu_items($menu_id) as $item) {
    wp_delete_post($item->ID, true);
}

foreach (["domov", "trgovina", "clan-zvij-si", "dubi-filtri", "cbd-vrsicki", "zvij-setup", "kontakt"] as $slug) {
    wp_update_nav_menu_item($menu_id, 0, [
        "menu-item-object-id" => $page_ids[$slug],
        "menu-item-object" => "page",
        "menu-item-type" => "post_type",
        "menu-item-status" => "publish",
    ]);
}

$locations = get_theme_mod("nav_menu_locations", []);
$locations["primary"] = $menu_id;
set_theme_mod("nav_menu_locations", $locations);

$categories = ["DUBI filtri", "CBD/CBG vršički", "Zvij setup", "Reload", "Član Zvij.si"];
foreach ($categories as $category) {
    if (! term_exists($category, "product_cat")) {
        wp_insert_term($category, "product_cat");
    }
}

$products = [
    ["DEV placeholder: DUBI filtri", "DUBI filtri", "DUBI filtri", "Vizualni placeholder za kartico izdelka. Cena, pakiranje in opis niso potrjeni."],
    ["DEV placeholder: CBD/CBG vršički", "CBD/CBG vršički", "CBD/CBG vršički", "Placeholder za izbrane vršičke. Čajna uporaba je lahko omenjena kot ena možnost."],
    ["DEV placeholder: Zvij setup paket", "Zvij setup", "Zvij setup paket", "Placeholder za začetni setup: DUBI filtri, rolca in kasnejša reload logika."],
    ["DEV placeholder: Reload paket", "Reload", "Reload paket", "Placeholder za ponovitev zaloge. Realni intervali in vsebina še niso potrjeni."],
];

foreach ($products as [$title, $category, $short_description, $description]) {
    $existing = get_page_by_title($title, OBJECT, "product");
    $postarr = [
        "post_title" => $title,
        "post_status" => "publish",
        "post_type" => "product",
        "post_excerpt" => $short_description,
        "post_content" => $description . "\n\nDEV placeholder: izdelek je objavljen samo zato, da javna trgovina pokaže vizualni prototip. Plačila in produkcijska dostava niso nastavljena.",
    ];

    if ($existing) {
        $postarr["ID"] = $existing->ID;
        $product_id = wp_update_post($postarr);
    } else {
        $product_id = wp_insert_post($postarr);
    }

    if ($product_id && ! is_wp_error($product_id)) {
        wp_set_object_terms($product_id, "simple", "product_type");
        wp_set_object_terms($product_id, $category, "product_cat");
        update_post_meta($product_id, "_catalog_visibility", "visible");
        update_post_meta($product_id, "_stock_status", "instock");
        update_post_meta($product_id, "_manage_stock", "no");
        delete_post_meta($product_id, "_regular_price");
        delete_post_meta($product_id, "_price");
    }
}
'
