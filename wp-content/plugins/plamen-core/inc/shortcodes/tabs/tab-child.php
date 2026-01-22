<?php

if ( ! function_exists( 'plamen_core_add_tabs_child_shortcode' ) ) {
	/**
	 * Function that add shortcode into shortcodes list for registration
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	function plamen_core_add_tabs_child_shortcode( $shortcodes ) {
		$shortcodes[] = 'PlamenCoreTabsChildShortcode';
		
		return $shortcodes;
	}
	
	add_filter( 'plamen_core_filter_register_shortcodes', 'plamen_core_add_tabs_child_shortcode' );
}

if ( class_exists( 'PlamenCoreShortcode' ) ) {
	class PlamenCoreTabsChildShortcode extends PlamenCoreShortcode {
		
		public function map_shortcode() {
			$this->set_shortcode_path( PLAMEN_CORE_SHORTCODES_URL_PATH . '/tabs' );
			$this->set_base( 'plamen_core_tabs_child' );
			$this->set_name( esc_html__( 'Tabs Child', 'plamen-core' ) );
			$this->set_description( esc_html__( 'Shortcode that adds tab child to tabs holder', 'plamen-core' ) );
			$this->set_category( esc_html__( 'Plamen Core', 'plamen-core' ) );
			$this->set_is_child_shortcode( true );
			$this->set_parent_elements( array(
				'plamen_core_tabs'
			) );
			$this->set_is_parent_shortcode( true );
			$this->set_option( array(
				'field_type' => 'text',
				'name'       => 'tab_title',
				'title'      => esc_html__( 'Title', 'plamen-core' ),
			) );
			$this->set_option( array(
				'field_type'    => 'text',
				'name'          => 'layout',
				'title'         => esc_html__( 'Layout', 'plamen-core' ),
				'default_value' => '',
				'visibility'    => array('map_for_page_builder' => false)
			) );
		}
		
		public function render( $options, $content = null ) {
			parent::render( $options );
			$atts = $this->get_atts();
			
			$atts['tab_title'] = $atts['tab_title'] . '-' . wp_unique_id();
			$atts['content']   = $content;

			return plamen_core_get_template_part( 'shortcodes/tabs', 'variations/'.$atts['layout'].'/templates/child', '', $atts );
		}
	}
}