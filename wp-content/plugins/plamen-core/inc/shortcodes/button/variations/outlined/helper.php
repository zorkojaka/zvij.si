<?php

if ( ! function_exists( 'plamen_core_add_button_variation_outlined' ) ) {
	function plamen_core_add_button_variation_outlined( $variations ) {
		
		$variations['outlined'] = esc_html__( 'Outlined', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_button_layouts', 'plamen_core_add_button_variation_outlined' );
}
