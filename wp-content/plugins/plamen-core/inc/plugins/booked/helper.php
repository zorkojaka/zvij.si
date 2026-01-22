<?php

if ( ! function_exists( 'plamen_core_include_booked_plugin_is_installed' ) ) {
	/**
	 * Function that set case is installed element for framework functionality
	 *
	 * @param bool $installed
	 * @param string $plugin - plugin name
	 *
	 * @return bool
	 */
	function plamen_core_include_booked_plugin_is_installed( $installed, $plugin ) {
		if ( $plugin === 'booked' ) {
			return defined( 'BOOKED_VERSION' );
		}
		
		return $installed;
	}
	
	add_filter( 'qode_framework_filter_is_plugin_installed', 'plamen_core_include_booked_plugin_is_installed', 10, 2 );
}