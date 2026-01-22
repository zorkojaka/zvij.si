<?php

if ( ! function_exists( 'plamen_core_add_item_showcase_variation_list' ) ) {
	function plamen_core_add_item_showcase_variation_list( $variations ) {
		$variations['list'] = esc_html__( 'List', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_item_showcase_layouts', 'plamen_core_add_item_showcase_variation_list' );
}
