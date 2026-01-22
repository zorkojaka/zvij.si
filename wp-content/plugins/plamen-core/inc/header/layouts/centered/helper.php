<?php
if ( ! function_exists( 'plamen_core_add_centered_header_global_option' ) ) {
	/**
	 * This function set header type value for global header option map
	 */

	function plamen_core_add_centered_header_global_option( $header_layout_options ) {
		$header_layout_options['centered'] = array(
			'image' => PLAMEN_CORE_HEADER_LAYOUTS_URL_PATH . '/centered/assets/img/centered-header.png',
			'label' => esc_html__( 'Centered', 'plamen-core' )
		);

		return $header_layout_options;
	}

	add_filter( 'plamen_core_filter_header_layout_option', 'plamen_core_add_centered_header_global_option' );
}

if ( ! function_exists( 'plamen_core_set_centered_header_as_default_global_option' ) ) {
    /**
     * This function set header type as default option value for global header option map
     */
    function plamen_core_set_centered_header_as_default_global_option( $default_value ) {
        return 'centered';
    }

    add_filter( 'plamen_core_filter_header_layout_default_option_value', 'plamen_core_set_centered_header_as_default_global_option' );
}

if ( ! function_exists( 'plamen_core_register_centered_header_layout' ) ) {
	function plamen_core_register_centered_header_layout( $header_layouts ) {
		$header_layout = array(
			'centered' => 'CenteredHeader'
		);

		$header_layouts = array_merge( $header_layouts, $header_layout );

		return $header_layouts;
	}

	add_filter( 'plamen_core_filter_register_header_layouts', 'plamen_core_register_centered_header_layout');
}