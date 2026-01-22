<?php

if ( ! function_exists( 'plamen_core_register_standard_title_layout' ) ) {
	function plamen_core_register_standard_title_layout( $layouts ) {
		$layouts['standard'] = 'PlamenCoreStandardTitle';
		
		return $layouts;
	}
	
	add_filter( 'plamen_core_filter_register_title_layouts', 'plamen_core_register_standard_title_layout');
}

if ( ! function_exists( 'plamen_core_add_standard_title_layout_option' ) ) {
	/**
	 * Function that set new value into title layout options map
	 *
	 * @param array $layouts  - module layouts
	 *
	 * @return array
	 */
	function plamen_core_add_standard_title_layout_option( $layouts ) {
		$layouts['standard'] = esc_html__( 'Standard', 'plamen-core' );
		
		return $layouts;
	}
	
	add_filter( 'plamen_core_filter_title_layout_options', 'plamen_core_add_standard_title_layout_option' );
}

if ( ! function_exists( 'plamen_core_get_standard_title_layout_subtitle_text' ) ) {
	/**
	 * Function that render current page subtitle text
	 */
	function plamen_core_get_standard_title_layout_subtitle_text() {
		$subtitle_meta = plamen_core_get_post_value_through_levels( 'qodef_page_title_subtitle' );
		$subtitle      = array( 'subtitle' => ! empty( $subtitle_meta ) ? $subtitle_meta : '' );
		
		return apply_filters( 'plamen_core_filter_standard_title_layout_subtitle_text', $subtitle );
	}
}
