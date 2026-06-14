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

wp eval '
$pages = [
    ["Domov", "domov", "Tvoj ritual. Tvoja mera. Tvoj setup."],
    ["Trgovina", "trgovina", "Razvojna trgovina za Zvij.si pakete, refille in clanstvo."],
    ["Član Zvij.si", "clan-zvij-si", "Clanstvo, Zvij koda in dobroimetje bodo tukaj dobili jasno dev osnovo."],
    ["DUBI filtri", "dubi-filtri", "Placeholder stran za DUBI filtre in setup okoli prvega nakupa."],
    ["CBD čaj", "cbd-caj", "Posuseni konopljini vrsicki za caj. Brez zdravstvenih obljub in brez kajenja v copyju."],
    ["Zvij setup", "zvij-setup", "Paketni pogled na pripomocke, refille in miren ritual."],
    ["Kontakt", "kontakt", "Kontaktna dev stran za Zvij.si."],
];

$page_ids = [];
foreach ($pages as [$title, $slug, $content]) {
    $page = get_page_by_path($slug, OBJECT, "page");
    $postarr = [
        "post_title" => $title,
        "post_name" => $slug,
        "post_content" => $content,
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

foreach (["domov", "trgovina", "clan-zvij-si", "dubi-filtri", "cbd-caj", "zvij-setup", "kontakt"] as $slug) {
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

$categories = ["DUBI filtri", "CBD čaj", "Zvij setup", "Refill", "Član Zvij.si"];
foreach ($categories as $category) {
    if (! term_exists($category, "product_cat")) {
        wp_insert_term($category, "product_cat");
    }
}

$products = [
    ["DUBI filtri", "DUBI filtri"],
    ["CBD čaj", "CBD čaj"],
    ["Zvij setup paket", "Zvij setup"],
];

foreach ($products as [$title, $category]) {
    $existing = get_page_by_title($title, OBJECT, "product");
    $product_id = $existing ? $existing->ID : wp_insert_post([
        "post_title" => $title,
        "post_status" => "draft",
        "post_type" => "product",
        "post_content" => "Placeholder dev izdelek. Podrobnosti, cene, pravna besedila in checkout nastavitve se dolocijo kasneje.",
    ]);

    if ($product_id && ! is_wp_error($product_id)) {
        wp_set_object_terms($product_id, "simple", "product_type");
        wp_set_object_terms($product_id, $category, "product_cat");
    }
}
'
