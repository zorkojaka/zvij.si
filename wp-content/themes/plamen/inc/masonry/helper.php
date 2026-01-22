<?php


if ( ! function_exists( 'masterds_theme_register_masonry_scripts' ) ) {
    /**
     * Function that include modules 3rd party scripts
     */
    function plamen_register_masonry_scripts() {
        wp_register_script( 'isotope', PLAMEN_INC_ROOT . '/masonry/assets/js/plugins/isotope.pkgd.min.js', array( 'jquery' ), false, true );
        wp_register_script( 'packery', PLAMEN_INC_ROOT . '/masonry/assets/js/plugins/packery-mode.pkgd.min.js', array( 'jquery' ), false, true );
    }

    add_action( 'plamen_action_before_main_js', 'plamen_register_masonry_scripts' );
}

if ( ! function_exists( 'plamen_include_masonry_scripts' ) ) {
    /**
     * Function that include modules 3rd party scripts
     */
    function plamen_include_masonry_scripts() {
        wp_enqueue_script( 'isotope' );
        wp_enqueue_script( 'packery' );
    }
}

if ( ! function_exists( 'plamen_enqueue_masonry_scripts_for_templates' ) ) {
	/**
	 * Function that enqueue modules 3rd party scripts for templates
	 */
	function plamen_enqueue_masonry_scripts_for_templates() {
		$post_type = apply_filters( 'plamen_filter_allowed_post_type_to_enqueue_masonry_scripts', '' );
		
		if ( ! empty( $post_type ) && is_singular( $post_type ) ) {
			plamen_include_masonry_scripts();
		}
	}
	
	add_action( 'plamen_action_before_main_js', 'plamen_enqueue_masonry_scripts_for_templates' );
}

if ( ! function_exists( 'plamen_enqueue_masonry_scripts_for_shortcodes' ) ) {
	/**
	 * Function that enqueue modules 3rd party scripts for shortcodes
	 *
	 * @param array $atts
	 */
	function plamen_enqueue_masonry_scripts_for_shortcodes( $atts ) {
		
		if ( isset( $atts['behavior'] ) && $atts['behavior'] == 'masonry' ) {
			plamen_include_masonry_scripts();
		}
	}
	
	add_action( 'plamen_core_action_list_shortcodes_load_assets', 'plamen_enqueue_masonry_scripts_for_shortcodes' );

}
if ( ! function_exists( 'plamen_theme_register_masonry_scripts_for_list_shortcodes' ) ) {
    /**
     * Function that add masonry scripts to array
     *
     * @param array $scripts
     *
     * @return array
     */
    function plamen_theme_register_masonry_scripts_for_list_shortcodes( $scripts ) {
        $scripts['isotope'] = array(
            'registered'    => true
        );
        $scripts['packery'] = array(
            'registered'    => true
        );
        return $scripts;
    }
    add_filter( 'plamen_core_filter_registered_list_scripts', 'plamen_theme_register_masonry_scripts_for_list_shortcodes' );
}