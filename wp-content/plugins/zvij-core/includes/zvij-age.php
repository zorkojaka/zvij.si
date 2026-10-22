<?php
/**
 * Confirmation at checkout for carts containing CBD/CBG flowers only.
 * This is a shop policy, not a determination of the products' legal status.
 * No entrance dialog or age-confirmation cookie is created.
 */
if (! defined('ABSPATH')) {
    exit;
}

function zvij_age_product_requires_confirmation($product): bool {
    if (! $product instanceof WC_Product) {
        return false;
    }
    $id = $product->get_parent_id() ?: $product->get_id();
    $category = defined('ZVIJ_GRAM_CATEGORY') ? ZVIJ_GRAM_CATEGORY : 'cbd-cbg-vrsicki';
    return has_term($category, 'product_cat', $id);
}

function zvij_age_cart_requires_confirmation(): bool {
    if (! function_exists('WC') || ! WC()->cart) {
        return false;
    }
    foreach (WC()->cart->get_cart() as $item) {
        if ((float) ($item['quantity'] ?? 0) <= 0) {
            continue;
        }
        $product = $item['data'] ?? null;
        if (! $product instanceof WC_Product) {
            $product = wc_get_product(! empty($item['variation_id']) ? $item['variation_id'] : ($item['product_id'] ?? 0));
        }
        if (zvij_age_product_requires_confirmation($product)) {
            return true;
        }
    }
    return false;
}

function zvij_age_order_requires_confirmation(WC_Order $order): bool {
    foreach ($order->get_items() as $item) {
        if ($item->get_quantity() > 0 && zvij_age_product_requires_confirmation($item->get_product())) {
            return true;
        }
    }
    return false;
}

function zvij_age_is_confirmed(array $posted): bool {
    return isset($posted['zvij_age_confirm']) && is_scalar($posted['zvij_age_confirm'])
        && (string) $posted['zvij_age_confirm'] === '1';
}

function zvij_age_checkout_render(): void {
    if (! zvij_age_cart_requires_confirmation()) {
        return;
    }
    $posted = $_POST; // phpcs:ignore WordPress.Security.NonceVerification
    // WooCommerce replaces this fragment when the checkout/cart changes.
    if (isset($posted['post_data']) && is_string($posted['post_data'])) {
        parse_str(wp_unslash($posted['post_data']), $posted);
    }
    ?>
    <p class="form-row validate-required zvij-age-confirm" id="zvij_age_confirm_field">
      <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
        <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="zvij_age_confirm" id="zvij_age_confirm" value="1" required aria-required="true" <?php checked(zvij_age_is_confirmed($posted)); ?>>
        <span><?php esc_html_e('Potrjujem, da sem star vsaj 18 let. Potrditev velja za CBD/CBG vršičke v tem naročilu.', 'zvij-core'); ?></span>&nbsp;<abbr class="required" title="<?php esc_attr_e('obvezno', 'zvij-core'); ?>">*</abbr>
      </label>
    </p>
    <?php
}
add_action('woocommerce_review_order_before_submit', 'zvij_age_checkout_render', 5);

function zvij_age_checkout_validate(): void {
    if (zvij_age_cart_requires_confirmation() && ! zvij_age_is_confirmed($_POST)) { // phpcs:ignore WordPress.Security.NonceVerification
        wc_add_notice(__('Za nakup CBD/CBG vršičkov potrdi, da si star vsaj 18 let.', 'zvij-core'), 'error');
    }
}
add_action('woocommerce_checkout_process', 'zvij_age_checkout_validate');

function zvij_age_checkout_save($order): void {
    if (! $order instanceof WC_Order || ! zvij_age_order_requires_confirmation($order)) {
        return;
    }
    if (! zvij_age_is_confirmed($_POST)) { // phpcs:ignore WordPress.Security.NonceVerification
        throw new Exception(__('Za nakup CBD/CBG vršičkov potrdi, da si star vsaj 18 let.', 'zvij-core'));
    }
    $order->update_meta_data('_zvij_age_confirmed', current_time('mysql'));
}
add_action('woocommerce_checkout_create_order', 'zvij_age_checkout_save');

add_action('woocommerce_admin_order_data_after_billing_address', function ($order): void {
    $at = (string) $order->get_meta('_zvij_age_confirmed');
    if ($at === '' && ! zvij_age_order_requires_confirmation($order)) {
        return;
    }
    echo '<p><strong>' . esc_html__('Starost 18+', 'zvij-core') . ':</strong> '
        . ($at !== '' ? esc_html(sprintf(__('potrjeno %s', 'zvij-core'), mysql2date('j. n. Y H:i', $at))) : esc_html__('ni potrditve', 'zvij-core'))
        . '</p>';
});
