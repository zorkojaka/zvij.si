<?php

if ( ! function_exists( 'plamen_core_dependency_for_top_area_options' ) ) {
	function plamen_core_dependency_for_top_area_options() {
		$dependency_options = apply_filters( 'plamen_core_filter_top_area_hide_option', $hide_dep_options = array() );

		return $dependency_options;
	}
}

if ( ! function_exists( 'plamen_core_register_top_area_header_areas' ) ) {
	function plamen_core_register_top_area_header_areas() {
		register_sidebar(
			array(
				'id'            => 'qodef-top-area-left',
				'name'          => esc_html__( 'Header Top Area - Left', 'plamen-core' ),
				'description'   => esc_html__( 'Widgets added here will appear on the left side in top header area', 'plamen-core' ),
				'before_widget' => '<div id="%1$s" class="widget %2$s qodef-top-bar-widget">',
				'after_widget'  => '</div>'
			)
		);

        register_sidebar(
            array(
                'id'            => 'qodef-top-area-center',
                'name'          => esc_html__( 'Header Top Area - Center', 'plamen-core' ),
                'description'   => esc_html__( 'Widgets added here will appear on the center side in top header area', 'plamen-core' ),
                'before_widget' => '<div id="%1$s" class="widget %2$s qodef-top-bar-widget">',
                'after_widget'  => '</div>'
            )
        );

		register_sidebar(
			array(
				'id'            => 'qodef-top-area-right',
				'name'          => esc_html__( 'Header Top Area - Right', 'plamen-core' ),
				'description'   => esc_html__( 'Widgets added here will appear on the right side in top header area', 'plamen-core' ),
				'before_widget' => '<div id="%1$s" class="widget %2$s qodef-top-bar-widget">',
				'after_widget'  => '</div>'
			)
		);
	}

	add_action( 'plamen_core_action_additional_header_widgets_area', 'plamen_core_register_top_area_header_areas' );
}

if ( ! function_exists( 'plamen_core_set_top_area_header_widget_area' ) ) {
	function plamen_core_set_top_area_header_widget_area( $widget_area_map ) {
		
		if ( $widget_area_map['header_layout'] === 'top-area-left' ) {
			$widget_area_map['is_enabled']          = true;
			$widget_area_map['default_widget_area'] = 'qodef-top-area-left';
			$widget_area_map['custom_widget_area']  = '';
		} elseif ( $widget_area_map['header_layout'] === 'top-area-center' ) {
			$widget_area_map['is_enabled']          = true;
			$widget_area_map['default_widget_area'] = 'qodef-top-area-center';
			$widget_area_map['custom_widget_area']  = '';
		} elseif ( $widget_area_map['header_layout'] === 'top-area-right' ) {
            $widget_area_map['is_enabled']          = true;
            $widget_area_map['default_widget_area'] = 'qodef-top-area-right';
            $widget_area_map['custom_widget_area']  = '';
        }
		
		return $widget_area_map;
	}
	
	add_filter( 'plamen_core_filter_header_widget_area', 'plamen_core_set_top_area_header_widget_area' );
}