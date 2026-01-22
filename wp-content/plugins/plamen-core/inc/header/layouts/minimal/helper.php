<?php
if ( ! function_exists( 'plamen_core_add_minimal_header_global_option' ) ) {
	/**
	 * This function set header type value for global header option map
	 */

	function plamen_core_add_minimal_header_global_option( $header_layout_options ) {
		$header_layout_options['minimal'] = array(
			'image' => PLAMEN_CORE_HEADER_LAYOUTS_URL_PATH . '/minimal/assets/img/minimal-header.png',
			'label' => esc_html__( 'Minimal', 'plamen-core' )
		);

		return $header_layout_options;
	}

	add_filter( 'plamen_core_filter_header_layout_option', 'plamen_core_add_minimal_header_global_option' );
}


if ( ! function_exists( 'plamen_core_register_minimal_header_layout' ) ) {
	function plamen_core_register_minimal_header_layout( $header_layouts ) {
		$header_layout = array(
			'minimal' => 'MinimalHeader'
		);

		$header_layouts = array_merge( $header_layouts, $header_layout );

		return $header_layouts;
	}

	add_filter( 'plamen_core_filter_register_header_layouts', 'plamen_core_register_minimal_header_layout');
}