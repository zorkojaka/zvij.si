<?php
class FullscreenSearch extends PlamenCoreSearch {
	private static $instance;

	public function __construct() {
		parent::__construct();
		add_filter( 'plamen_filter_add_inline_style', array( $this, 'set_inline_fullscreen_search_styles' ) );
		add_action('plamen_action_page_footer_template', array($this, 'load_template'), 11); //after footer
	}
	
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	public function load_template() {
		if(is_active_widget(false,false,'plamen_core_search_opener')) {
			plamen_core_template_part('search/layouts/' . $this->search_layout, 'templates/' . $this->search_layout);
		}
	}

    public function set_inline_fullscreen_search_styles( $style ) {
        $styles       = array();

        $background_color       = plamen_core_get_option_value( 'admin', 'qodef_search_overlay_background' );
        $background_image = plamen_core_get_option_value( 'admin', 'qodef_search_overlay_background_image' );

        if ( $background_color !== '' ) {
            $styles['background-color'] = $background_color;
        }

        if ( $background_image !== '' ) {
            $styles['background-image'] = 'url(' . esc_url( wp_get_attachment_image_url( $background_image, 'full' ) ) . ')';
            $styles['background-repeat'] = 'no-repeat';
            $styles['background-size'] = 'cover';
        }

        if ( ! empty( $styles ) ) {
            $style .= qode_framework_dynamic_style( '.qodef-fullscreen-search-holder', $styles );
        }

        return $style;
    }
}