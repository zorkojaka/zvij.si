<?php

if ( ! function_exists( 'plamen_core_add_accordion_child_shortcode' ) ) {
	/**
	 * Function that add shortcode into shortcodes list for registration
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	function plamen_core_add_accordion_child_shortcode( $shortcodes ) {
		$shortcodes[] = 'PlamenCoreAccordionChildShortcode';
		
		return $shortcodes;
	}
	
	add_filter( 'plamen_core_filter_register_shortcodes', 'plamen_core_add_accordion_child_shortcode' );
}

if ( class_exists( 'PlamenCoreShortcode' ) ) {
	class PlamenCoreAccordionChildShortcode extends PlamenCoreShortcode {
		
		public function map_shortcode() {
			$this->set_shortcode_path( PLAMEN_CORE_SHORTCODES_URL_PATH . '/accordion' );
			$this->set_base( 'plamen_core_accordion_child' );
			$this->set_name( esc_html__( 'Accordion Child', 'plamen-core' ) );
			$this->set_description( esc_html__( 'Shortcode that adds accordion child to accordion holder', 'plamen-core' ) );
			$this->set_category( esc_html__( 'Plamen Core', 'plamen-core' ) );
			$this->set_is_child_shortcode( true );
			$this->set_parent_elements( array(
				'plamen_core_accordion'
			) );
			$this->set_is_parent_shortcode( true );
			$this->set_option( array(
				'field_type' => 'text',
				'name'       => 'title',
				'title'      => esc_html__( 'Title', 'plamen-core' ),
			) );
			$this->set_option( array(
				'field_type'    => 'select',
				'name'          => 'title_tag',
				'title'         => esc_html__( 'Title Tag', 'plamen-core' ),
				'options'       => plamen_core_get_select_type_options_pool( 'title_tag' ),
				'default_value' => 'h4'
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
			$atts['content'] = $content;

			return plamen_core_get_template_part( 'shortcodes/accordion', 'variations/'.$atts['layout'].'/templates/child', '', $atts );
		}
	}
}