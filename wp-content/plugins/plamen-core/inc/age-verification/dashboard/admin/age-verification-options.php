<?php

if ( ! function_exists( 'plamen_core_add_age_verification_options' ) ) {
	/**
	 * Function that add general options for this module
	 */
	function plamen_core_add_age_verification_options() {
		$qode_framework = qode_framework_get_framework_root();
		
		$page = $qode_framework->add_options_page(
			array(
				'scope'       => PLAMEN_CORE_OPTIONS_NAME,
				'type'        => 'admin',
				'slug'        => 'age-verification',
				'icon'        => 'fa fa-envelope',
				'title'       => esc_html__( 'Age Verification', 'plamen-core' ),
				'description' => esc_html__( 'Age Verification Settings', 'plamen-core' )
			)
		);
		
		if ( $page ) {
			
			$page->add_field_element(
				array(
					'field_type'    => 'yesno',
					'name'          => 'qodef_enable_age_verification',
					'title'         => esc_html__( 'Enable Age Verification', 'plamen-core' ),
					'description'   => esc_html__( 'Use this option to enable/disable Age Verification', 'plamen-core' ),
					'default_value' => 'no'
				)
			);
			
			$age_verification_section = $page->add_section_element(
				array(
					'name'       => 'qodef_ae_verification_section',
					'title'      => esc_html__( 'Age Verification', 'plamen-core' ),
					'dependency' => array(
						'hide' => array(
							'qodef_enable_age_verification' => array(
								'values'        => 'no',
								'default_value' => ''
							)
						)
					)
				)
			);
			
			$age_verification_section->add_field_element(
				array(
					'field_type' => 'image',
					'name'       => 'qodef_age_verification_logo_image',
					'title'      => esc_html__( 'Logo Image', 'plamen-core' )
				)
			);
			
			$age_verification_section->add_field_element(
				array(
					'field_type'    => 'text',
					'name'          => 'qodef_age_verification_title',
					'title'         => esc_html__( 'Title', 'plamen-core' ),
					'description'   => esc_html__( 'Enter Age Verification window title', 'plamen-core' ),
					'default_value' => esc_html__( 'Are You Over 18?', 'plamen-core' )
				)
			);
			
			$age_verification_section->add_field_element(
				array(
					'field_type'    => 'text',
					'name'          => 'qodef_age_verification_subtitle',
					'title'         => esc_html__( 'Subtitle', 'plamen-core' ),
					'description'   => esc_html__( 'Enter Age Verification window subtitle', 'plamen-core' ),
					'default_value' => esc_html__( 'By entering this site you agree to our Privacy Policy', 'plamen-core' )
				)
			);
			
			$age_verification_section->add_field_element(
				array(
					'field_type'    => 'text',
					'name'          => 'qodef_age_verification_note',
					'title'         => esc_html__( 'Note', 'plamen-core' ),
					'description'   => esc_html__( 'Enter Age Verification window note', 'plamen-core' ),
					'default_value' => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Lectus arcu bibendum at varius. Ut porttitor leo a diam. Penatibus et magnis dis. Ut enim ad minim veniam.', 'plamen-core' )
				)
			);
			
			$age_verification_section->add_field_element(
				array(
					'field_type'    => 'text',
					'name'          => 'qodef_age_verification_link',
					'title'         => esc_html__( 'Link for Negative Action', 'plamen-core' ),
					'description'   => esc_html__( 'Enter Age Verification link for negative action', 'plamen-core' ),
					'default_value' => esc_html__( 'https://www.google.com', 'plamen-core' )
				)
			);
			
			$age_verification_section->add_field_element(
				array(
					'field_type' => 'image',
					'name'       => 'qodef_age_verification_background_image',
					'title'      => esc_html__( 'Background Image', 'plamen-core' )
				)
			);
			
			$age_verification_section->add_field_element(
				array(
					'field_type'  => 'select',
					'name'        => 'qodef_age_verification_prevent_behavior',
					'title'       => esc_html__( 'Age Verification Behavior', 'plamen-core' ),
					'description' => esc_html__( 'Choose how to manage modal', 'plamen-core' ),
					'options'     => array(
						'cookies' => esc_html__( 'by Cookies', 'plamen-core' )
					)
				)
			);
			
			// Hook to include additional options after module options
			do_action( 'plamen_core_action_after_age_verification_options_map', $age_verification_section );
		}
	}
	
	add_action( 'plamen_core_action_default_options_init', 'plamen_core_add_age_verification_options', plamen_core_get_admin_options_map_position( 'age-verification' ) );
}