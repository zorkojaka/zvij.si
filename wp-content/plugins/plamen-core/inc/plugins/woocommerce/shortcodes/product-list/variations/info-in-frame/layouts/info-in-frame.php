<div <?php wc_product_class( $item_classes ); ?>>
    <div class="qodef-woo-product-inner">
        <?php if ( has_post_thumbnail() ) { ?>
            <div class="qodef-woo-product-image">
                <?php plamen_core_template_part( 'plugins/woocommerce/shortcodes/product-list', 'templates/post-info/mark' ); ?>
                <?php plamen_core_template_part( 'plugins/woocommerce/shortcodes/product-list', 'templates/post-info/image', '', $params ); ?>
                <div class="qodef-woo-product-image-inner" <?php qode_framework_inline_style($wrapper_styles); ?>>
                    <?php
                    // Hook to include additional content inside product list item image
                    do_action( 'plamen_core_action_product_list_item_additional_image_content' );
                    ?>
                </div>
            </div>
        <?php } ?>
        <div class="qodef-woo-product-info" <?php qode_framework_inline_style($wrapper_styles); ?>>
            <?php plamen_core_template_part( 'plugins/woocommerce/shortcodes/product-list', 'templates/post-info/category', '', $params ); ?>
            <?php plamen_core_template_part( 'plugins/woocommerce/shortcodes/product-list', 'templates/post-info/title', '', $params ); ?>
            <div class="qodef-woo-product-hover-info">
                <?php plamen_core_template_part( 'plugins/woocommerce/shortcodes/product-list', 'templates/post-info/price', '', $params ); ?>
                <?php plamen_core_template_part( 'plugins/woocommerce/shortcodes/product-list', 'templates/post-info/add-to-cart' ); ?>
            </div>
        </div>
        <?php plamen_core_template_part( 'plugins/woocommerce/shortcodes/product-list', 'templates/post-info/link' ); ?>
    </div>
</div>