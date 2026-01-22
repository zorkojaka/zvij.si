<?php

if ( ! function_exists( 'plamen_core_add_centered_header_options' ) ) {
	function plamen_core_add_centered_header_options( $page, $general_header_tab ) {
		
		$section = $general_header_tab->add_section_element(
			array(
				'name'       => 'qodef_centered_header_section',
				'title'      => esc_html__( 'Centered Header', 'plamen-core' ),
				'dependency' => array(
					'show' => array(
						'qodef_header_layout' => array(
							'values' => 'centered',
							'default_value' => ''
						)
					)
				)
			)
		);
		
		$section->add_field_element(
			array(
				'field_type'  => 'text',
				'name'        => 'qodef_centered_header_height',
				'title'       => esc_html__( 'Header Height', 'plamen-core' ),
				'description' => esc_html__( 'Enter header height', 'plamen-core' ),
				'args'        => array(
					'suffix' => esc_html__( 'px', 'plamen-core' )
				)
			)
		);
		
		$section->add_field_element(
			array(
				'field_type'  => 'color',
				'name'        => 'qodef_centered_header_background_color',
				'title'       => esc_html__( 'Header Background Color', 'plamen-core' ),
				'description' => esc_html__( 'Enter header background color', 'plamen-core' ),
				'args'        => array(
					'suffix' => esc_html__( 'px', 'plamen-core' )
				)
			)
		);
	}
	
	add_action( 'plamen_core_action_after_header_options_map', 'plamen_core_add_centered_header_options', 10, 2 );
}