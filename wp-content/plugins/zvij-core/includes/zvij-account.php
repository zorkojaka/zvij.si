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

/** Keep the shared slot API used by existing account cards. */
function zvij_account_placeholder(string $label): string {
    $images = [
        'pozdrav' => ['welcome-v1', 'Izdelki Zvij.si, DUBI filtri in Ziggi rizle na kamniti podlagi'],
        'stopnja članstva' => ['tiers-v1', 'Pet simbolnih stopenj grinderja, ki zbirajo vedno več kristalov'],
        'kristali' => ['crystals-v1', 'Zbiralni predal srebrnega grinderja s kristali in kovanec za en evro'],
        'izziv' => ['quest-v1', 'Skrivnostna žerjavica in svetloba, skrita v dimu'],
        'povabi prijatelje' => ['friends-v1', 'Izdelki Zvij.si, Ziggi in DUBI, povezani s kristali'],
        'članstvo' => ['welcome-v1', 'Izdelki trgovine Zvij.si'],
        'zadnje naročilo' => ['welcome-v1', 'Izdelki Zvij.si za naslednji nakup'],
        'Zvij koda' => ['friends-v1', 'Izdelki Zvij.si in kristali za članske ugodnosti'],
    ];
    $image = $images[$label] ?? null;
    if (! $image) { return ''; }
    $url = plugins_url('assets/account-images/' . $image[0] . '.webp', dirname(__DIR__) . '/zvij-core.php');
    $html = '<figure class="zv-acc-media zv-acc-media--photo"><img src="' . esc_url($url) . '" alt="' . esc_attr($image[1]) . '" width="1600" height="534" loading="' . ($label === 'pozdrav' ? 'eager' : 'lazy') . '" decoding="async">';
    if ($label === 'kristali') { $html .= '<figcaption class="zv-acc-equation">100 kristalov<br><strong>= 1 €</strong></figcaption>'; }
    $html .= '</figure>';
    if ($label === 'stopnja članstva') { $html .= '<p class="zv-acc-image-note">Nakupi v zadnjih 12 mesecih → višja stopnja → več kristalov.<small>Grinderji simbolizirajo stopnje članstva, niso fizična nagrada.</small></p>'; }
    return $html;
}

add_action('wp_enqueue_scripts', function (): void {
    if (function_exists('is_account_page') && is_account_page()) {
        wp_enqueue_style('zvij-account', plugins_url('assets/account.css', dirname(__DIR__) . '/zvij-core.php'), [], '1.1.0');
    }
});
