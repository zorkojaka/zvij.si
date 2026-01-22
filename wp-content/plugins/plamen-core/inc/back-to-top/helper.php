<?php

if ( ! function_exists( 'plamen_core_is_back_to_top_enabled' ) ) {
	function plamen_core_is_back_to_top_enabled() {
		return plamen_core_get_post_value_through_levels( 'qodef_back_to_top' ) !== 'no';
	}
}

if ( ! function_exists( 'plamen_core_add_back_to_top_to_body_classes' ) ) {
	function plamen_core_add_back_to_top_to_body_classes( $classes ) {
		$classes[] = plamen_core_is_back_to_top_enabled() ? 'qodef-back-to-top--enabled' : '';
		
		return $classes;
	}
	
	add_filter( 'body_class', 'plamen_core_add_back_to_top_to_body_classes' );
}

if ( ! function_exists( 'plamen_core_load_back_to_top' ) ) {
	/**
	 * Loads Back To Top HTML
	 */
	function plamen_core_load_back_to_top() {
		
		if ( plamen_core_is_back_to_top_enabled() ) {
			$parameters = array();
			
			plamen_core_template_part( 'back-to-top', 'templates/back-to-top', '', $parameters );
		}
	}
	
	add_action( 'plamen_action_before_wrapper_close_tag', 'plamen_core_load_back_to_top' );
}