<?php

if ( ! function_exists( 'plamen_core_add_icon_with_text_variation_before_title' ) ) {
	function plamen_core_add_icon_with_text_variation_before_title( $variations ) {
		
		$variations['before-title'] = esc_html__( 'Before Title', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_icon_with_text_layouts', 'plamen_core_add_icon_with_text_variation_before_title' );
}
