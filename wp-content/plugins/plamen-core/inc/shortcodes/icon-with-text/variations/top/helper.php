<?php

if ( ! function_exists( 'plamen_core_add_icon_with_text_variation_top' ) ) {
	function plamen_core_add_icon_with_text_variation_top( $variations ) {
		
		$variations['top'] = esc_html__( 'Top', 'plamen-core' );
		
		return $variations;
	}
	
	add_filter( 'plamen_core_filter_icon_with_text_layouts', 'plamen_core_add_icon_with_text_variation_top' );
}

if ( ! function_exists( 'plamen_core_add_icon_with_text_options_text_align' ) ) {
	function plamen_core_add_icon_with_text_options_text_align( $options, $default_layout ) {
		$icon_with_text_options   = array();
		
		$alignment_option = array(
			'field_type' => 'select',
			'name'       => 'content_alignment',
			'title'      => esc_html__( 'Content Alignment', 'plamen-core' ),
			'options'       => array(
				''       => esc_html__( 'Default', 'plamen-core' ),
				'left'   => esc_html__( 'Left', 'plamen-core' ),
				'center' => esc_html__( 'Center', 'plamen-core' ),
				'right'  => esc_html__( 'Right', 'plamen-core' )
			),
			'dependency' => array(
				'show' => array(
					'layout' => array(
						'values'        => 'top',
						'default_value' => $default_layout
					)
				)
			),
			'group'      => esc_html__( 'Content', 'plamen-core' )
		);
		
		$icon_with_text_options[] = $alignment_option;
		
		return array_merge( $options, $icon_with_text_options );
	}
	
	add_filter( 'plamen_core_filter_icon_with_text_extra_options', 'plamen_core_add_icon_with_text_options_text_align', 10, 2 );
}

if ( ! function_exists( 'plamen_core_add_icon_with_text_classes_alignment' ) ) {
	
	function plamen_core_add_icon_with_text_classes_alignment( $holder_classes, $atts ) {
		
		if( $atts['layout'] == 'top' ) {
			$holder_classes[] = ! empty( $atts['content_alignment'] ) ?  'qodef-alignment--' . $atts['content_alignment'] : 'qodef-alignment--center';
		}
		
		return $holder_classes;
	}
	
	add_filter( 'plamen_core_filter_icon_with_text_variation_classes', 'plamen_core_add_icon_with_text_classes_alignment', 10, 2 );
}