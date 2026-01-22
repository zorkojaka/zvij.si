<?php

if ( ! function_exists( 'plamen_core_add_mobile_logo_options' ) ) {
	/**
	 * Function that add mobile header options for this module
	 */
	function plamen_core_add_mobile_logo_options( $page, $header_tab ) {

		if ( $page ) {
			
			$mobile_header_tab = $page->add_tab_element(
				array(
					'name'        => 'tab-mobile-header',
					'icon'        => 'fa fa-cog',
					'title'       => esc_html__( 'Mobile Header Logo Options', 'plamen-core' ),
					'description' => esc_html__( 'Set options for mobile headers', 'plamen-core' )
				)
			);
			
			$mobile_header_tab->add_field_element(
				array(
					'field_type'  => 'text',
					'name'        => 'qodef_mobile_logo_height',
					'title'       => esc_html__( 'Mobile Logo Height', 'plamen-core' ),
					'description' => esc_html__( 'Enter mobile logo height', 'plamen-core' ),
					'args'        => array(
						'suffix' => esc_html__( 'px', 'plamen-core' )
					)
				)
			);
			
			$mobile_header_tab->add_field_element(
				array(
					'field_type'  => 'image',
					'name'        => 'qodef_mobile_logo_main',
					'title'       => esc_html__( 'Mobile Logo - Main', 'plamen-core' ),
					'description' => esc_html__( 'Choose main mobile logo image', 'plamen-core' ),
					'default_value' => defined( 'PLAMEN_ASSETS_ROOT' ) ? PLAMEN_ASSETS_ROOT . '/img/logo.png' : '',
					'multiple'    => 'no'
				)
			);
			
			do_action( 'plamen_core_action_after_mobile_logo_options_map', $page );
		}
	}
	
	add_action( 'plamen_core_action_after_header_logo_options_map', 'plamen_core_add_mobile_logo_options', 10, 2 );
}