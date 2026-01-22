<?php

if ( ! function_exists( 'plamen_core_add_blog_list_variation_metro' ) ) {
	function plamen_core_add_blog_list_variation_metro( $variations ) {
		$variations['metro'] = esc_html__( 'Metro', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_blog_list_layouts', 'plamen_core_add_blog_list_variation_metro' );
}