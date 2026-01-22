<?php

if ( ! function_exists( 'plamen_core_add_fixed_header_option' ) ) {
	/**
	 * This function set header scrolling appearance value for global header option map
	 */
	function plamen_core_add_fixed_header_option( $options ) {
		$options['fixed'] = esc_html__( 'Fixed', 'plamen-core' );

		return $options;
	}

	add_filter( 'plamen_core_filter_header_scroll_appearance_option', 'plamen_core_add_fixed_header_option' );
}