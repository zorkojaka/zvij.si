<?php

if ( ! function_exists( 'plamen_core_include_working_hours_shortcodes' ) ) {
	/**
	 * Function that includes shortcodes
	 */
	function plamen_core_include_working_hours_shortcodes() {
		foreach ( glob( PLAMEN_CORE_INC_PATH . '/working-hours/shortcodes/*/include.php' ) as $shortcode ) {
			include_once $shortcode;
		}
	}
	
	add_action( 'qode_framework_action_before_shortcodes_register', 'plamen_core_include_working_hours_shortcodes' );
}

if ( ! function_exists( 'plamen_core_include_working_hours_widgets' ) ) {
	/**
	 * Function that includes widgets
	 */
	function plamen_core_include_working_hours_widgets() {
		foreach ( glob( PLAMEN_CORE_INC_PATH . '/working-hours/shortcodes/*/widget/include.php' ) as $widget ) {
			include_once $widget;
		}
	}
	
	add_action( 'qode_framework_action_before_widgets_register', 'plamen_core_include_working_hours_widgets' );
}

if ( ! function_exists( 'plamen_core_set_working_hours_template_params' ) ) {
	/**
	 * Function that set working hours area content parameters
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	function plamen_core_set_working_hours_template_params( $params ) {
        $layout = plamen_core_get_option_value( 'admin', 'qodef_working_hours_layout' );

        if ( $layout === 'all-days' ) {
            $days = array(
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday'
            );
        } else {
            $days = array(
                'workdays',
                'saturday',
                'sunday'
            );
        }
		
		foreach ( $days as $day ) {
			$option = plamen_core_get_post_value_through_levels( 'qodef_working_hours_' . $day );
			
			$params[ $day ] = ! empty( $option ) ? esc_attr( $option ) : '';
		}
		
		return $params;
	}
	
	add_filter( 'plamen_core_filter_working_hours_template_params', 'plamen_core_set_working_hours_template_params' );
}

if ( ! function_exists( 'plamen_core_set_working_hours_special_template_params' ) ) {
	/**
	 * Function that set working hours area special content parameters
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	function plamen_core_set_working_hours_special_template_params( $params ) {
		$special_days = plamen_core_get_post_value_through_levels( 'qodef_working_hours_special_days' );
		$special_text = plamen_core_get_post_value_through_levels( 'qodef_working_hours_special_text' );
		
		if ( ! empty( $special_days ) ) {
			$special_days = array_filter( (array) $special_days, 'strlen' );
		}
		
		$params['special_days'] = $special_days;
		$params['special_text'] = esc_attr( $special_text );
		
		return $params;
	}
	
	add_filter( 'plamen_core_filter_working_hours_special_template_params', 'plamen_core_set_working_hours_special_template_params' );
}

if ( ! function_exists( 'plamen_core_working_hours_set_admin_options_map_position' ) ) {
	/**
	 * Function that set dashboard admin options map position for this module
	 *
	 * @param int $position
	 * @param string $map
	 *
	 * @return int
	 */
	function plamen_core_working_hours_set_admin_options_map_position( $position, $map ) {
		
		if ( $map === 'working-hours' ) {
			$position = 90;
		}
		
		return $position;
	}
	
	add_filter( 'plamen_core_filter_admin_options_map_position', 'plamen_core_working_hours_set_admin_options_map_position', 10, 2 );
}