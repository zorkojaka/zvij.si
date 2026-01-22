<?php

if ( ! function_exists( 'plamen_core_get_elementor_instance' ) ) {
	function plamen_core_get_elementor_instance() {
		return \Elementor\Plugin::instance();
	}
}

if ( ! function_exists( 'plamen_core_get_elementor_widgets_manager' ) ) {
	function plamen_core_get_elementor_widgets_manager() {
		return plamen_core_get_elementor_instance()->widgets_manager;
	}
}

if ( ! function_exists( 'plamen_core_load_elementor_widgets' ) ) {
	function plamen_core_load_elementor_widgets() {
		$check_code = class_exists( 'PlamenCoreDashboard' ) ? PlamenCoreDashboard::get_instance()->get_code() : true;
        $widgets = array();
        
		if ( ! empty( $check_code ) ) {
			$widgets = array();
			
			foreach ( glob( PLAMEN_CORE_SHORTCODES_PATH . '/*', GLOB_ONLYDIR ) as $shortcode ) {
				
				if ( basename( $shortcode ) !== 'dashboard' ) {
					$is_disabled = plamen_core_performance_get_option_value( $shortcode, 'plamen_core_performance_shortcode_' );
					
					if ( empty( $is_disabled ) ) {
						foreach ( glob( $shortcode . '/*-elementor.php' ) as $shortcode_load ) {
							$widgets[ basename( $shortcode_load ) ] = $shortcode_load;
						}
					}
				}
			}
			
			foreach ( glob( PLAMEN_CORE_INC_PATH . '/*/shortcodes/*/*-elementor.php' ) as $shortcode_load ) {
				$widgets[ basename( $shortcode_load ) ] = $shortcode_load;
			}
			
			foreach ( glob( PLAMEN_CORE_CPT_PATH . '/*', GLOB_ONLYDIR ) as $post_type ) {
				
				if ( basename( $post_type ) !== 'dashboard' ) {
					$is_disabled = plamen_core_performance_get_option_value( $post_type, 'plamen_core_performance_post_type_' );
					
					if ( empty( $is_disabled ) ) {
						foreach ( glob( $post_type . '/shortcodes/*/*-elementor.php' ) as $shortcode_load ) {
							$widgets[ basename( $shortcode_load ) ] = $shortcode_load;
						}
					}
				}
			}
			
			foreach ( glob( PLAMEN_CORE_PLUGINS_PATH . '/*/shortcodes/*/*-elementor.php' ) as $shortcode_load ) {
				$widgets[ basename( $shortcode_load ) ] = $shortcode_load;
			}
			
			foreach ( glob( PLAMEN_CORE_PLUGINS_PATH . '/*/post-types/*/shortcodes/*/*-elementor.php' ) as $shortcode_load ) {
				$widgets[ basename( $shortcode_load ) ] = $shortcode_load;
			}
			
			if ( ! empty( $widgets ) ) {
				ksort( $widgets );
				
				foreach ( $widgets as $widget ) {
					include_once $widget;
				}
			}
		}
	}
	
	add_action( 'elementor/widgets/widgets_registered', 'plamen_core_load_elementor_widgets' );
}