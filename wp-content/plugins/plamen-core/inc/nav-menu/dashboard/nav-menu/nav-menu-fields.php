<?php

if ( ! function_exists( 'plamen_core_add_nav_menu_options' ) ) {
	function plamen_core_add_nav_menu_options() {
		$qode_framework = qode_framework_get_framework_root();
		
		$page = $qode_framework->add_options_page(
			array(
				'scope' => array( 'nav_menu_item' ),
				'type'  => 'nav-menu'
			)
		);
		
		if ( $page ) {
			$page->add_field_element(
				array(
					'field_type' => 'checkbox',
					'name'       => 'qodef-enable-mega-menu',
					'title'      => esc_html__( 'Enable Mega Menu', 'plamen-core' ),
					'options'    => array(
						'enable' => esc_html__( 'Enable', 'plamen-core' )
					),
					'args'       => array(
						'depth' => 0
					)
				)
			);
			
			$page->add_field_element(
				array(
					'field_type' => 'checkbox',
					'name'       => 'qodef-enable-anchor-link',
					'title'      => esc_html__( 'Enable Anchor Link', 'plamen-core' ),
					'options'    => array(
						'enable' => esc_html__( 'Enable', 'plamen-core' )
					)
				)
			);
			
			$page->add_field_element(
				array(
					'field_type' => 'select',
					'name'       => 'qodef-menu-item-appearance',
					'title'      => esc_html__( 'Menu Item Appearance', 'plamen-core' ),
					'options'    => array(
						'none'      => esc_html__( 'None', 'plamen-core' ),
						'hide-item' => esc_html__( 'Hide Item', 'plamen-core' ),
						'hide-link' => esc_html__( 'Hide Link', 'plamen-core' ),
						'hide-label' => esc_html__( 'Hide Label', 'plamen-core' )
					)
				)
			);
			
			$page->add_field_element(
				array(
					'field_type' => 'iconpack',
					'name'       => 'qodef-menu-item-icon-pack',
					'title'      => esc_html__( 'Icon Pack', 'plamen-core' ),
					'args'       => array(
						'width' => 'thin'
					)
				)
			);
			
			$custom_sidebars = plamen_core_get_custom_sidebars();
			if ( ! empty( $custom_sidebars ) && count( $custom_sidebars ) > 1 ) {
				$page->add_field_element(
					array(
						'field_type'  => 'select',
						'name'        => 'qodef-enable-mega-menu-widget',
						'title'       => esc_html__( 'Custom Sidebar', 'plamen-core' ),
						'description' => esc_html__( 'Choose a custom sidebar to display on wide menu', 'plamen-core' ),
						'options'     => $custom_sidebars,
						'args'        => array(
							'depth' => 1
						)
					)
				);
			}
		}
	}
	
	add_action( 'qode_framework_action_custom_nav_menu_fields', 'plamen_core_add_nav_menu_options' );
}