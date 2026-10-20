<?php
/** Moj račun: meni, kartice na nadzorni plošči in mesta za slike. */
if (! defined('ABSPATH')) {
    exit;
}

/** Trgovina nima prenosljivih izdelkov — »Prenosi« skrijemo, »Naslova« → »Naslovi«. */
add_filter('woocommerce_account_menu_items', function (array $items): array {
    unset($items['downloads']);
    if (isset($items['edit-address'])) {
        $items['edit-address'] = __('Naslovi', 'zvij-core');
    }
    return $items;
}, 20);

/** Označeno mesto, kamor kasneje pride prava slika ali ilustracija. */
function zvij_account_placeholder(string $label): string {
    return '<div class="zv-acc-media" role="img" aria-label="' . esc_attr('Prostor za sliko: ' . $label) . '"><span>' . esc_html('Slika · ' . $label) . '</span></div>';
}

add_action('wp_enqueue_scripts', function (): void {
    if (function_exists('is_account_page') && is_account_page()) {
        wp_enqueue_style('zvij-account', plugins_url('assets/account.css', dirname(__DIR__) . '/zvij-core.php'), [], '1.0.0');
    }
});
