<?php

if ( ! function_exists( 'plamen_core_add_icon_with_text_variation_before_content' ) ) {
	function plamen_core_add_icon_with_text_variation_before_content( $variations ) {
		
		$variations['before-content'] = esc_html__( 'Before Content', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_icon_with_text_layouts', 'plamen_core_add_icon_with_text_variation_before_content' );
}
