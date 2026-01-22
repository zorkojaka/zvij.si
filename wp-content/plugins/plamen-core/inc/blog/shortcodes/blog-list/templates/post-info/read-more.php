<?php if ( ! post_password_required() && class_exists( 'PlamenCoreButtonShortcode' ) ) { ?>
	<div class="qodef-e-read-more">
		<?php
		$button_params = array(
			'link' => get_the_permalink(),
            'button_layout' => 'textual',
			'text' => esc_html__( 'Read More', 'plamen-core' )
		);
		
		echo PlamenCoreButtonShortcode::call_shortcode( $button_params ); ?>
	</div>
<?php } ?>