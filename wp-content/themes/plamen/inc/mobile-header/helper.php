<?php

if ( ! function_exists( 'plamen_load_page_mobile_header' ) ) {
	/**
	 * Function which loads page template module
	 */
	function plamen_load_page_mobile_header() {
		// Include mobile header template
		echo apply_filters( 'plamen_filter_mobile_header_template', plamen_get_template_part( 'mobile-header', 'templates/mobile-header' ) );
	}
	
	add_action( 'plamen_action_page_header_template', 'plamen_load_page_mobile_header' );
}

if ( ! function_exists( 'plamen_register_mobile_navigation_menus' ) ) {
	/**
	 * Function which registers navigation menus
	 */
	function plamen_register_mobile_navigation_menus() {
		$navigation_menus = apply_filters( 'plamen_filter_register_mobile_navigation_menus', array( 'mobile-navigation' => esc_html__( 'Mobile Navigation', 'plamen' ) ) );
		
		if ( ! empty( $navigation_menus ) ) {
			register_nav_menus( $navigation_menus );
		}
	}
	
	add_action( 'plamen_action_after_include_modules', 'plamen_register_mobile_navigation_menus' );
}