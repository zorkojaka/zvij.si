<div <?php qode_framework_class_attribute( $item_classes ); ?> data-slide-index="<?php echo esc_attr($params['itemnum']); ?>">
	<div class="qodef-e-inner">
        <?php plamen_core_list_sc_template_part( 'post-types/testimonials/shortcodes/testimonials-list', 'post-info/title', '', $params ); ?>
        <?php plamen_core_list_sc_template_part( 'post-types/testimonials/shortcodes/testimonials-list', 'post-info/text', '', $params ); ?>
        <?php plamen_core_list_sc_template_part( 'post-types/testimonials/shortcodes/testimonials-list', 'post-info/author', '', $params ); ?>
	</div>
</div>