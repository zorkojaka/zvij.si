<?php

if ( ! function_exists( 'plamen_core_add_image_with_content_shortcode' ) ) {
	/**
	 * Function that add shortcode into shortcodes list for registration
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	function plamen_core_add_image_with_content_shortcode( $shortcodes ) {
		$shortcodes[] = 'PlamenCoreImageWithContentShortcode';
		
		return $shortcodes;
	}
	
	add_filter( 'plamen_core_filter_register_shortcodes', 'plamen_core_add_image_with_content_shortcode' );
}

if ( class_exists( 'PlamenCoreShortcode' ) ) {
	class PlamenCoreImageWithContentShortcode extends PlamenCoreShortcode {
		
		public function map_shortcode() {
			$this->set_shortcode_path( PLAMEN_CORE_SHORTCODES_URL_PATH . '/image-with-content' );
			$this->set_base( 'plamen_core_image_with_content' );
			$this->set_name( esc_html__( 'Image With Content', 'plamen-core' ) );
			$this->set_description( esc_html__( 'Shortcode that adds element with text on one side and image on the other', 'plamen-core' ) );
			$this->set_category( esc_html__( 'Plamen Core', 'plamen-core' ) );
			$this->set_option( array(
				'field_type' => 'text',
				'name'       => 'custom_class',
				'title'      => esc_html__( 'Custom Class', 'plamen-core' ),
			) );
            $this->set_option( array(
                'field_type' => 'image',
                'name'       => 'image',
                'title'      => esc_html__( 'Image', 'plamen-core' ),
            ) );
            $this->set_option( array(
                'field_type'    => 'select',
                'name'          => 'image_position',
                'title'         => esc_html__( 'Image Position', 'plamen-core' ),
                'description'   => esc_html__( 'Should the image be on the left or right of the content?', 'plamen-core' ),
                'options'       => array(
                    'left'   => esc_html__( 'Left', 'plamen-core' ),
                    'right'  => esc_html__( 'Right', 'plamen-core' )
                )
            ) );
            $this->set_option( array(
                'field_type' => 'text',
                'name'       => 'image_size',
                'title'      => esc_html__( 'Image Size', 'plamen-core' ),
                'description'=> esc_html__( 'For predefined image sizes input thumbnail, medium, large or full. If you wish to set a custom image size, type in the desired image dimensions in pixels (e.g. 400x400).', 'plamen-core' ),
            ) );
			$this->set_option( array(
				'field_type' => 'text',
				'name'       => 'title',
				'title'      => esc_html__( 'Title', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type'  => 'text',
				'name'        => 'line_break_positions',
				'title'       => esc_html__( 'Positions of Line Break', 'plamen-core' ),
				'description' => esc_html__( 'Enter the positions of the words after which you would like to create a line break. Separate the positions with commas (e.g. if you would like the first, third, and fourth word to have a line break, you would enter "1,3,4")', 'plamen-core' ),
				'group'       => esc_html__( 'Title Style', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type'    => 'select',
				'name'          => 'disable_title_break_words',
				'title'         => esc_html__( 'Disable Title Line Break', 'plamen-core' ),
				'description'   => esc_html__( 'Enabling this option will disable title line breaks for screen size 1024 and lower', 'plamen-core' ),
				'options'       => plamen_core_get_select_type_options_pool( 'no_yes', false ),
				'default_value' => 'no',
				'group'         => esc_html__( 'Title Style', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type'    => 'select',
				'name'          => 'title_tag',
				'title'         => esc_html__( 'Title Tag', 'plamen-core' ),
				'options'       => plamen_core_get_select_type_options_pool( 'title_tag' ),
				'default_value' => 'h3',
				'group'         => esc_html__( 'Title Style', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'color',
				'name'       => 'title_color',
				'title'      => esc_html__( 'Title Color', 'plamen-core' ),
				'group'      => esc_html__( 'Title Style', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'text',
				'name'       => 'link',
				'title'      => esc_html__( 'Title Custom Link', 'plamen-core' ),
				'group'      => esc_html__( 'Title Style', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type'    => 'select',
				'name'          => 'target',
				'title'         => esc_html__( 'Custom Link Target', 'plamen-core' ),
				'options'       => plamen_core_get_select_type_options_pool( 'link_target' ),
				'default_value' => '_self',
				'dependency' => array(
					'show' => array(
						'image_action' => array(
							'values'        => 'custom-link',
							'default_value' => ''
						)
					)
				),
				'group'      => esc_html__( 'Title Style', 'plamen-core' )
			) );
            $this->set_option( array(
                'field_type' => 'text',
                'name'       => 'background_text',
                'title'      => esc_html__( 'Background Text', 'plamen-core' )
            ) );
			$this->set_option( array(
				'field_type' => 'textarea',
				'name'       => 'text',
				'title'      => esc_html__( 'Text', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'color',
				'name'       => 'text_color',
				'title'      => esc_html__( 'Text Color', 'plamen-core' ),
				'group'      => esc_html__( 'Text Style', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'text',
				'name'       => 'text_margin_top',
				'title'      => esc_html__( 'Text Margin Top', 'plamen-core' ),
				'group'      => esc_html__( 'Text Style', 'plamen-core' )
			) );
			$this->set_option( array(
				'field_type' => 'select',
				'name'       => 'content_alignment',
				'title'      => esc_html__( 'Content Alignment', 'plamen-core' ),
				'options'       => array(
					''       => esc_html__( 'Default', 'plamen-core' ),
					'left'   => esc_html__( 'Left', 'plamen-core' ),
					'center' => esc_html__( 'Center', 'plamen-core' ),
					'right'  => esc_html__( 'Right', 'plamen-core' )
				),
			) );
		}
		
		public function render( $options, $content = null ) {
			parent::render( $options );
			$atts = $this->get_atts();
			
			$atts['holder_classes'] = $this->get_holder_classes( $atts );
			$atts['title']          = $this->get_modified_title( $atts );
			$atts['title_styles']   = $this->get_title_styles( $atts );
            $atts['text_styles']    = $this->get_text_styles( $atts );
            $atts['image_params']   = $this->generate_image_params( $atts );
			
			return plamen_core_get_template_part( 'shortcodes/image-with-content', 'templates/image-with-content', '', $atts );
		}
		
		private function get_holder_classes( $atts ) {
			$holder_classes = $this->init_holder_classes();
			
			$holder_classes[] = 'qodef-image-with-content qodef-grid qodef-layout--columns qodef-col-num--2 qodef-gutter--tiny';
			$holder_classes[] = ! empty( $atts['content_alignment'] ) ?  'qodef-alignment--' . $atts['content_alignment'] : 'qodef-alignment--left';
			$holder_classes[] = ! empty( $atts['image_position'] ) ?  'qodef-image-on--' . $atts['image_position'] : 'qodef-image-on--left';
			$holder_classes[]  = $atts['disable_title_break_words'] === 'yes' ? 'qodef-title-break--disabled' : '';
			
			return implode( ' ', $holder_classes );
		}
		
		private function get_modified_title( $atts ) {
			$title = $atts['title'];
			
			if ( ! empty( $title ) && ! empty( $atts['line_break_positions'] ) ) {
				$split_title          = explode( ' ', $title );
				$line_break_positions = explode( ',', str_replace( ' ', '', $atts['line_break_positions'] ) );
				
				foreach ( $line_break_positions as $position ) {
					if ( isset( $split_title[ $position - 1 ] ) && ! empty( $split_title[ $position - 1 ] ) ) {
						$split_title[ $position - 1 ] = $split_title[ $position - 1 ] . '<br />';
					}
				}
				
				$title = implode( ' ', $split_title );
			}
			
			return $title;
		}
		
		private function get_title_styles( $atts ) {
			$styles = array();
			
			if ( ! empty( $atts['title_color'] ) ) {
				$styles[] = 'color: ' . $atts['title_color'];
			}
			
			return $styles;
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
		
		private function get_text_styles( $atts ) {
			$styles = array();
			
			if ( $atts['text_margin_top'] !== '' ) {
				if ( qode_framework_string_ends_with_space_units( $atts['text_margin_top'] ) ) {
					$styles[] = 'margin-top: ' . $atts['text_margin_top'];
				} else {
					$styles[] = 'margin-top: ' . intval( $atts['text_margin_top'] ) . 'px';
				}
			}
			
			if ( ! empty( $atts['text_color'] ) ) {
				$styles[] = 'color: ' . $atts['text_color'];
			}
			
			return $styles;
		}
	}
}