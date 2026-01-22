<?php

if ( ! function_exists( 'plamen_core_add_banner_variation_link_button' ) ) {
	function plamen_core_add_banner_variation_link_button( $variations ) {
		
		$variations['link-button'] = esc_html__( 'Link Button', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_banner_layouts', 'plamen_core_add_banner_variation_link_button' );
}
