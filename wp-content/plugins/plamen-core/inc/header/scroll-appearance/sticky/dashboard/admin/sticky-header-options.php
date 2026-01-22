<?php

if ( ! function_exists( 'plamen_core_add_sticky_header_options' ) ) {
	function plamen_core_add_sticky_header_options( $page, $section ) {
		
		$section->add_field_element(
			array(
				'field_type'  => 'text',
				'name'        => 'qodef_sticky_header_scroll_amount',
				'title'       => esc_html__( 'Sticky Scroll Amount', 'plamen-core' ),
				'description' => esc_html__( 'Enter scroll amount for sticky header to appear', 'plamen-core' ),
				'args'        => array(
					'suffix' => esc_html__( 'px', 'plamen-core' )
				),
				'dependency'  => array(
					'show' => array(
						'qodef_header_scroll_appearance' => array(
							'values'        => 'sticky',
							'default_value' => ''
						)
					)
				)
			)
		);
		
		$section->add_field_element(
			array(
				'field_type'  => 'text',
				'name'        => 'qodef_sticky_header_side_padding',
				'title'       => esc_html__( 'Sticky Header Side Padding', 'plamen-core' ),
				'description' => esc_html__( 'Enter side padding for sticky header area', 'plamen-core' ),
				'args'        => array(
					'suffix' => esc_html__( 'px or %', 'plamen-core' )
				),
				'dependency'  => array(
					'show' => array(
						'qodef_header_scroll_appearance' => array(
							'values'        => 'sticky',
							'default_value' => ''
						)
					)
				)
			)
		);
		
		$section->add_field_element(
			array(
				'field_type'  => 'color',
				'name'        => 'qodef_sticky_header_background_color',
				'title'       => esc_html__( 'Sticky Header Background Color', 'plamen-core' ),
				'description' => esc_html__( 'Enter sticky header background color', 'plamen-core' ),
				'dependency'  => array(
					'show' => array(
						'qodef_header_scroll_appearance' => array(
							'values'        => 'sticky',
							'default_value' => ''
						)
					)
				)
			)
		);
	}
	
	add_action( 'plamen_core_action_after_header_scroll_appearance_options_map', 'plamen_core_add_sticky_header_options', 10, 2 );
}

if ( ! function_exists( 'plamen_core_add_sticky_header_logo_options' ) ) {
	function plamen_core_add_sticky_header_logo_options( $page, $header_tab ) {
		
		if ( $header_tab ) {
            $header_tab->add_field_element(
                array(
                    'field_type'  => 'text',
                    'name'        => 'qodef_sticky_logo_height',
                    'title'       => esc_html__( 'Logo - Sticky Height', 'plamen-core' ),
                    'description' => esc_html__( 'Enter Logo - Sticky height', 'plamen-core' ),
                    'args'        => array(
                        'suffix' => esc_html__( 'px', 'plamen-core' )
                    )
                )
            );

			$header_tab->add_field_element(
				array(
					'field_type'  => 'image',
					'name'        => 'qodef_logo_sticky',
					'title'       => esc_html__( 'Logo - Sticky', 'plamen-core' ),
					'description' => esc_html__( 'Choose sticky logo image', 'plamen-core' ),
					'multiple'    => 'no'
				)
			);
		}
	}
	
	add_action( 'plamen_core_action_after_header_logo_options_map', 'plamen_core_add_sticky_header_logo_options', 10, 2 );
}