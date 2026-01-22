<?php

if ( ! function_exists( 'plamen_core_add_standard_header_options' ) ) {
	function plamen_core_add_standard_header_options( $page, $general_header_tab ) {
		
		$section = $general_header_tab->add_section_element(
			array(
				'name'        => 'qodef_standard_header_section',
				'title'       => esc_html__( 'Standard Header', 'plamen-core' ),
				'description' => esc_html__( 'Standard header settings', 'plamen-core' ),
				'dependency'  => array(
					'show'    => array(
						'qodef_header_layout' => array(
							'values' => 'standard',
							'default_value' => ''
						)
					)
				)
			)
		);

		$section->add_field_element(
			array(
				'field_type'  => 'yesno',
				'name'        => 'qodef_standard_header_in_grid',
				'title'       => esc_html__( 'Content in Grid', 'plamen-core' ),
				'description' => esc_html__( 'Set content to be in grid', 'plamen-core' ),
				'default_value' => 'no',
				'args'        => array(
					'suffix' => esc_html__( 'px', 'plamen-core' )
				)
			)
		);
		
		$section->add_field_element(
			array(
				'field_type'  => 'text',
				'name'        => 'qodef_standard_header_height',
				'title'       => esc_html__( 'Header Height', 'plamen-core' ),
				'description' => esc_html__( 'Enter header height', 'plamen-core' ),
				'args'        => array(
					'suffix' => esc_html__( 'px', 'plamen-core' )
				)
			)
		);
		
		$section->add_field_element(
			array(
				'field_type'  => 'text',
				'name'        => 'qodef_standard_header_side_padding',
				'title'       => esc_html__( 'Header Side Padding', 'plamen-core' ),
				'description' => esc_html__( 'Enter side padding for header area', 'plamen-core' ),
				'args'        => array(
					'suffix' => esc_html__( 'px or %', 'plamen-core' )
				)
			)
		);
		
		$section->add_field_element(
			array(
				'field_type'  => 'color',
				'name'        => 'qodef_standard_header_background_color',
				'title'       => esc_html__( 'Header Background Color', 'plamen-core' ),
				'description' => esc_html__( 'Enter header background color', 'plamen-core' )
			)
		);
		
		$section->add_field_element(
			array(
				'field_type'    => 'select',
				'name'          => 'qodef_standard_header_menu_position',
				'title'         => esc_html__( 'Menu position', 'plamen-core' ),
				'default_value' => 'center',
				'options'       => array(
					'left'   => esc_html__( 'Left', 'plamen-core' ),
					'center' => esc_html__( 'Center', 'plamen-core' ),
					'right'  => esc_html__( 'Right', 'plamen-core' ),
				)
			)
		);
	}
	
	add_action( 'plamen_core_action_after_header_options_map', 'plamen_core_add_standard_header_options', 10, 2 );
}