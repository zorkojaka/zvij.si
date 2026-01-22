<?php

if ( ! function_exists( 'plamen_core_add_call_to_action_variation_standard' ) ) {
	function plamen_core_add_call_to_action_variation_standard( $variations ) {
		
		$variations['standard'] = esc_html__( 'Standard', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_call_to_action_layouts', 'plamen_core_add_call_to_action_variation_standard' );
}
