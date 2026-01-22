<?php if ( class_exists( 'PlamenCoreSocialShareShortcode' ) ) { ?>
	<div class="qodef-e-info-item qodef-e-info-social-share">
		<?php
		$params = array();
		$params['title'] = esc_html__( 'Share:', 'plamen-core' );
		
		echo PlamenCoreSocialShareShortcode::call_shortcode( $params ); ?>
	</div>
<?php } ?>