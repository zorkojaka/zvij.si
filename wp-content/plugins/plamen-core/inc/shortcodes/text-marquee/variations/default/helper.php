<?php

if ( ! function_exists( 'plamen_core_add_text_marquee_variation_default' ) ) {
	function plamen_core_add_text_marquee_variation_default( $variations ) {
		
		$variations['default'] = esc_html__( 'Default', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_text_marquee_layouts', 'plamen_core_add_text_marquee_variation_default' );
}