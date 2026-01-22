<?php

if ( ! function_exists( 'plamen_core_add_team_list_variation_info_on_hover' ) ) {
	function plamen_core_add_team_list_variation_info_on_hover( $variations ) {
		
		$variations['info-on-hover'] = esc_html__( 'Info on Hover', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_team_list_layouts', 'plamen_core_add_team_list_variation_info_on_hover' );
}