<?php

if ( ! function_exists( 'plamen_core_add_general_page_meta_box' ) ) {
	/**
	 * Function that add general options for this module
	 */
	function plamen_core_add_general_page_meta_box( $page ) {

		$general_tab = $page->add_tab_element(
			array(
				'name'        => 'tab-page',
				'icon'        => 'fa fa-cog',
				'title'       => esc_html__( 'Page Settings', 'plamen-core' ),
				'description' => esc_html__( 'General page layout settings', 'plamen-core' )
			)
		);

		$general_tab->add_field_element(
			array(
				'field_type'  => 'color',
				'name'        => 'qodef_page_background_color',
				'title'       => esc_html__( 'Page Background Color', 'plamen-core' ),
				'description' => esc_html__( 'Set background color', 'plamen-core' )
			)
		);

		$general_tab->add_field_element(
			array(
				'field_type'  => 'image',
				'name'        => 'qodef_page_background_image',
				'title'       => esc_html__( 'Page Background Image', 'plamen-core' ),
				'description' => esc_html__( 'Set background image', 'plamen-core' )
			)
		);

		$general_tab->add_field_element(
			array(
				'field_type'  => 'select',
				'name'        => 'qodef_page_background_repeat',
				'title'       => esc_html__( 'Page Background Image Repeat', 'plamen-core' ),
				'description' => esc_html__( 'Set background image repeat', 'plamen-core' ),
				'options'     => array(
					''          => esc_html__( 'Default', 'plamen-core' ),
					'no-repeat' => esc_html__( 'No Repeat', 'plamen-core' ),
					'repeat'    => esc_html__( 'Repeat', 'plamen-core' ),
					'repeat-x'  => esc_html__( 'Repeat-x', 'plamen-core' ),
					'repeat-y'  => esc_html__( 'Repeat-y', 'plamen-core' )
				)
			)
		);

		$general_tab->add_field_element(
			array(
				'field_type'  => 'select',
				'name'        => 'qodef_page_background_size',
				'title'       => esc_html__( 'Page Background Image Size', 'plamen-core' ),
				'description' => esc_html__( 'Set background image size', 'plamen-core' ),
				'options'     => array(
					''        => esc_html__( 'Default', 'plamen-core' ),
					'contain' => esc_html__( 'Contain', 'plamen-core' ),
					'cover'   => esc_html__( 'Cover', 'plamen-core' )
				)
			)
		);

		$general_tab->add_field_element(
			array(
				'field_type'  => 'select',
				'name'        => 'qodef_page_background_attachment',
				'title'       => esc_html__( 'Page Background Image Attachment', 'plamen-core' ),
				'description' => esc_html__( 'Set background image attachment', 'plamen-core' ),
				'options'     => array(
					''       => esc_html__( 'Default', 'plamen-core' ),
					'fixed'  => esc_html__( 'Fixed', 'plamen-core' ),
					'scroll' => esc_html__( 'Scroll', 'plamen-core' )
				)
			)
		);

		$general_tab->add_field_element(
			array(
				'field_type'  => 'text',
				'name'        => 'qodef_page_content_padding',
				'title'       => esc_html__( 'Page Content Padding', 'plamen-core' ),
				'description' => esc_html__( 'Set padding that will be applied for page content in format: top right bottom left (e.g. 10px 5px 10px 5px)', 'plamen-core' )
			)
		);

		$general_tab->add_field_element(
			array(
				'field_type'  => 'text',
				'name'        => 'qodef_page_content_padding_mobile',
				'title'       => esc_html__( 'Page Content Padding Mobile', 'plamen-core' ),
				'description' => esc_html__( 'Set padding that will be applied for page content on mobile screens (1024px and below) in format: top right bottom left (e.g. 10px 5px 10px 5px)', 'plamen-core' )
			)
		);

		$general_tab->add_field_element(
			array(
				'field_type'    => 'select',
				'name'          => 'qodef_boxed',
				'title'         => esc_html__( 'Boxed Layout', 'plamen-core' ),
				'description'   => esc_html__( 'Set boxed layout', 'plamen-core' ),
				'default_value' => '',
				'options'       => plamen_core_get_select_type_options_pool( 'yes_no' )
			)
		);

		$boxed_section = $general_tab->add_section_element(
			array(
				'name'       => 'qodef_boxed_section',
				'title'      => esc_html__( 'Boxed Layout Section', 'plamen-core' ),
				'dependency' => array(
					'hide' => array(
						'qodef_boxed' => array(
							'values'        => 'no',
							'default_value' => ''
						)
					)
				)
			)
		);

		$boxed_section->add_field_element(
			array(
				'field_type'  => 'color',
				'name'        => 'qodef_boxed_background_color',
				'title'       => esc_html__( 'Boxed Background Color', 'plamen-core' ),
				'description' => esc_html__( 'Set boxed background color', 'plamen-core' )
			)
		);

        $boxed_section->add_field_element(
            array(
                'field_type'  => 'image',
                'name'        => 'qodef_boxed_background_pattern',
                'title'       => esc_html__( 'Boxed Background Pattern', 'plamen-core' ),
                'description' => esc_html__( 'Set boxed background pattern', 'plamen-core' )
            )
        );

        $boxed_section->add_field_element(
            array(
                'field_type'  => 'select',
                'name'        => 'qodef_boxed_background_pattern_behavior',
                'title'       => esc_html__( 'Boxed Background Pattern Behavior', 'plamen-core' ),
                'description' => esc_html__( 'Set boxed background pattern behavior', 'plamen-core' ),
                'options'     => array(
                    ''       => esc_html__( 'Default', 'plamen-core' ),
                    'fixed'  => esc_html__( 'Fixed', 'plamen-core' ),
                    'scroll' => esc_html__( 'Scroll', 'plamen-core' )
                ),
            )
        );

		$general_tab->add_field_element(
			array(
				'field_type'    => 'select',
				'name'          => 'qodef_passepartout',
				'title'         => esc_html__( 'Passepartout', 'plamen-core' ),
				'description'   => esc_html__( 'Enabling this option will display a passepartout around website content', 'plamen-core' ),
				'default_value' => '',
				'options'       => plamen_core_get_select_type_options_pool( 'yes_no' )
			)
		);

		$passepartout_section = $general_tab->add_section_element(
			array(
				'name'       => 'qodef_passepartout_section',
				'dependency' => array(
					'hide' => array(
						'qodef_passepartout' => array(
							'values'        => 'no',
							'default_value' => ''
						)
					)
				)
			)
		);

		$passepartout_section->add_field_element(
			array(
				'field_type'  => 'color',
				'name'        => 'qodef_passepartout_color',
				'title'       => esc_html__( 'Passepartout Color', 'plamen-core' ),
				'description' => esc_html__( 'Choose background color for passepartout', 'plamen-core' )
			)
		);

		$passepartout_section->add_field_element(
			array(
				'field_type'  => 'image',
				'name'        => 'qodef_passepartout_image',
				'title'       => esc_html__( 'Passepartout Background Image', 'plamen-core' ),
				'description' => esc_html__( 'Set background image for passepartout', 'plamen-core' )
			)
		);

		$passepartout_section->add_field_element(
			array(
				'field_type'  => 'text',
				'name'        => 'qodef_passepartout_size',
				'title'       => esc_html__( 'Passepartout Size', 'plamen-core' ),
				'description' => esc_html__( 'Enter size amount for passepartout', 'plamen-core' ),
				'args'        => array(
					'suffix' => esc_html__( 'px or %', 'plamen-core' )
				)
			)
		);

		$passepartout_section->add_field_element(
			array(
				'field_type'  => 'text',
				'name'        => 'qodef_passepartout_size_responsive',
				'title'       => esc_html__( 'Passepartout Responsive Size', 'plamen-core' ),
				'description' => esc_html__( 'Enter size amount for passepartout for smaller screens (1024px and below)', 'plamen-core' ),
				'args'        => array(
					'suffix' => esc_html__( 'px or %', 'plamen-core' )
				)
			)
		);

		$general_tab->add_field_element(
			array(
				'field_type'  => 'select',
				'name'        => 'qodef_content_width',
				'title'       => esc_html__( 'Initial Width of Content', 'plamen-core' ),
				'description' => esc_html__( 'Choose the initial width of content which is in grid (applies to pages set to "Default Template" and rows set to "In Grid")', 'plamen-core' ),
				'options'     => plamen_core_get_select_type_options_pool( 'content_width' )
			)
		);

		$general_tab->add_field_element( array(
			'field_type'    => 'yesno',
			'default_value' => 'no',
			'name'          => 'qodef_content_behind_header',
			'title'         => esc_html__( 'Always put content behind header', 'plamen-core' ),
			'description'   => esc_html__( 'Enabling this option will put page content behind page header', 'plamen-core' ),
		) );

		// Hook to include additional options after module options
		do_action( 'plamen_core_action_after_general_page_meta_box_map', $general_tab );
	}

	add_action( 'plamen_core_action_after_general_meta_box_map', 'plamen_core_add_general_page_meta_box', 9 );
}

if ( ! function_exists( 'plamen_core_add_general_page_meta_box_callback' ) ) {
	/**
	 * Function that set current meta box callback as general callback functions
	 *
	 * @param array $callbacks
	 *
	 * @return array
	 */
	function plamen_core_add_general_page_meta_box_callback( $callbacks ) {
		$callbacks['page'] = 'plamen_core_add_general_page_meta_box';
		
		return $callbacks;
	}
	
	add_filter( 'plamen_core_filter_general_meta_box_callbacks', 'plamen_core_add_general_page_meta_box_callback' );
}