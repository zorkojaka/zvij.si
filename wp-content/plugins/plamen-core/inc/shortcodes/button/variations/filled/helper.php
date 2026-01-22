<?php

if ( ! function_exists( 'plamen_core_add_button_variation_filled' ) ) {
	function plamen_core_add_button_variation_filled( $variations ) {
		
		$variations['filled'] = esc_html__( 'Filled', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_button_layouts', 'plamen_core_add_button_variation_filled' );
}
