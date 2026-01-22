<?php

if ( ! function_exists( 'plamen_core_add_image_marquee_shortcode' ) ) {
	/**
	 * Function that add shortcode into shortcodes list for registration
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	function plamen_core_add_image_marquee_shortcode( $shortcodes ) {
		$shortcodes[] = 'PlamenCoreImageMarqueeShortcode';
		
		return $shortcodes;
	}
	
	add_filter( 'plamen_core_filter_register_shortcodes', 'plamen_core_add_image_marquee_shortcode' );
}

if ( class_exists( 'PlamenCoreShortcode' ) ) {
	class PlamenCoreImageMarqueeShortcode extends PlamenCoreShortcode {
		
		public function __construct() {
			$this->set_layouts( apply_filters( 'plamen_core_filter_image_marquee_layouts', array() ) );
			$this->set_extra_options( apply_filters( 'plamen_core_filter_image_marquee_extra_options', array() ) );
			
			parent::__construct();
		}
		
		public function map_shortcode() {
			$this->set_shortcode_path( PLAMEN_CORE_SHORTCODES_URL_PATH . '/image-marquee' );
			$this->set_base( 'plamen_core_image_marquee' );
			$this->set_name( esc_html__( 'Image Marquee', 'plamen-core' ) );
			$this->set_description( esc_html__( 'Shortcode that adds Image Marquee element', 'plamen-core' ) );
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
				'name'       => 'image',
				'title'      => esc_html__( 'Image', 'plamen-core' ),
			) );
			$this->set_option( array(
				'field_type'    => 'text',
				'name'          => 'duration',
				'title'         => esc_html__( 'Animation Duration (Seconds)', 'plamen-core' ),
				'default_value' => '20',
			) );
			$this->set_option( array(
				'field_type' => 'select',
				'name'       => 'image_action',
				'title'      => esc_html__( 'Image Action', 'plamen-core' ),
				'options'    => array(
					''            => esc_html__( 'No Action', 'plamen-core' ),
					'custom-link' => esc_html__( 'Custom Link', 'plamen-core' )
				)
			) );
			$this->set_option( array(
				'field_type' => 'text',
				'name'       => 'link',
				'title'      => esc_html__( 'Custom Link', 'plamen-core' ),
				'dependency' => array(
					'show' => array(
						'image_action' => array(
							'values'        => 'custom-link',
							'default_value' => ''
						)
					)
				)
			) );
			$this->set_option( array(
				'field_type'    => 'select',
				'name'          => 'target',
				'title'         => esc_html__( 'Custom Link Target', 'plamen-core' ),
				'options'       => plamen_core_get_select_type_options_pool( 'link_target' ),
				'default_value' => '_self',
				'dependency'    => array(
					'show' => array(
						'image_action' => array(
							'values'        => 'custom-link',
							'default_value' => ''
						)
					)
				)
			) );
			$this->map_extra_options();
		}
		
		public function render( $options, $content = null ) {
			parent::render( $options );
			$atts = $this->get_atts();
			
			$atts['holder_classes']           = $this->get_holder_classes( $atts );
			$atts['content_styles']           = $this->get_content_styles( $atts );
			$atts['mobile_content_styles']    = $this->get_content_styles( $atts, true );
			$atts['image_styles']             = $this->get_image_styles( $atts );
			$atts['copy_image_styles']        = $this->get_image_styles( $atts, true );
			$atts['mobile_image_styles']      = $this->get_image_styles( $atts, false, true );
			$atts['mobile_copy_image_styles'] = $this->get_image_styles( $atts, true, true );
			
			return plamen_core_get_template_part( 'shortcodes/image-marquee', 'variations/' . $atts['layout'] . '/templates/' . $atts['layout'], '', $atts );
		}
		
		private function get_holder_classes( $atts ) {
			$holder_classes = $this->init_holder_classes();
			
			$holder_classes[] = 'qodef-image-marquee';
			$holder_classes[] = ! empty ( $atts['layout'] ) ? 'qodef-layout--' . $atts['layout'] : '';
			
			return implode( ' ', $holder_classes );
		}
		
		private function get_content_styles( $atts, $is_mobile = false ) {
			$styles = array();
			
			$image_id   = $atts['image'];
			$image_size = 'full';
			
			if ( $is_mobile ) {
				$image_height = ( wp_get_attachment_image_src( $image_id, $image_size )[2] / 2 ) . 'px';
			} else {
				$image_height = wp_get_attachment_image_src( $image_id, $image_size )[2] . 'px';
			}
			
			if ( ! empty( $image_height ) ) {
				$styles[] = 'height: ' . $image_height;
			}
			
			return $styles;
		}
		
		private function get_image_styles( $atts, $is_copy = false, $is_mobile = false ) {
			$styles = array();
			
			$image_id   = $atts['image'];
			$image_size = 'full';
			
			$image_src = wp_get_attachment_image_src( $image_id, $image_size )[0];
			
			if ( $is_mobile ) {
				$image_width  = ( wp_get_attachment_image_src( $image_id, $image_size )[1] / 2 ) . 'px';
				$image_height = ( wp_get_attachment_image_src( $image_id, $image_size )[2] / 2 ) . 'px';
			} else {
				$image_width  = wp_get_attachment_image_src( $image_id, $image_size )[1] . 'px';
				$image_height = wp_get_attachment_image_src( $image_id, $image_size )[2] . 'px';
			}
			
			if ( ! empty( $image_src ) ) {
				$styles[] = 'background: url("' . esc_url( $image_src ) . '")';
			}
			
			if ( ! empty( $image_width ) ) {
				$styles[] = 'width: ' . $image_width;
			}
			
			if ( ! empty( $image_height ) ) {
				$styles[] = 'height: ' . $image_height;
			}
			
			if ( ! empty( $atts['duration'] ) ) {
				$styles[] = 'animation: qode-move-marquee ' . intval( $atts['duration'] ) . 's linear infinite';
				
				if ( $is_copy ) {
					$styles[] = 'animation-delay: ' . intval( $atts['duration'] ) / 2 . 's';
				}
			}
			
			return $styles;
		}
	}
}