<?php

if ( ! function_exists( 'plamen_core_add_page_spinner_meta_box' ) ) {
	/**
	 * Function that add general options for this module
	 */
	function plamen_core_add_page_spinner_meta_box( $page ) {
		
		if ( $page ) {
			$page->add_field_element(
				array(
					'field_type'  => 'select',
					'name'        => 'qodef_enable_page_spinner',
					'title'       => esc_html__( 'Enable Page Spinner', 'plamen-core' ),
					'description' => esc_html__( 'Enable Page Spinner Effect', 'plamen-core' ),
					'options'     => plamen_core_get_select_type_options_pool( 'yes_no' )
				)
			);
		}
	}
	
	add_action( 'plamen_core_action_after_general_page_meta_box_map', 'plamen_core_add_page_spinner_meta_box', 9 );
}