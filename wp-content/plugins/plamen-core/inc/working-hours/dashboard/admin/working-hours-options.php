<?php

if ( ! function_exists( 'plamen_core_add_working_hours_options' ) ) {
	/**
	 * Function that add general options for this module
	 */
	function plamen_core_add_working_hours_options() {
		$qode_framework = qode_framework_get_framework_root();

		$page = $qode_framework->add_options_page(
			array(
				'scope'       => PLAMEN_CORE_OPTIONS_NAME,
				'type'        => 'admin',
				'slug'        => 'working-hours',
				'icon'        => 'fa fa-book',
				'title'       => esc_html__( 'Working Hours', 'plamen-core' ),
				'description' => esc_html__( 'Global Working Hours Options', 'plamen-core' )
			)
		);

		if ( $page ) {
            $page->add_field_element(
                array(
                    'field_type'    => 'select',
                    'name'          => 'qodef_working_hours_layout',
                    'options'       => array(
                        'all-days'   => esc_html__( 'All Days', 'plamen-core' ),
                        'split-days' => esc_html__( 'Split Days', 'plamen-core' )
                    ),
                    'default_value' => 'split-days',
                    'title'         => esc_html__( 'Working Hours Layout', 'plamen-core' )
                )
            );


			$page->add_field_element(
				array(
					'field_type' => 'text',
					'name'       => 'qodef_working_hours_monday',
					'title'      => esc_html__( 'Working Hours For Monday', 'plamen-core' ),
                    'dependency'    => array(
                        'hide' => array(
                            'qodef_working_hours_layout' => array(
                                'values'        => 'split-days',
                                'default_value' => 'split-days'
                            )
                        )
                    )
				)
			);

			$page->add_field_element(
				array(
					'field_type' => 'text',
					'name'       => 'qodef_working_hours_tuesday',
					'title'      => esc_html__( 'Working Hours For Tuesday', 'plamen-core' ),
                    'dependency'    => array(
                        'hide' => array(
                            'qodef_working_hours_layout' => array(
                                'values'        => 'split-days',
                                'default_value' => 'split-days'
                            )
                        )
                    )
				)
			);

			$page->add_field_element(
				array(
					'field_type' => 'text',
					'name'       => 'qodef_working_hours_wednesday',
					'title'      => esc_html__( 'Working Hours For Wednesday', 'plamen-core' ),
                    'dependency'    => array(
                        'hide' => array(
                            'qodef_working_hours_layout' => array(
                                'values'        => 'split-days',
                                'default_value' => 'split-days'
                            )
                        )
                    )
				)
			);

			$page->add_field_element(
				array(
					'field_type' => 'text',
					'name'       => 'qodef_working_hours_thursday',
					'title'      => esc_html__( 'Working Hours For Thursday', 'plamen-core' ),
                    'dependency'    => array(
                        'hide' => array(
                            'qodef_working_hours_layout' => array(
                                'values'        => 'split-days',
                                'default_value' => 'split-days'
                            )
                        )
                    )
				)
			);

			$page->add_field_element(
				array(
					'field_type' => 'text',
					'name'       => 'qodef_working_hours_friday',
					'title'      => esc_html__( 'Working Hours For Friday', 'plamen-core' ),
                    'dependency'    => array(
                        'hide' => array(
                            'qodef_working_hours_layout' => array(
                                'values'        => 'split-days',
                                'default_value' => 'split-days'
                            )
                        )
                    )
				)
			);

            $page->add_field_element(
                array(
                    'field_type' => 'text',
                    'name'       => 'qodef_working_hours_workdays',
                    'title'      => esc_html__( 'Working Hours For Work days', 'plamen-core' ),
                    'dependency'    => array(
                        'show' => array(
                            'qodef_working_hours_layout' => array(
                                'values'        => 'split-days',
                                'default_value' => 'split-days'
                            )
                        )
                    )
                )
            );

			$page->add_field_element(
				array(
					'field_type' => 'text',
					'name'       => 'qodef_working_hours_saturday',
					'title'      => esc_html__( 'Working Hours For Saturday', 'plamen-core' )
				)
			);

			$page->add_field_element(
				array(
					'field_type' => 'text',
					'name'       => 'qodef_working_hours_sunday',
					'title'      => esc_html__( 'Working Hours For Sunday', 'plamen-core' )
				)
			);

			$page->add_field_element(
				array(
					'field_type' => 'checkbox',
					'name'       => 'qodef_working_hours_special_days',
					'title'      => esc_html__( 'Special Days', 'plamen-core' ),
					'options'    => array(
						'monday'    => esc_html__( 'Monday', 'plamen-core' ),
						'tuesday'   => esc_html__( 'Tuesday', 'plamen-core' ),
						'wednesday' => esc_html__( 'Wednesday', 'plamen-core' ),
						'thursday'  => esc_html__( 'Thursday', 'plamen-core' ),
						'friday'    => esc_html__( 'Friday', 'plamen-core' ),
                        'workdays'  => esc_html__( 'Work Days', 'plamen-core' ),
						'saturday'  => esc_html__( 'Saturday', 'plamen-core' ),
						'sunday'    => esc_html__( 'Sunday', 'plamen-core' ),
					)
				)
			);

			$page->add_field_element(
				array(
					'field_type' => 'text',
					'name'       => 'qodef_working_hours_special_text',
					'title'      => esc_html__( 'Featured Text For Special Days', 'plamen-core' )
				)
			);

			// Hook to include additional options after module options
			do_action( 'plamen_core_action_after_working_hours_options_map', $page );
		}
	}

	add_action( 'plamen_core_action_default_options_init', 'plamen_core_add_working_hours_options', plamen_core_get_admin_options_map_position( 'working-hours' ) );
}