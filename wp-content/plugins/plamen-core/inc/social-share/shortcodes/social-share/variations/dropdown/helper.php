<?php

if ( ! function_exists( 'plamen_core_add_social_share_variation_dropdown' ) ) {
	function plamen_core_add_social_share_variation_dropdown( $variations ) {
		
		$variations['dropdown'] = esc_html__( 'Dropdown', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_social_share_layouts', 'plamen_core_add_social_share_variation_dropdown' );
}
