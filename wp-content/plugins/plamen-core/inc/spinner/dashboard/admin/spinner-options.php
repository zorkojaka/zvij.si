<?php

if ( ! function_exists( 'plamen_core_add_page_spinner_options' ) ) {
	/**
	 * Function that add general options for this module
	 */
	function plamen_core_add_page_spinner_options( $page ) {
		
		if ( $page ) {
			$page->add_field_element(
				array(
					'field_type'    => 'yesno',
					'name'          => 'qodef_enable_page_spinner',
					'title'         => esc_html__( 'Enable Page Spinner', 'plamen-core' ),
					'description'   => esc_html__( 'Enable Page Spinner Effect', 'plamen-core' ),
					'default_value' => 'no'
				)
			);
			
			$spinner_section = $page->add_section_element(
				array(
					'name'       => 'qodef_page_spinner_section',
					'title'      => esc_html__( 'Page Spinner Section', 'plamen-core' ),
					'dependency' => array(
						'show' => array(
							'qodef_enable_page_spinner' => array(
								'values'        => 'yes',
								'default_value' => 'no'
							)
						)
					)
				)
			);
			
			$spinner_section->add_field_element(
				array(
					'field_type'    => 'select',
					'name'          => 'qodef_page_spinner_type',
					'title'         => esc_html__( 'Select Page Spinner Type', 'plamen-core' ),
					'description'   => esc_html__( 'Choose a page spinner animation style', 'plamen-core' ),
					'options'       => apply_filters( 'plamen_core_filter_page_spinner_layout_options', array() ),
					'default_value' => apply_filters( 'plamen_core_filter_page_spinner_default_layout_option', '' ),
				)
			);
			
			$spinner_section->add_field_element(
				array(
					'field_type'  => 'color',
					'name'        => 'qodef_page_spinner_background_color',
					'title'       => esc_html__( 'Spinner Background Color', 'plamen-core' ),
					'description' => esc_html__( 'Choose the spinner background color', 'plamen-core' ),
                    'dependency'  => array(
                        'hide' => array(
                            'qodef_page_spinner_type' => array(
                                'values'        => 'plamen',
                                'default_value' => ''
                            )
                        )
                    )
				)
			);
			
			$spinner_section->add_field_element(
				array(
					'field_type'  => 'color',
					'name'        => 'qodef_page_spinner_color',
					'title'       => esc_html__( 'Spinner Color', 'plamen-core' ),
					'description' => esc_html__( 'Choose the spinner color', 'plamen-core' ),
                    'dependency'  => array(
                        'hide' => array(
                            'qodef_page_spinner_type' => array(
                                'values'        => 'plamen',
                                'default_value' => ''
                            )
                        )
                    )
				)
			);

            $spinner_section->add_field_element(
                array(
                    'field_type'  => 'image',
                    'name'        => 'qodef_page_spinner_image',
                    'title'       => esc_html__( 'Spinner Image', 'plamen-core' ),
                    'description' => esc_html__( 'Choose your preloader image. Please note that this image will be shown only when "Plamen" is set as Spinner Type', 'plamen-core' ),
                    'dependency'  => array(
                        'show' => array(
                            'qodef_page_spinner_type' => array(
                                'values'        => 'plamen',
                                'default_value' => ''
                            )
                        )
                    )
                )
            );

            $spinner_section->add_field_element(
                array(
                    'field_type'  => 'text',
                    'name'        => 'qodef_page_spinner_text',
                    'title'       => esc_html__( 'Spinner Text', 'plamen-core' ),
                    'description' => esc_html__( 'Choose your preloader text. Please note that this text will be shown only when "Plamen" is set as Spinner Type', 'plamen-core' ),
                    'dependency'  => array(
                        'show' => array(
                            'qodef_page_spinner_type' => array(
                                'values'        => 'plamen',
                                'default_value' => ''
                            )
                        )
                    )
                )
            );

            $spinner_section->add_field_element(
                array(
                    'field_type'  => 'yesno',
                    'name'        => 'qodef_page_spinner_smoke',
                    'title'       => esc_html__( 'Smoke Effect', 'plamen-core' ),
                    'dependency'  => array(
                        'show' => array(
                            'qodef_page_spinner_type' => array(
                                'values'        => 'plamen',
                                'default_value' => ''
                            )
                        )
                    ),
                    'default_value' => 'yes'
                )
            );
		}
	}
	
	add_action( 'plamen_core_action_after_general_options_map', 'plamen_core_add_page_spinner_options' );
}