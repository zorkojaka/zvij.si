<?php

if ( ! function_exists( 'plamen_core_add_image_with_text_variation_text_below' ) ) {
	function plamen_core_add_image_with_text_variation_text_below( $variations ) {
		
		$variations['text-below'] = esc_html__( 'Text Below', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_image_with_text_layouts', 'plamen_core_add_image_with_text_variation_text_below' );
}
