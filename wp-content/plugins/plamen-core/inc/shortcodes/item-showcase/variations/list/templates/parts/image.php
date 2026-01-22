<?php if ( ! empty( $main_image ) ) { ?>
	<div class="qodef-m-image" <?php qode_framework_inline_style( $this_shortcode->get_image_styles( $params ) ); ?>>
		<?php echo wp_get_attachment_image( $main_image, 'full' ); ?>
	</div>
<?php } ?>