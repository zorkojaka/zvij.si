<?php

if ( ! function_exists( 'plamen_core_include_yith_color_and_label_variations_plugin_is_installed' ) ) {
	/**
	 * Function that set case is installed element for framework functionality
	 *
	 * @param bool $installed
	 * @param string $plugin - plugin name
	 *
	 * @return bool
	 */
	function plamen_core_include_yith_color_and_label_variations_plugin_is_installed( $installed, $plugin ) {
		if ( $plugin === 'yith-color-and-label-variations' ) {
			return defined( 'YITH_WCCL' );
		}
		
		return $installed;
	}
	
	add_filter( 'qode_framework_filter_is_plugin_installed', 'plamen_core_include_yith_color_and_label_variations_plugin_is_installed', 10, 2 );
}