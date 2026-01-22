<?php

if ( ! function_exists( 'plamen_core_register_standard_with_breadcrumbs_title_layout' ) ) {
	function plamen_core_register_standard_with_breadcrumbs_title_layout( $layouts ) {
		$layouts['standard-with-breadcrumbs'] = 'PlamenCoreStandardWithBreadcrumbsTitle';

		return $layouts;
	}

	add_filter( 'plamen_core_filter_register_title_layouts', 'plamen_core_register_standard_with_breadcrumbs_title_layout' );
}

if ( ! function_exists( 'plamen_core_add_standard_with_breadcrumbs_title_layout_option' ) ) {
	/**
	 * Function that set new value into title layout options map
	 *
	 * @param array $layouts  - module layouts
	 *
	 * @return array
	 */
	function plamen_core_add_standard_with_breadcrumbs_title_layout_option( $layouts ) {
		$layouts['standard-with-breadcrumbs'] = esc_html__( 'Standard with breadcrumbs', 'plamen-core' );

		return $layouts;
	}

	add_filter( 'plamen_core_filter_title_layout_options', 'plamen_core_add_standard_with_breadcrumbs_title_layout_option' );
}

