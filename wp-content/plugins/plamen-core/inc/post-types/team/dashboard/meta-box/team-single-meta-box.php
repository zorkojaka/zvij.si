<?php

if ( ! function_exists( 'plamen_core_add_team_single_meta_box' ) ) {
	/**
	 * Function that add general options for this module
	 */
	function plamen_core_add_team_single_meta_box() {
		$qode_framework = qode_framework_get_framework_root();
		$has_single     = plamen_core_team_has_single();
		
		$page = $qode_framework->add_options_page(
			array(
				'scope' => array( 'team' ),
				'type'  => 'meta',
				'slug'  => 'team',
				'title' => esc_html__( 'Team Single', 'plamen-core' )
			)
		);
		
		if ( $page ) {
			$section = $page->add_section_element(
				array(
					'name'        => 'qodef_team_general_section',
					'title'       => esc_html__( 'General Settings', 'plamen-core' ),
					'description' => esc_html__( 'General information about team member.', 'plamen-core' )
				)
			);
			
			if ( $has_single ) {
				$section->add_field_element( array(
					'field_type'  => 'select',
					'name'        => 'qodef_team_single_layout',
					'title'       => esc_html__( 'Single Layout', 'plamen-core' ),
					'description' => esc_html__( 'Choose default layout for team single', 'plamen-core' ),
					'options'     => array(
						'' => esc_html__( 'Default', 'plamen-core' )
					)
				) );
			}
			
			$section->add_field_element(
				array(
					'field_type'  => 'text',
					'name'        => 'qodef_team_member_role',
					'title'       => esc_html__( 'Role', 'plamen-core' ),
					'description' => esc_html__( 'Enter team member role', 'plamen-core' ),
				)
			);
			
			$social_icons_repeater = $section->add_repeater_element(
				array(
					'name'        => 'qodef_team_member_social_icons',
					'title'       => esc_html__( 'Social Networks', 'plamen-core' ),
					'description' => esc_html__( 'Populate team member social networks info', 'plamen-core' ),
					'button_text' => esc_html__( 'Add New Network', 'plamen-core' )
				)
			);

            $social_icons_repeater->add_field_element(
                array(
                    'field_type' => 'select',
                    'name'       => 'qodef_team_member_icon_source',
                    'title'      => esc_html__( 'Icon Source', 'plamen-core' ),
                    'options'    => array(
                        'textual'  => esc_html__( 'Textual', 'plamen-core' ),
                        'iconpack' => esc_html__( 'Icon Pack', 'plamen-core' )
                    )
                )
            );
			
			$social_icons_repeater->add_field_element(
				array(
					'field_type' => 'iconpack',
					'name'       => 'qodef_team_member_icon',
					'title'      => esc_html__( 'Icon', 'plamen-core' ),
                    'dependency' => array(
                        'show' => array(
                            'qodef_team_member_icon_source' => array(
                                'values' => 'iconpack',
                                'default_value' => 'textual'
                            )
                        )
                    ),
				)
			);

            $social_icons_repeater->add_field_element(
                array(
                    'field_type' => 'text',
                    'name'       => 'qodef_team_member_icon_text',
                    'title'      => esc_html__( 'Icon Text', 'plamen-core' ),
                    'dependency' => array(
                        'show' => array(
                            'qodef_team_member_icon_source' => array(
                                'values' => 'textual',
                                'default_value' => 'textual'
                            )
                        )
                    ),
                )
            );
			
			$social_icons_repeater->add_field_element(
				array(
					'field_type' => 'text',
					'name'       => 'qodef_team_member_icon_link',
					'title'      => esc_html__( 'Icon Link', 'plamen-core' )
				)
			);
			
			$social_icons_repeater->add_field_element(
				array(
					'field_type' => 'select',
					'name'       => 'qodef_team_member_icon_target',
					'title'      => esc_html__( 'Icon Target', 'plamen-core' ),
					'options'    => plamen_core_get_select_type_options_pool( 'link_target' )
				)
			);
			
			if ( $has_single ) {
				$section->add_field_element( array(
					'field_type'  => 'date',
					'name'        => 'qodef_team_member_birth_date',
					'title'       => esc_html__( 'Birth Date', 'plamen-core' ),
					'description' => esc_html__( 'Enter team member birth date', 'plamen-core' ),
				) );
				
				$section->add_field_element( array(
					'field_type'  => 'text',
					'name'        => 'qodef_team_member_email',
					'title'       => esc_html__( 'E-mail', 'plamen-core' ),
					'description' => esc_html__( 'Enter team member e-mail address', 'plamen-core' ),
				) );
				
				$section->add_field_element( array(
					'field_type'  => 'text',
					'name'        => 'qodef_team_member_address',
					'title'       => esc_html__( 'Address', 'plamen-core' ),
					'description' => esc_html__( 'Enter team member address', 'plamen-core' ),
				) );
				
				$section->add_field_element( array(
					'field_type'  => 'text',
					'name'        => 'qodef_team_member_education',
					'title'       => esc_html__( 'Education', 'plamen-core' ),
					'description' => esc_html__( 'Enter team member education', 'plamen-core' ),
				) );
				
				$section->add_field_element( array(
					'field_type'  => 'file',
					'name'        => 'qodef_team_member_resume',
					'title'       => esc_html__( 'Resume', 'plamen-core' ),
					'description' => esc_html__( 'Upload team member resume', 'plamen-core' ),
					'args'        => array(
						'allowed_type' => '[application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document]'
					)
				) );
			}
			
			// Hook to include additional options after module options
			do_action( 'plamen_core_action_after_team_meta_box_map', $page, $has_single );
		}
	}
	
	add_action( 'plamen_core_action_default_meta_boxes_init', 'plamen_core_add_team_single_meta_box' );
}