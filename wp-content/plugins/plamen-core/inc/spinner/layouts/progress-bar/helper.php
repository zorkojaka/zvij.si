<?php

if ( ! function_exists( 'plamen_core_add_progress_bar_spinner_layout_option' ) ) {
	/**
	 * Function that set new value into page spinner layout options map
	 *
	 * @param array $layouts  - module layouts
	 *
	 * @return array
	 */
	function plamen_core_add_progress_bar_spinner_layout_option( $layouts ) {
		$layouts['progress-bar'] = esc_html__( 'Progress Bar', 'plamen-core' );
		
		return $layouts;
	}
	
	add_filter( 'plamen_core_filter_page_spinner_layout_options', 'plamen_core_add_progress_bar_spinner_layout_option' );
}

if ( ! function_exists( 'plamen_core_add_progress_bar_spinner_layout_classes' ) ) {
	/**
	 * Function that return classes for page spinner area
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	function plamen_core_add_progress_bar_spinner_layout_classes( $classes ) {
		$type = plamen_core_get_post_value_through_levels( 'qodef_page_spinner_type' );
		
		if ( $type === 'progress-bar' ) {
			$classes[] = 'qodef--custom-spinner';
		}
		
		return $classes;
	}
	
	add_filter( 'plamen_core_filter_page_spinner_classes', 'plamen_core_add_progress_bar_spinner_layout_classes' );
}