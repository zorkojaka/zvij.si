<?php

if ( ! function_exists( 'plamen_core_nav_menu_meta_options' ) ) {
	function plamen_core_nav_menu_meta_options( $page ) {
		
		if ( $page ) {
			
			$section = $page->add_section_element(
				array(
					'name'  => 'qodef_nav_menu_section',
					'title' => esc_html__( 'Main Menu', 'plamen-core' )
				)
			);
			
			$section->add_field_element(
				array(
					'field_type'  => 'text',
					'name'        => 'qodef_dropdown_top_position',
					'title'       => esc_html__( 'Dropdown Position', 'plamen-core' ),
					'description' => esc_html__( 'Enter value in percentage of entire header height', 'plamen-core' ),
				)
			);
		}
	}
	
	add_action( 'plamen_core_action_after_page_header_meta_map', 'plamen_core_nav_menu_meta_options' );
}