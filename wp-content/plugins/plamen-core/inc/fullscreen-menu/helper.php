<?php

if ( ! function_exists( 'plamen_core_register_fullscreen_menu' ) ) {
	function plamen_core_register_fullscreen_menu( $menus ) {
		$menus['fullscreen-menu-navigation'] = esc_html__( 'Fullscreen Navigation', 'plamen-core' );
		
		return $menus;
	}
	
	add_filter( 'plamen_filter_register_navigation_menus', 'plamen_core_register_fullscreen_menu' );
}

if ( ! function_exists( 'plamen_core_set_fullscreen_menu_typography_styles' ) ) {
	/**
	 * Function that generates module inline styles
	 *
	 * @param string $style
	 *
	 * @return string
	 */
	function plamen_core_set_fullscreen_menu_typography_styles( $style ) {
		$scope = PLAMEN_CORE_OPTIONS_NAME;
		
		$first_lvl_styles        = plamen_core_get_typography_styles( $scope, 'qodef_fullscreen_1st_lvl' );
		$first_lvl_hover_styles  = plamen_core_get_typography_hover_styles( $scope, 'qodef_fullscreen_1st_lvl' );
		$second_lvl_styles       = plamen_core_get_typography_styles( $scope, 'qodef_fullscreen_2nd_lvl' );
		$second_lvl_hover_styles = plamen_core_get_typography_hover_styles( $scope, 'qodef_fullscreen_2nd_lvl' );
		
		if ( ! empty( $first_lvl_styles ) ) {
			$style .= qode_framework_dynamic_style( '.qodef-fullscreen-menu > ul > li > a', $first_lvl_styles );
		}
		
		if ( ! empty( $first_lvl_hover_styles ) ) {
			$style .= qode_framework_dynamic_style( '.qodef-fullscreen-menu > ul > li > a:hover', $first_lvl_hover_styles );
		}
		
		$first_lvl_active_color = plamen_core_get_option_value( 'admin', 'qodef_fullscreen_1st_lvl_active_color' );
		
		if ( ! empty( $first_lvl_active_color ) ) {
			$first_lvl_active_styles = array(
				'color' => $first_lvl_active_color
			);
			
			$style .= qode_framework_dynamic_style( array(
				'.qodef-fullscreen-menu > ul >li.current-menu-ancestor > a',
				'.qodef-fullscreen-menu > ul >li.current-menu-item > a'
			), $first_lvl_active_styles );
		}
		
		if ( ! empty( $second_lvl_styles ) ) {
			$style .= qode_framework_dynamic_style( '.qodef-fullscreen-menu .qodef-drop-down-second-inner ul li > a', $second_lvl_styles );
		}
		
		if ( ! empty( $second_lvl_hover_styles ) ) {
			$style .= qode_framework_dynamic_style( '.qodef-fullscreen-menu .qodef-drop-down-second-inner ul li > a:hover', $second_lvl_hover_styles );
		}
		
		$second_lvl_active_color = plamen_core_get_option_value( 'admin', 'qodef_fullscreen_2nd_lvl_active_color' );
		
		if ( ! empty( $second_lvl_active_color ) ) {
			$second_lvl_active_styles = array(
				'color' => $second_lvl_active_color
			);
			
			$style .= qode_framework_dynamic_style( array(
				'.qodef-fullscreen-menu .qodef-drop-down-second ul li.current-menu-ancestor > a',
				'.qodef-fullscreen-menu .qodef-drop-down-second ul li.current-menu-item > a'
			), $second_lvl_active_styles );
		}
		
		return $style;
	}
	
	add_filter( 'plamen_filter_add_inline_style', 'plamen_core_set_fullscreen_menu_typography_styles' );
}