<?php

if ( ! function_exists( 'plamen_core_add_logo_options' ) ) {
	function plamen_core_add_logo_options() {
		$qode_framework = qode_framework_get_framework_root();

		$page = $qode_framework->add_options_page(
			array(
				'scope'       => PLAMEN_CORE_OPTIONS_NAME,
				'type'        => 'admin',
				'slug'        => 'logo',
				'icon'        => 'fa fa-cog',
				'title'       => esc_html__( 'Logo', 'plamen-core' ),
				'description' => esc_html__( 'Global Logo Options', 'plamen-core' ),
				'layout'      => 'tabbed'
			)
		);

		if ( $page ) {

			$header_tab = $page->add_tab_element(
				array(
					'name'        => 'tab-header',
					'icon'        => 'fa fa-cog',
					'title'       => esc_html__( 'Header Logo Options', 'plamen-core' ),
					'description' => esc_html__( 'Set options for initial headers', 'plamen-core' )
				)
			);

			$header_tab->add_field_element(
                array(
                    'field_type'  => 'text',
                    'name'        => 'qodef_logo_height',
                    'title'       => esc_html__( 'Logo Height', 'plamen-core' ),
                    'description' => esc_html__( 'Enter logo height', 'plamen-core' ),
                    'args'        => array(
                        'suffix' => esc_html__( 'px', 'plamen-core' )
                    )
                )
            );

			$header_tab->add_field_element(
				array(
					'field_type'    => 'image',
					'name'          => 'qodef_logo_main',
					'title'         => esc_html__( 'Logo - Main', 'plamen-core' ),
					'description'   => esc_html__( 'Choose main logo image', 'plamen-core' ),
					'default_value' => defined( 'PLAMEN_ASSETS_ROOT' ) ? PLAMEN_ASSETS_ROOT . '/img/logo.png' : '',
					'multiple'      => 'no'
				)
			);

			$header_tab->add_field_element(
				array(
					'field_type'  => 'image',
					'name'        => 'qodef_logo_dark',
					'title'       => esc_html__( 'Logo - Dark', 'plamen-core' ),
					'description' => esc_html__( 'Choose dark logo image', 'plamen-core' ),
					'multiple'    => 'no'
				)
			);

			$header_tab->add_field_element(
				array(
					'field_type'  => 'image',
					'name'        => 'qodef_logo_light',
					'title'       => esc_html__( 'Logo - Light', 'plamen-core' ),
					'description' => esc_html__( 'Choose light logo image', 'plamen-core' ),
					'multiple'    => 'no'
				)
			);

			// Hook to include additional options after module options
			do_action( 'plamen_core_action_after_header_logo_options_map', $page, $header_tab );
		}
	}

	add_action( 'plamen_core_action_default_options_init', 'plamen_core_add_logo_options', plamen_core_get_admin_options_map_position( 'logo' ) );
}