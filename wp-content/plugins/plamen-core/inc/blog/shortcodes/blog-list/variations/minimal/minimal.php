<?php

if ( ! function_exists( 'plamen_core_add_blog_list_variation_minimal' ) ) {
	function plamen_core_add_blog_list_variation_minimal( $variations ) {
		$variations['minimal'] = esc_html__( 'Minimal', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_blog_list_layouts', 'plamen_core_add_blog_list_variation_minimal' );
}