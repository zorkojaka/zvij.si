<?php

if ( ! function_exists( 'plamen_core_add_social_share_variation_list' ) ) {
	function plamen_core_add_social_share_variation_list( $variations ) {
		
		$variations['list'] = esc_html__( 'List', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_social_share_layouts', 'plamen_core_add_social_share_variation_list' );
}
