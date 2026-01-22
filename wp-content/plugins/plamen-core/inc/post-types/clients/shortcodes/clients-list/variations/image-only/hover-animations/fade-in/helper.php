<?php
if ( ! function_exists( 'plamen_core_filter_clients_list_image_only_fade_in' ) ) {
	function plamen_core_filter_clients_list_image_only_fade_in( $variations ) {
		
		$variations['fade-in'] = esc_html__( 'Fade In', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_clients_list_image_only_animation_options', 'plamen_core_filter_clients_list_image_only_fade_in' );
}