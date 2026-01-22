<?php

if ( ! function_exists( 'plamen_core_add_button_variation_textual' ) ) {
	function plamen_core_add_button_variation_textual( $variations ) {
		
		$variations['textual'] = esc_html__( 'Textual', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_button_layouts', 'plamen_core_add_button_variation_textual' );
}
