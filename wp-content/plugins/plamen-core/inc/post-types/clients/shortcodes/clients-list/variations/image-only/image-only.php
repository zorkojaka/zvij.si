<?php

if ( ! function_exists( 'plamen_core_add_clients_list_variation_image_only' ) ) {
	function plamen_core_add_clients_list_variation_image_only( $variations ) {
		
		$variations['image-only'] = esc_html__( 'Image Only', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_clients_list_layouts', 'plamen_core_add_clients_list_variation_image_only' );
}