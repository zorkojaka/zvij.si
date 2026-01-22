<?php

if ( ! function_exists( 'plamen_core_add_banner_variation_link_overlay' ) ) {
	function plamen_core_add_banner_variation_link_overlay( $variations ) {
		
		$variations['link-overlay'] = esc_html__( 'Link Overlay', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_banner_layouts', 'plamen_core_add_banner_variation_link_overlay' );
}
