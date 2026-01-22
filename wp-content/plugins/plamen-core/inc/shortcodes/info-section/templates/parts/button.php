<?php if ( ! empty( $button_params ) && ! empty ( $button_params['text'] ) && class_exists( 'PlamenCoreButtonShortcode' ) ) { ?>
	<div class="qodef-m-button">
		<?php echo PlamenCoreButtonShortcode::call_shortcode( $button_params ); ?>
	</div>
<?php } ?>