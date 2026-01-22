<?php

if ( ! function_exists( 'plamen_core_add_stamp_shortcode' ) ) {
	/**
	 * Function that add shortcode into shortcodes list for registration
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	function plamen_core_add_stamp_shortcode( $shortcodes ) {
		$shortcodes[] = 'PlamenCoreStampShortcode';
		
		return $shortcodes;
	}
	
	add_filter( 'plamen_core_filter_register_shortcodes', 'plamen_core_add_stamp_shortcode' );
}

if ( class_exists( 'PlamenCoreShortcode' ) ) {
	class PlamenCoreStampShortcode extends PlamenCoreShortcode {
		
		public function map_shortcode() {
			$this->set_shortcode_path( PLAMEN_CORE_SHORTCODES_URL_PATH . '/stamp' );
			$this->set_base( 'plamen_core_stamp' );
			$this->set_name( esc_html__( 'Stamp', 'plamen-core' ) );
			$this->set_description( esc_html__( 'Shortcode that adds stamp element', 'plamen-core' ) );
			$this->set_category( esc_html__( 'Plamen Core', 'plamen-core' ) );
			$this->set_option( array(
				'field_type' => 'text',
				'name'       => 'custom_class',
				'title'      => esc_html__( 'Custom Class', 'plamen-core' ),
			) );
			$this->set_option( array(
				'field_type' => 'textfield',
				'name'       => 'text',
				'title'      => esc_html__( 'Stamp Text', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'color',
				'name'       => 'text_color',
				'title'      => esc_html__( 'Text Color', 'plamen-core' ),
				'dependency' => array(
					'hide' => array(
						'text' => array(
							'values' => ''
						)
					)
				),
				'group'      => esc_html__( 'Text Settings', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'textfield',
				'name'       => 'text_font_size',
				'title'      => esc_html__( 'Text Font Size (px)', 'plamen-core' ),
				'dependency' => array(
					'hide' => array(
						'text' => array(
							'values' => ''
						)
					)
				),
				'group'      => esc_html__( 'Text Settings', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'textfield',
				'name'       => 'centered_text',
				'title'      => esc_html__( 'Centered Text', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'color',
				'name'       => 'centered_text_color',
				'title'      => esc_html__( 'Centered Text Color', 'plamen-core' ),
				'dependency' => array(
					'hide' => array(
						'centered_text' => array(
							'values' => ''
						)
					)
				),
				'group'      => esc_html__( 'Text Settings', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'textfield',
				'name'       => 'centered_text_font_size',
				'title'      => esc_html__( 'Centered Text Font Size (px)', 'plamen-core' ),
				'dependency' => array(
					'hide' => array(
						'centered_text' => array(
							'values' => ''
						)
					)
				),
				'group'      => esc_html__( 'Text Settings', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type'  => 'textfield',
				'name'        => 'stamp_size',
				'title'       => esc_html__( 'Stamp Size (px)', 'plamen-core' ),
				'description' => esc_html__( 'Default value is 114', 'plamen-core' ),
				'dependency'  => array(
					'hide' => array(
						'text' => array(
							'values' => ''
						)
					)
				),
				'group'      => esc_html__( 'Text Settings', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'select',
				'name'       => 'disable_stamp',
				'title'      => esc_html__( 'Disable Stamp', 'plamen-core' ),
				'group'      => esc_html__( 'Visibility', 'plamen-core' ),
				'options'    => array(
					''     => esc_html__( 'Never', 'plamen-core' ),
					'1440' => esc_html__( 'Below 1440px', 'plamen-core' ),
					'1280' => esc_html__( 'Below 1280px', 'plamen-core' ),
					'1024' => esc_html__( 'Below 1024px', 'plamen-core' ),
					'768'  => esc_html__( 'Below 768px', 'plamen-core' ),
					'680'  => esc_html__( 'Below 680px', 'plamen-core' ),
					'480'  => esc_html__( 'Below 480px', 'plamen-core' ),
				),
				'dependency' => array(
					'hide' => array(
						'text' => array(
							'values' => ''
						)
					)
				)
			) );
			$this->set_option( array(
				'field_type'  => 'textfield',
				'name'        => 'appearing_delay',
				'title'       => esc_html__( 'Appearing Delay (ms)', 'plamen-core' ),
				'description' => esc_html__( 'Default value is 0', 'plamen-core' ),
				'dependency'  => array(
					'hide' => array(
						'text' => array(
							'values' => ''
						)
					)
				),
				'group'       => esc_html__( 'Visibility', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'select',
				'name'       => 'absolute_position',
				'title'      => esc_html__( 'Enable Absolute Position', 'plamen-core' ),
				'options'    => plamen_core_get_select_type_options_pool( 'no_yes', false ),
				'dependency' => array(
					'hide' => array(
						'text' => array(
							'values' => ''
						)
					)
				),
				'group'      => esc_html__( 'Visibility', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'textfield',
				'name'       => 'top_position',
				'title'      => esc_html__( 'Top Position (px or %)', 'plamen-core' ),
				'dependency' => array(
					'show' => array(
						'absolute_position' => array(
							'values'        => 'yes',
							'default_value' => 'no'
						)
					)
				),
				'group'      => esc_html__( 'Visibility', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'textfield',
				'name'       => 'bottom_position',
				'title'      => esc_html__( 'Bottom Position (px or %)', 'plamen-core' ),
				'dependency' => array(
					'show' => array(
						'absolute_position' => array(
							'values'        => 'yes',
							'default_value' => 'no'
						)
					)
				),
				'group'      => esc_html__( 'Visibility', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'textfield',
				'name'       => 'left_position',
				'title'      => esc_html__( 'Left Position (px or %)', 'plamen-core' ),
				'dependency' => array(
					'show' => array(
						'absolute_position' => array(
							'values'        => 'yes',
							'default_value' => 'no'
						)
					)
				),
				'group'      => esc_html__( 'Visibility', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'textfield',
				'name'       => 'right_position',
				'title'      => esc_html__( 'Right Position (px or %)', 'plamen-core' ),
				'dependency' => array(
					'show' => array(
						'absolute_position' => array(
							'values'        => 'yes',
							'default_value' => 'no'
						)
					)
				),
				'group'      => esc_html__( 'Visibility', 'plamen-core' )
			) );
		}
		
		public function render( $options, $content = null ) {
			parent::render( $options );
			$atts = $this->get_atts();
			
			$atts['holder_classes']       = $this->getHolderClasses( $atts );
			$atts['holder_styles']        = $this->getHolderStyles( $atts );
			$atts['centered_text_styles'] = $this->getCenteredTextStyles( $atts );
			$atts['holder_data']          = $this->getHolderData( $atts );
			$atts['text_data']            = $this->getModifiedText( $atts );
			
			return plamen_core_get_template_part( 'shortcodes/stamp', 'templates/stamp', '', $atts );
		}
		
		private function getHolderClasses( $atts ) {
			$holder_classes = $this->init_holder_classes();
			
			$holder_classes[] = 'qodef-stamp';
			$holder_classes[] = ! empty( $atts['disable_stamp'] ) ? 'qodef-hide-on--' . $atts['disable_stamp'] : '';
			$holder_classes[] = $atts['absolute_position'] === 'yes' ? 'qodef--abs' : '';
			
			return implode( ' ', $holder_classes );
		}
		
		private function getHolderStyles( $atts ) {
			$styles = array();
			
			if ( ! empty( $atts['text_color'] ) ) {
				$styles[] = 'color: ' . $atts['text_color'];
			}
			
			if ( ! empty( $atts['text_font_size'] ) ) {
				$styles[] = 'font-size: ' . intval( $atts['text_font_size'] ) . 'px';
			}
			if ( ! empty( $atts['text_color'] ) ) {
				$styles[] = 'color: ' . $atts['text_color'];
			}
			
			if ( ! empty( $atts['stamp_size'] ) ) {
				$styles[] = 'width: ' . intval( $atts['stamp_size'] ) . 'px';
				$styles[] = 'height: ' . intval( $atts['stamp_size'] ) . 'px';
			}
			
			if ( $atts['top_position'] !== '' ) {
				$styles[] = 'top: ' . $atts['top_position'];
			}
			if ( $atts['bottom_position'] !== '' ) {
				$styles[] = 'bottom: ' . $atts['bottom_position'];
			}
			
			if ( $atts['left_position'] !== '' ) {
				$styles[] = 'left: ' . $atts['left_position'];
			}
			
			if ( $atts['right_position'] !== '' ) {
				$styles[] = 'right: ' . $atts['right_position'];
			}
			
			return implode( ';', $styles );
		}
		
		private function getCenteredtextStyles( $atts ) {
			$styles = array();
			
			if ( ! empty( $atts['centered_text_font_size'] ) ) {
				$styles[] = 'font-size: ' . intval( $atts['centered_text_font_size'] ) . 'px';
			}
			if ( ! empty( $atts['centered_text_color'] ) ) {
				$styles[] = 'color: ' . $atts['centered_text_color'];
			}
			
			return implode( ';', $styles );
		}
		
		private function getHolderData( $atts ) {
			$slider_data = array();
			
			$slider_data['data-appearing-delay'] = ! empty( $atts['appearing_delay'] ) ? intval( $atts['appearing_delay'] ) : 0;
			
			return $slider_data;
		}
		
		private function getModifiedText( $atts ) {
			$text = $atts['text'];
			$data = array(
				'text'  => $this->get_split_text( $text ),
				'count' => count( $this->str_split_unicode( $text ) )
			);
			
			return $data;
		}
		
		private function str_split_unicode( $str ) {
			return preg_split( '~~u', $str, - 1, PREG_SPLIT_NO_EMPTY );
		}
		
		private function get_split_text( $text ) {
			if ( ! empty( $text ) ) {
				$split_text = $this->str_split_unicode( $text );
				
				foreach ( $split_text as $key => $value ) {
					$split_text[ $key ] = '<span class="qodef-m-character">' . $value . '</span>';
				}
				
				return implode( ' ', $split_text );
			}
			
			return $text;
		}
	}
}