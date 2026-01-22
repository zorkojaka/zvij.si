<div <?php qode_framework_class_attribute( $holder_classes ); ?>>
	<?php plamen_core_template_part( 'shortcodes/image-with-text', 'templates/parts/image', '', $params ) ?>
	<div class="qodef-m-content">
		<?php plamen_core_template_part( 'shortcodes/image-with-text', 'templates/parts/tagline', '', $params ) ?>
		<?php plamen_core_template_part( 'shortcodes/image-with-text', 'templates/parts/title', '', $params ) ?>
		<?php plamen_core_template_part( 'shortcodes/image-with-text', 'templates/parts/text', '', $params ) ?>
        <?php if ( ! empty( $link ) ) : ?>
            <a itemprop="url" href="<?php echo esc_url( $link ); ?>" target="<?php echo esc_attr( $target ); ?>"></a>
        <?php endif; ?>

	</div>
</div>