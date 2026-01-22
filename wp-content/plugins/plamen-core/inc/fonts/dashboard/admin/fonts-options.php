<?php

if ( ! function_exists( 'plamen_core_add_fonts_options' ) ) {
	/**
	 * Function that add options for this module
	 */
	function plamen_core_add_fonts_options() {
		$qode_framework = qode_framework_get_framework_root();

		$page = $qode_framework->add_options_page(
			array(
				'scope'       => PLAMEN_CORE_OPTIONS_NAME,
				'type'        => 'admin',
				'slug'        => 'fonts',
				'title'       => esc_html__( 'Fonts', 'plamen-core' ),
				'description' => esc_html__( 'Global Fonts Options', 'plamen-core' ),
				'icon'        => 'fa fa-cog'
			)
		);

		if ( $page ) {
			$page->add_field_element(
				array(
					'field_type'    => 'yesno',
					'name'          => 'qodef_enable_google_fonts',
					'title'         => esc_html__( 'Enable Google Fonts', 'plamen-core' ),
					'default_value' => 'yes',
					'args'          => array(
						'custom_class' => 'qodef-enable-google-fonts'
					)
				)
			);

			$google_fonts_section = $page->add_section_element(
				array(
					'name'       => 'qodef_google_fonts_section',
					'title'      => esc_html__( 'Google Fonts Options', 'plamen-core' ),
					'dependency' => array(
						'show' => array(
							'qodef_enable_google_fonts' => array(
								'values'        => 'yes',
								'default_value' => ''
							)
						)
					)
				)
			);

			$page_repeater = $google_fonts_section->add_repeater_element(
				array(
					'name'        => 'qodef_choose_google_fonts',
					'title'       => esc_html__( 'Google Fonts to Include', 'plamen-core' ),
					'description' => esc_html__( 'Choose Google Fonts which you want to use on your website', 'plamen-core' ),
					'button_text' => esc_html__( 'Add New Google Font', 'plamen-core' )
				)
			);

			$page_repeater->add_field_element( array(
				'field_type'  => 'googlefont',
				'name'        => 'qodef_choose_google_font',
				'title'       => esc_html__( 'Google Font', 'plamen-core' ),
				'description' => esc_html__( 'Choose Google Font', 'plamen-core' ),
				'args'        => array(
					'include' => 'google-fonts'
				)
			) );

			$google_fonts_section->add_field_element(
				array(
					'field_type'  => 'checkbox',
					'name'        => 'qodef_google_fonts_weight',
					'title'       => esc_html__( 'Google Fonts Weight', 'plamen-core' ),
					'description' => esc_html__( 'Choose a default Google Fonts weights for your website. Impact on page load time', 'plamen-core' ),
					'options'     => array(
						'100'  => esc_html__( '100 Thin', 'plamen-core' ),
						'100i' => esc_html__( '100 Thin Italic', 'plamen-core' ),
						'200'  => esc_html__( '200 Extra-Light', 'plamen-core' ),
						'200i' => esc_html__( '200 Extra-Light Italic', 'plamen-core' ),
						'300'  => esc_html__( '300 Light', 'plamen-core' ),
						'300i' => esc_html__( '300 Light Italic', 'plamen-core' ),
						'400'  => esc_html__( '400 Regular', 'plamen-core' ),
						'400i' => esc_html__( '400 Regular Italic', 'plamen-core' ),
						'500'  => esc_html__( '500 Medium', 'plamen-core' ),
						'500i' => esc_html__( '500 Medium Italic', 'plamen-core' ),
						'600'  => esc_html__( '600 Semi-Bold', 'plamen-core' ),
						'600i' => esc_html__( '600 Semi-Bold Italic', 'plamen-core' ),
						'700'  => esc_html__( '700 Bold', 'plamen-core' ),
						'700i' => esc_html__( '700 Bold Italic', 'plamen-core' ),
						'800'  => esc_html__( '800 Extra-Bold', 'plamen-core' ),
						'800i' => esc_html__( '800 Extra-Bold Italic', 'plamen-core' ),
						'900'  => esc_html__( '900 Ultra-Bold', 'plamen-core' ),
						'900i' => esc_html__( '900 Ultra-Bold Italic', 'plamen-core' )
					)
				)
			);

			$google_fonts_section->add_field_element(
				array(
					'field_type'  => 'checkbox',
					'name'        => 'qodef_google_fonts_subset',
					'title'       => esc_html__( 'Google Fonts Style', 'plamen-core' ),
					'description' => esc_html__( 'Choose a default Google Fonts style for your website. Impact on page load time', 'plamen-core' ),
					'options'     => array(
						'latin'        => esc_html__( 'Latin', 'plamen-core' ),
						'latin-ext'    => esc_html__( 'Latin Extended', 'plamen-core' ),
						'cyrillic'     => esc_html__( 'Cyrillic', 'plamen-core' ),
						'cyrillic-ext' => esc_html__( 'Cyrillic Extended', 'plamen-core' ),
						'greek'        => esc_html__( 'Greek', 'plamen-core' ),
						'greek-ext'    => esc_html__( 'Greek Extended', 'plamen-core' ),
						'vietnamese'   => esc_html__( 'Vietnamese', 'plamen-core' )
					)
				)
			);

			$page_repeater = $page->add_repeater_element(
				array(
					'name'        => 'qodef_custom_fonts',
					'title'       => esc_html__( 'Custom Fonts', 'plamen-core' ),
					'description' => esc_html__( 'Add custom fonts', 'plamen-core' ),
					'button_text' => esc_html__( 'Add New Custom Font', 'plamen-core' )
				)
			);

			$page_repeater->add_field_element( array(
				'field_type' => 'file',
				'name'       => 'qodef_custom_font_ttf',
				'title'      => esc_html__( 'Custom Font TTF', 'plamen-core' ),
				'args'       => array(
					'allowed_type' => 'application/octet-stream'
				)
			) );

			$page_repeater->add_field_element( array(
				'field_type' => 'file',
				'name'       => 'qodef_custom_font_otf',
				'title'      => esc_html__( 'Custom Font OTF', 'plamen-core' ),
				'args'       => array(
					'allowed_type' => 'application/octet-stream'
				)
			) );

			$page_repeater->add_field_element( array(
				'field_type' => 'file',
				'name'       => 'qodef_custom_font_woff',
				'title'      => esc_html__( 'Custom Font WOFF', 'plamen-core' ),
				'args'       => array(
					'allowed_type' => 'application/octet-stream'
				)
			) );

			$page_repeater->add_field_element( array(
				'field_type' => 'file',
				'name'       => 'qodef_custom_font_woff2',
				'title'      => esc_html__( 'Custom Font WOFF2', 'plamen-core' ),
				'args'       => array(
					'allowed_type' => 'application/octet-stream'
				)
			) );

			$page_repeater->add_field_element( array(
				'field_type' => 'text',
				'name'       => 'qodef_custom_font_name',
				'title'      => esc_html__( 'Custom Font Name', 'plamen-core' ),
			) );

			// Hook to include additional options after module options
			do_action( 'plamen_core_action_after_page_fonts_options_map', $page );
		}
	}

	add_action( 'plamen_core_action_default_options_init', 'plamen_core_add_fonts_options', plamen_core_get_admin_options_map_position( 'fonts' ) );
}