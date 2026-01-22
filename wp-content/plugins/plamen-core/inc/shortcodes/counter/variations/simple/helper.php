<?php

if ( ! function_exists( 'plamen_core_add_counter_variation_simple' ) ) {
	function plamen_core_add_counter_variation_simple( $variations ) {
		
		$variations['simple'] = esc_html__( 'Simple', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_counter_layouts', 'plamen_core_add_counter_variation_simple' );
}
