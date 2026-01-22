<?php

if ( ! function_exists( 'plamen_core_add_page_social_share_options' ) ) {
	/**
	 * Function that add general options for this module
	 */
	function plamen_core_add_page_social_share_options() {
		$qode_framework = qode_framework_get_framework_root();

		$page = $qode_framework->add_options_page(
			array(
				'scope'       => PLAMEN_CORE_OPTIONS_NAME,
				'type'        => 'admin',
				'slug'        => 'social-share',
				'icon'        => 'fa fa-book',
				'title'       => esc_html__( 'Social Share', 'plamen-core' ),
				'description' => esc_html__( 'Global Social Share Options', 'plamen-core' )
			)
		);

		if ( $page ) {
			$social_networks = plamen_core_social_networks_list();

			foreach ( $social_networks as $network => $params ) {
				$page->add_field_element(
					array(
						'field_type'    => 'yesno',
						'name'          => 'qodef_enable_share_' . $network,
						'title'         => sprintf( esc_html__( 'Enable %s Share', 'plamen-core' ), $params['label'] ),
						'default_value' => 'yes'
					)
				);

				if ( $network == 'twitter' ) {
					$page->add_field_element(
						array(
							'field_type'    => 'text',
							'name'          => 'qodef_twitter_via',
							'title'         => esc_html__( 'Twitter Via Text', 'plamen-core' ),
							'default_value' => esc_html__( '@QodeInteractive', 'plamen-core' ),
							'dependency'    => array(
								'show' => array(
									'qodef_enable_share_twitter' => array(
										'values'        => 'yes',
										'default_value' => 'yes'
									)
								)
							)
						)
					);
				}
			}

			// Hook to include additional options after module options
			do_action( 'plamen_core_action_after_social_share_options_map', $page );
		}
	}

	add_action( 'plamen_core_action_default_options_init', 'plamen_core_add_page_social_share_options', plamen_core_get_admin_options_map_position( 'social-share' ) );
}