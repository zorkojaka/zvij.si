<div <?php qode_framework_class_attribute( $holder_classes ); ?>  <?php echo qode_framework_get_inline_style( $holder_styles ); ?> <?php echo qode_framework_get_inline_attrs( $holder_data, true ); ?>>
	<div class="qodef-m-text" data-count="<?php echo esc_attr( $text_data['count'] ); ?>"><?php echo qode_framework_wp_kses_html( 'content', $text_data['text'] ); ?></div>
	<?php if ( ! empty( $centered_text ) ) { ?>
		<div class="qodef-m-centered-text" <?php echo qode_framework_get_inline_style( $centered_text_styles ); ?>>
			<?php echo esc_html( $centered_text ); ?>
		</div>
	<?php } ?>
</div>