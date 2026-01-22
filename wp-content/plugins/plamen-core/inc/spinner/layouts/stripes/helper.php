<?php

if ( ! function_exists( 'plamen_core_add_stripes_spinner_layout_option' ) ) {
	/**
	 * Function that set new value into page spinner layout options map
	 *
	 * @param array $layouts  - module layouts
	 *
	 * @return array
	 */
	function plamen_core_add_stripes_spinner_layout_option( $layouts ) {
		$layouts['stripes'] = esc_html__( 'Stripes', 'plamen-core' );
		
		return $layouts;
	}
	
	add_filter( 'plamen_core_filter_page_spinner_layout_options', 'plamen_core_add_stripes_spinner_layout_option' );
}