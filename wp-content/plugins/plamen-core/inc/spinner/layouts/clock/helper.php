<?php

if ( ! function_exists( 'plamen_core_add_clock_spinner_layout_option' ) ) {
	/**
	 * Function that set new value into page spinner layout options map
	 *
	 * @param array $layouts  - module layouts
	 *
	 * @return array
	 */
	function plamen_core_add_clock_spinner_layout_option( $layouts ) {
		$layouts['clock'] = esc_html__( 'Clock', 'plamen-core' );
		
		return $layouts;
	}
	
	add_filter( 'plamen_core_filter_page_spinner_layout_options', 'plamen_core_add_clock_spinner_layout_option' );
}