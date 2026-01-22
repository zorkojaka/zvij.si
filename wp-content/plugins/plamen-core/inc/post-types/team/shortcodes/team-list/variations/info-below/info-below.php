<?php

if ( ! function_exists( 'plamen_core_add_team_list_variation_info_below' ) ) {
	function plamen_core_add_team_list_variation_info_below( $variations ) {
		
		$variations['info-below'] = esc_html__( 'Info Below', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_team_list_layouts', 'plamen_core_add_team_list_variation_info_below' );
}

if ( ! function_exists( 'plamen_core_add_team_list_options_info_below' ) ) {
	function plamen_core_add_team_list_options_info_below( $options ) {
		$info_below_options   = array();
		$margin_option        = array(
			'field_type' => 'text',
			'name'       => 'info_below_content_margin_top',
			'title'      => esc_html__( 'Content Top Margin', 'plamen-core' ),
			'dependency' => array(
				'show' => array(
					'layout' => array(
						'values'        => 'info-below',
						'default_value' => 'default'
					)
				)
			),
			'group'      => esc_html__( 'Layout', 'plamen-core' )
		);
		$info_below_options[] = $margin_option;
		
		return array_merge( $options, $info_below_options );
	}
	
	add_filter( 'plamen_core_filter_team_list_extra_options', 'plamen_core_add_team_list_options_info_below' );
}