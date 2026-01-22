<?php

if ( ! function_exists( 'plamen_core_add_image_with_text_variation_text_on_image' ) ) {
	function plamen_core_add_image_with_text_variation_text_on_image( $variations ) {
		
		$variations['text-on'] = esc_html__( 'Text On Image', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_image_with_text_layouts', 'plamen_core_add_image_with_text_variation_text_on_image' );
}
