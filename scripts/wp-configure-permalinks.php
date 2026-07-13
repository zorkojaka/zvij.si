<?php
/**
 * Slovenski permalinki (odločitev iz RELEASE_PLAN §6 / runbook Faza 1 korak 6,
 * opcija A) — poravnano z živo stranjo, ki že uporablja /izdelek/.
 * Ponovljivo, idempotentno; poganja se v dev IN ob produkcijski migraciji:
 *   docker compose run --rm wp-cli wp eval-file scripts/wp-configure-permalinks.php
 *   docker compose run --rm wp-cli wp rewrite flush --hard
 */
if (!function_exists('wc_get_page_id')) { WP_CLI::error('WooCommerce not loaded.'); }

update_option('woocommerce_permalinks', [
    'product_base' => 'izdelek',
    'category_base' => 'kategorija',
    'tag_base' => 'oznaka',
    'attribute_base' => '',
    'use_verbose_page_rules' => false,
]);
WP_CLI::log('woocommerce_permalinks: izdelek / kategorija / oznaka');

foreach ([
    'cart' => 'kosarica',
    'checkout' => 'blagajna',
    'myaccount' => 'moj-racun',
] as $page => $slug) {
    $id = wc_get_page_id($page);
    if ($id > 0) {
        wp_update_post(['ID' => $id, 'post_name' => $slug]);
        WP_CLI::log("page {$page} (#{$id}) -> /{$slug}/");
    } else {
        WP_CLI::warning("page {$page} not found — slug not set");
    }
}

foreach ([
    'woocommerce_checkout_pay_endpoint' => 'placilo',
    'woocommerce_checkout_order_received_endpoint' => 'narocilo-prejeto',
    'woocommerce_myaccount_add_payment_method_endpoint' => 'dodaj-placilno-metodo',
    'woocommerce_myaccount_delete_payment_method_endpoint' => 'izbrisi-placilno-metodo',
    'woocommerce_myaccount_set_default_payment_method_endpoint' => 'privzeta-placilna-metoda',
    'woocommerce_myaccount_orders_endpoint' => 'narocila',
    'woocommerce_myaccount_view_order_endpoint' => 'narocilo',
    'woocommerce_myaccount_downloads_endpoint' => 'prenosi',
    'woocommerce_myaccount_edit_account_endpoint' => 'uredi-racun',
    'woocommerce_myaccount_edit_address_endpoint' => 'naslovi',
    'woocommerce_myaccount_payment_methods_endpoint' => 'placilne-metode',
    'woocommerce_myaccount_lost_password_endpoint' => 'pozabljeno-geslo',
    'woocommerce_logout_endpoint' => 'odjava',
] as $option => $slug) {
    update_option($option, $slug);
}
WP_CLI::log('account/checkout endpoints set to Slovenian');
WP_CLI::log('Reminder: run `wp rewrite flush --hard` after this script.');
