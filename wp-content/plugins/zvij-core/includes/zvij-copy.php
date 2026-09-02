<?php
/**
 * Besedišče nakupnega toka (Jaka, 1. 9. 2026).
 *
 * Nakup je pripovedan kot ritual, ne kot obrazec:
 *
 *   dodaj v grinder  →  zvijanje  →  prižgi
 *   (košarica)          (podatki)     (naročilo)
 *
 * Kristali ostanejo kristali — valuta ni del te metafore.
 *
 * Zakaj gettext in ne samo prevodna datoteka: nizi morajo ostati vezani na
 * izvorni angleški msgid, sicer bi jih posodobitev slovenskega prevoda
 * WooCommerca tiho povozila. Filter velja SAMO na prednjem delu strani —
 * v adminu mora pisati »košarica«, sicer je nemogoče brati WooCommerce
 * poročila in nastavitve.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Preslikava izvornih (angleških) WooCommerce nizov v Zvij besedišče.
 *
 * @return array<string,string>
 */
function zvij_copy_map(): array {
    return apply_filters('zvij_copy_map', [
        // Grinder = košarica
        'Add to cart'                       => 'Dodaj v grinder',
        'Add to Cart'                       => 'Dodaj v grinder',
        'View cart'                         => 'Poglej grinder',
        'Cart'                              => 'Grinder',
        'Cart totals'                       => 'Skupaj v grinderju',
        'Your cart is currently empty.'     => 'V grinderju še ni ničesar.',
        'Your cart is currently empty'      => 'V grinderju še ni ničesar',
        'Return to shop'                    => 'Nazaj v trgovino',
        'Update cart'                       => 'Posodobi grinder',
        'Remove this item'                  => 'Odstrani iz grinderja',
        // Bralnikom zaslona: WooCommerce sestavi aria-label z imenom izdelka.
        'Add to cart: &ldquo;%s&rdquo;'      => 'Dodaj v grinder: &ldquo;%s&rdquo;',
        // Sporocilo po AJAX dodajanju (data-success_message na gumbu).
        '&ldquo;%s&rdquo; has been added to your cart'  => '&ldquo;%s&rdquo; je v grinderju',
        '%s has been added to your cart.'    => 'Izdelek %s je v grinderju.',

        // Zvijanje = vnos podatkov
        'Proceed to checkout'               => 'Naprej na zvijanje',
        'Checkout'                          => 'Zvijanje',
        'Billing details'                   => 'Tvoji podatki',
        'Your order'                        => 'Kaj zviješ',

        // Prižgi = oddaja naročila
        'Place order'                       => 'Prižgi',
    ]);
}

/**
 * Zamenja WooCommerce nize po zgornji preslikavi.
 */
function zvij_copy_gettext($translated, $text, $domain) {
    // V adminu, REST in WP-CLI mora ostati izvorno besedisce, sicer so
    // WooCommerce porocila in nastavitve neberljivi.
    if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST) || (defined('WP_CLI') && WP_CLI)) {
        return $translated;
    }
    if ($domain !== 'woocommerce') {
        return $translated;
    }

    $map = zvij_copy_map();

    return $map[$text] ?? $translated;
}
add_filter('gettext', 'zvij_copy_gettext', 20, 3);

/**
 * Isto za nize s kontekstom (npr. gumb na blagajni).
 */
function zvij_copy_gettext_ctx($translated, $text, $context, $domain) {
    return zvij_copy_gettext($translated, $text, $domain);
}
add_filter('gettext_with_context', 'zvij_copy_gettext_ctx', 20, 4);

/** Gumb »Dodaj v grinder« na izdelku in v seznamu. */
add_filter('woocommerce_product_single_add_to_cart_text', static fn () => __('Dodaj v grinder', 'zvij-core'));
add_filter('woocommerce_product_add_to_cart_text', static fn () => __('Dodaj v grinder', 'zvij-core'));

/** Gumb, ki dejansko odda naročilo. */
add_filter('woocommerce_order_button_text', static fn () => __('Prižgi', 'zvij-core'));

/**
 * Obvestilo po dodajanju. WooCommerce sestavi niz z že prevedenim delom,
 * zato ga zamenjamo v celoti, ne prek gettexta.
 */
function zvij_copy_added_to_cart_message($message, $products) {
    return sprintf(
        '<a href="%s" tabindex="1" class="button wc-forward">%s</a> %s',
        esc_url(wc_get_cart_url()),
        esc_html__('Poglej grinder', 'zvij-core'),
        esc_html__('Dodano v grinder.', 'zvij-core')
    );
}
add_filter('wc_add_to_cart_message_html', 'zvij_copy_added_to_cart_message', 10, 2);
