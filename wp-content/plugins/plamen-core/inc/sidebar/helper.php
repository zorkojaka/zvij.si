<?php

if ( ! function_exists( 'plamen_core_get_main_sidebar_config' ) ) {
    /**
     * Function that return config variables for main sidebar area
     *
     * @return array
     */
    function plamen_core_get_main_sidebar_config() {
        // Config variables
        $config = apply_filters( 'plamen_core_filter_main_sidebar_config', array(
            'title_tag'   => 'h4',
            'title_class' => 'qodef-widget-title',
        ) );
        return $config;
    }
    add_filter( 'qode_framework_filter_main_sidebar_config', 'plamen_core_get_main_sidebar_config' );
}
if ( ! function_exists( 'plamen_core_register_main_sidebar' ) ) {
    /**
     * Function that registers theme's main sidebar area
     */
    function plamen_core_register_main_sidebar() {
        // Sidebar config variables
        $config = plamen_core_get_main_sidebar_config();
        register_sidebar(
            array(
                'id'            => 'main-sidebar',
                'name'          => esc_html__( 'Main Sidebar', 'plamen-core' ),
                'description'   => esc_html__( 'In order to display widgets inside this area you need to set sidebar layout option inside general options or meta box options', 'plamen-core' ),
                'before_widget' => '<div id="%1$s" class="widget %2$s" data-area="main-sidebar">',
                'after_widget'  => '</div>',
                'before_title'  => '<'. esc_attr( $config['title_tag'] ) .' class="'. esc_attr( $config['title_class'] ) .'">',
                'after_title'   => '</'. esc_attr( $config['title_tag'] ) .'>',
            )
        );
    }
    add_action( 'widgets_init', 'plamen_core_register_main_sidebar', 1 ); // Permission 1 is set to main sidebar be at the first place
}

if ( ! function_exists( 'plamen_core_set_custom_sidebar_name' ) ) {
	/**
	 * Function that return sidebar name
	 * 
	 * @param string $sidebar_name
	 *
	 * @return string
	 */
	function plamen_core_set_custom_sidebar_name( $sidebar_name ) {
		$option = plamen_core_get_post_value_through_levels( 'qodef_page_custom_sidebar' );
		
		if ( ! empty( $option ) ) {
			$sidebar_name = $option;
		}
		
		return $sidebar_name;
	}
	
	add_filter( 'plamen_filter_sidebar_name', 'plamen_core_set_custom_sidebar_name', 5 ); // permission 5 is set to global option check be at the first place
}

if ( ! function_exists( 'plamen_core_set_sidebar_layout' ) ) {
	/**
	 * Function that return sidebar layout
	 *
	 * @param string $layout
	 *
	 * @return string
	 */
	function plamen_core_set_sidebar_layout( $layout ) {
		$option = plamen_core_get_post_value_through_levels( 'qodef_page_sidebar_layout' );
		
		if ( ! empty( $option ) ) {
			$layout = $option;
		}
		
		return $layout;
	}
	
	add_filter( 'plamen_filter_sidebar_layout', 'plamen_core_set_sidebar_layout', 5 ); // permission 5 is set to global option check be at the first place
}

if ( ! function_exists( 'plamen_core_set_sidebar_grid_gutter_classes' ) ) {
	/**
	 * Function that returns grid gutter classes
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	function plamen_core_set_sidebar_grid_gutter_classes( $classes ) {
		$option = plamen_core_get_post_value_through_levels( 'qodef_page_sidebar_grid_gutter' );
		
		if ( ! empty( $option ) ) {
			$classes = 'qodef-gutter--' . esc_attr( $option );
		}
		
		return $classes;
	}
	
	add_filter('plamen_filter_grid_gutter_classes', 'plamen_core_set_sidebar_grid_gutter_classes', 5 ); // permission 5 is set to global option check be at the first place
}