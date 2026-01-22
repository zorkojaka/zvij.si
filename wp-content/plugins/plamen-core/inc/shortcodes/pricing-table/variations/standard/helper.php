<?php

if ( ! function_exists( 'plamen_core_add_pricing_table_variation_standard' ) ) {
	function plamen_core_add_pricing_table_variation_standard( $variations ) {
		
		$variations['standard'] = esc_html__( 'Standard', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_pricing_table_layouts', 'plamen_core_add_pricing_table_variation_standard' );
}
