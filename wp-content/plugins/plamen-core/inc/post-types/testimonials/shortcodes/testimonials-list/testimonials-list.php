<?php

if ( ! function_exists( 'plamen_core_add_testimonials_list_shortcode' ) ) {
	/**
	 * Function that is adding shortcode into shortcodes list for registration
	 *
	 * @param array $shortcodes - Array of registered shortcodes
	 *
	 * @return array
	 */
	function plamen_core_add_testimonials_list_shortcode( $shortcodes ) {
		$shortcodes[] = 'PlamenCoreTestimonialsListShortcode';
		
		return $shortcodes;
	}
	
	add_filter( 'plamen_core_filter_register_shortcodes', 'plamen_core_add_testimonials_list_shortcode' );
}

if ( class_exists( 'PlamenCoreListShortcode' ) ) {
	class PlamenCoreTestimonialsListShortcode extends PlamenCoreListShortcode {
		
		public function __construct() {
			$this->set_post_type( 'testimonials' );
			$this->set_post_type_additional_taxonomies( array( 'testimonials-category' ) );
			$this->set_layouts( apply_filters( 'plamen_core_filter_testimonials_list_layouts', array() ) );
			$this->set_extra_options( apply_filters( 'plamen_core_filter_testimonials_list_extra_options', array() ) );
			
			parent::__construct();
		}
		
		public function map_shortcode() {
			$this->set_shortcode_path( PLAMEN_CORE_CPT_URL_PATH . '/testimonials/shortcodes/testimonials-list' );
			$this->set_base( 'plamen_core_testimonials_list' );
			$this->set_name( esc_html__( 'Testimonials List', 'plamen-core' ) );
			$this->set_description( esc_html__( 'Shortcode that displays list of testimonials', 'plamen-core' ) );
			$this->set_category( esc_html__( 'Plamen Core', 'plamen-core' ) );
			$this->set_option( array(
				'field_type' => 'text',
				'name'       => 'custom_class',
				'title'      => esc_html__( 'Custom Class', 'plamen-core' )
			) );
			$this->map_list_options( array(
				'exclude_behavior' => array( 'gallery', 'masonry', 'justified-gallery' ),
				'exclude_option'   => array( 'images_proportion', 'columns', 'slider_navigation')
			) );
            $this->set_option( array(
                'field_type' => 'select',
                'name'       => 'variation',
                'title'      => esc_html__( 'Variation', 'plamen-core' ),
                'options'    => array(
                    ''      => esc_html__( 'With Image', 'plamen-core' ),
                    'simple' => esc_html__( 'Simple', 'plamen-core' )
                ),
            ) );
			$this->set_option( array(
				'field_type' => 'select',
				'name'       => 'skin',
				'title'      => esc_html__( 'Skin', 'plamen-core' ),
				'options'    => array(
					''      => esc_html__( 'Default', 'plamen-core' ),
					'light' => esc_html__( 'Light', 'plamen-core' )
				),
			) );
			$this->map_query_options( array( 'post_type' => $this->get_post_type() ) );
			$this->map_layout_options( array( 'layouts' => $this->get_layouts() ) );
			$this->map_extra_options();
		}
		
		public function render( $options, $content = null ) {
			parent::render( $options );
			
			$atts = $this->get_atts();
			
			$atts['post_type'] = $this->get_post_type();
			
			// Additional query args
			$atts['additional_query_args'] = $this->get_additional_query_args( $atts );
			
			$atts['unique'] = wp_unique_id();
			
			$atts['holder_classes'] = $this->get_holder_classes( $atts );
			$atts['item_classes']   = $this->get_item_classes( $atts );
			$atts['slider_attr']    = $this->get_slider_data( $atts, array( 'direction' => 'vertical' ) );
			$atts['query_result']   = new \WP_Query( plamen_core_get_query_params( $atts ) );
			
			$atts['this_shortcode'] = $this;
			
			return plamen_core_get_template_part( 'post-types/testimonials/shortcodes/testimonials-list', 'templates/content', $atts['behavior'], $atts );
		}
		
		private function get_holder_classes( $atts ) {
			$holder_classes = $this->init_holder_classes();
			
			$holder_classes[] = 'qodef-testimonials-list';
			$holder_classes[] = isset( $atts['skin'] ) && ! empty( $atts['skin'] ) ? 'qodef-skin--' . $atts['skin'] : '';
			
			$list_classes   = $this->get_list_classes( $atts );
			$holder_classes = array_merge( $holder_classes, $list_classes );

            $holder_classes = array_diff($holder_classes, array('qodef-swiper-container'));
			
			return implode( ' ', $holder_classes );
		}
		
		private function get_item_classes( $atts ) {
			$item_classes = $this->init_item_classes();
			
			$list_item_classes = $this->get_list_item_classes( $atts );
			
			$item_classes = array_merge( $item_classes, $list_item_classes );
			
			return implode( ' ', $item_classes );
		}
		
		public function get_title_styles( $atts ) {
			$styles = array();
			
			if ( ! empty( $atts['text_transform'] ) ) {
				$styles[] = 'text-transform: ' . $atts['text_transform'];
			}
			
			return $styles;
		}
	}
}