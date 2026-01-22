<?php

if ( ! function_exists( 'plamen_core_add_stacked_images_shortcode' ) ) {
	/**
	 * Function that add shortcode into shortcodes list for registration
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	function plamen_core_add_stacked_images_shortcode( $shortcodes ) {
		$shortcodes[] = 'PlamenCoreStackedImagesShortcode';
		
		return $shortcodes;
	}
	
	add_filter( 'plamen_core_filter_register_shortcodes', 'plamen_core_add_stacked_images_shortcode' );
}

if ( class_exists( 'PlamenCoreShortcode' ) ) {
	class PlamenCoreStackedImagesShortcode extends PlamenCoreShortcode {
		
		public function __construct() {
			$this->set_layouts( apply_filters( 'plamen_core_filter_stacked_images_layouts', array() ) );
			$this->set_extra_options( apply_filters( 'plamen_core_filter_stacked_images_extra_options', array() ) );
			
			parent::__construct();
		}
		
		public function map_shortcode() {
			$this->set_shortcode_path( PLAMEN_CORE_SHORTCODES_URL_PATH . '/stacked-images' );
			$this->set_base( 'plamen_core_stacked_images' );
			$this->set_name( esc_html__( 'Stacked Images', 'plamen-core' ) );
			$this->set_description( esc_html__( 'Shortcode that adds image with text element', 'plamen-core' ) );
			$this->set_category( esc_html__( 'Plamen Core', 'plamen-core' ) );
			$this->set_option( array(
				'field_type' => 'text',
				'name'       => 'custom_class',
				'title'      => esc_html__( 'Custom Class', 'plamen-core' ),
			) );
			
			$options_map = plamen_core_get_variations_options_map( $this->get_layouts() );
			
			$this->set_option( array(
				'field_type'    => 'select',
				'name'          => 'layout',
				'title'         => esc_html__( 'Layout', 'plamen-core' ),
				'options'       => $this->get_layouts(),
				'default_value' => $options_map['default_value'],
				'visibility'    => array( 'map_for_page_builder' => $options_map['visibility'] )
			) );
			$this->set_option( array(
				'field_type' => 'image',
				'name'       => 'main_image',
				'title'      => esc_html__( 'Main Image', 'plamen-core' ),
			) );
			$this->set_option( array(
				'field_type' => 'image',
				'name'       => 'stacked_image',
				'title'      => esc_html__( 'Stacked Image', 'plamen-core' ),
			) );
			$this->set_option( array(
				'field_type' => 'select',
				'name'       => 'stack_image_position',
				'title'      => esc_html__( 'Stack Image Position', 'plamen-core' ),
				'options'    => array(
					'left'   => esc_html__( 'Left Offset', 'plamen-core' ),
					'right'  => esc_html__( 'Right Offset', 'plamen-core' ),
					'bottom' => esc_html__( 'Bottom Offset', 'plamen-core' )
				),
			) );
			$this->map_extra_options();
		}
		
		public static function call_shortcode( $params ) {
			$html = qode_framework_call_shortcode( 'plamen_core_stacked_images', $params );
			$html = str_replace( "\n", '', $html );
			
			return $html;
		}
		
		public function render( $options, $content = null ) {
			parent::render( $options );
			$atts = $this->get_atts();
			
			$atts['holder_classes'] = $this->get_holder_classes( $atts );
			$atts['image_params']   = $this->generate_image_params( $atts );
			
			return plamen_core_get_template_part( 'shortcodes/stacked-images', 'variations/' . $atts['layout'] . '/templates/' . $atts['layout'], '', $atts );
		}
		
		private function get_holder_classes( $atts ) {
			$holder_classes = $this->init_holder_classes();
			
			$holder_classes[] = 'qodef-stacked-images';
			$holder_classes[] = ! empty ( $atts['layout'] ) ? 'qodef-layout--' . $atts['layout'] : '';
			$holder_classes[] = ! empty( $atts['stack_image_position'] ) ? 'qodef-stack--' . $atts['stack_image_position'] : 'qodef-stack--' . $atts['stack_image_position'];
			
			return implode( ' ', $holder_classes );
		}
		
		private function generate_image_params( $atts ) {
			$image = array();
			
			if ( ! empty( $atts['image'] ) ) {
				$id = $atts['image'];
				
				$image['image_id'] = intval( $id );
				$image_original    = wp_get_attachment_image_src( $id, 'full' );
				$image['url']      = $image_original[0];
				$image['alt']      = get_post_meta( $id, '_wp_attachment_image_alt', true );
				
				$image_size = trim( $atts['image_size'] );
				preg_match_all( '/\d+/', $image_size, $matches ); /* check if numeral width and height are entered */
				if ( in_array( $image_size, array( 'thumbnail', 'thumb', 'medium', 'large', 'full' ) ) ) {
					$image['image_size'] = $image_size;
				} elseif ( ! empty( $matches[0] ) ) {
					$image['image_size'] = array(
						$matches[0][0],
						$matches[0][1]
					);
				} else {
					$image['image_size'] = 'full';
				}
			}
			
			return $image;
		}
	}
}