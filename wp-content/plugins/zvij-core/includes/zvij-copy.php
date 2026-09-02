<?php
/**
 * Besedišče nakupnega toka (Jaka, 1. 9. 2026).
 *
 * Ritual nakupa:  dodaj v grinder  →  zvijanje  →  prižgi
 *
 * Metafora nosi KOŠARICO (tam ni česa narobe razumeti), na blagajni pa je
 * samo okras: vsi napisi, ki vodijo plačilo — »Blagajna«, »Nadaljuj na
 * blagajno«, »Oddaj naročilo« — ostanejo nedvoumni. Ritual je tam viden
 * kot korakovnik nad obrazcem in kot pripis pod gumbom, ne kot oznaka
 * dejanja. Odločitev Jake, 1. 9. 2026: »ne sme biti dvoumno«.
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

        // Blagajna ostane blagajna. Tu spreminjamo samo ton, ne pomena:
        // nobena od teh zamenjav ne skriva, kaj gumb naredi. Privzeti
        // slovenski prevod WooCommerca tudi vika, stran pa dosledno tika.
        'Billing details'                   => 'Tvoji podatki',
        'Your order'                        => 'Tvoje naročilo',
        'Proceed to checkout'               => 'Nadaljuj na blagajno',
        'Place order'                       => 'Oddaj naročilo',
        'Calculate shipping'                => 'Izračunaj dostavo',
        'Enter your coupon code'            => 'Vpiši kodo kupona',
        'Notes about your order, e.g. special notes for delivery.'
                                            => 'Opombe o naročilu, npr. navodila za dostavo.',
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

/** Gumb oddaje: jasen napis in tikanje (privzeti prevod je »Kupite sedaj«). */
add_filter('woocommerce_order_button_text', static fn () => __('Oddaj naročilo', 'zvij-core'));

/**
 * Korakovnik nakupa — ritual kot okras, ne kot navodilo.
 *
 * Prikaže se nad košarico in nad blagajno. Poimenovanja korakov so
 * metafora, podnapisi pa povedo, kaj se na koraku dejansko zgodi, da
 * nihče ne ugiba.
 */
function zvij_copy_steps(int $active): void {
    $steps = [
        1 => [ 'Grindanje', __('izbereš izdelke', 'zvij-core') ],
        2 => [ 'Zvijanje', __('vpišeš podatke', 'zvij-core') ],
        3 => [ 'Prižgi',   __('oddaš naročilo', 'zvij-core') ],
    ];
    ?>
    <ol class="zv-steps" aria-label="<?php esc_attr_e('Koraki nakupa', 'zvij-core'); ?>">
      <?php foreach ($steps as $n => [$label, $note]) :
          $state = $n === $active ? ' is--active' : ($n < $active ? ' is--done' : '');
      ?>
        <li class="zv-steps__step<?php echo esc_attr($state); ?>"<?php echo $n === $active ? ' aria-current="step"' : ''; ?>>
          <span class="zv-steps__n"><?php echo esc_html((string) $n); ?></span>
          <span class="zv-steps__label"><?php echo esc_html($label); ?></span>
          <span class="zv-steps__note"><?php echo esc_html($note); ?></span>
        </li>
      <?php endforeach; ?>
    </ol>
    <?php
}
add_action('woocommerce_before_cart', static fn () => zvij_copy_steps(1), 5);
add_action('woocommerce_before_checkout_form', static fn () => zvij_copy_steps(2), 5);

/** Pripis pod gumbom za oddajo — brez njega gumb ne izgubi pomena. */
add_action('woocommerce_review_order_after_submit', static function () {
    echo '<p class="zv-submit-note">' . esc_html__('Klik odda naročilo — in prižgi si.', 'zvij-core') . '</p>';
}, 20);
