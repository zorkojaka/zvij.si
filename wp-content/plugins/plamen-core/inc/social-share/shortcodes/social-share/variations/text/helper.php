<?php

if ( ! function_exists( 'plamen_core_add_social_share_variation_text' ) ) {
	function plamen_core_add_social_share_variation_text( $variations ) {
		
		$variations['text'] = esc_html__( 'Text', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_social_share_layouts', 'plamen_core_add_social_share_variation_text' );
}
