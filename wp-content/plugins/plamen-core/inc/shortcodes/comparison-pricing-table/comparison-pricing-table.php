<?php

if ( ! function_exists( 'plamen_core_add_cpt_shortcode' ) ) {
	/**
	 * Function that add shortcode into shortcodes list for registration
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	function plamen_core_add_cpt_shortcode( $shortcodes ) {
		$shortcodes[] = 'PlamenCoreCPTShortcode';
		
		return $shortcodes;
	}
	
	add_filter( 'plamen_core_filter_register_shortcodes', 'plamen_core_add_cpt_shortcode' );
}

if ( class_exists( 'PlamenCoreShortcode' ) ) {
	class PlamenCoreCPTShortcode extends PlamenCoreShortcode {
		
		public function __construct() {
			$this->set_layouts( apply_filters( 'plamen_core_filter_cpt_layouts', array() ) );
			
			parent::__construct();
		}
		
		public function map_shortcode() {
			$this->set_shortcode_path( PLAMEN_CORE_SHORTCODES_URL_PATH . '/comparison-pricing-table' );
			$this->set_base( 'plamen_core_comparison_pricing_table_holder' );
			$this->set_name( esc_html__( 'Comparison Pricing table', 'plamen-core' ) );
			$this->set_description( esc_html__( 'Shortcode that adds Comparison Pricing Table holder', 'plamen-core' ) );
			$this->set_category( esc_html__( 'Plamen Core', 'plamen-core' ) );
			
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
				'field_type'  => 'select',
				'name'        => 'columns',
				'title'       => esc_html__( 'Number of Columns', 'plamen-core' ),
				'description' => esc_html__( 'Choose number of columns for features and child elements', 'plamen-core' ),
				'options'     => plamen_core_get_select_type_options_pool( 'columns_number', '', array( '1', '4', '5', '6' ) )
			) );
			
			$this->set_option( array(
				'field_type' => 'text',
				'name'       => 'title',
				'title'      => esc_html__( 'Title', 'plamen-core' ),
			) );
			$this->set_option( array(
				'field_type'  => 'textarea',
				'name'        => 'features',
				'title'       => esc_html__( 'Features', 'plamen-core' ),
				'description' => esc_html__( 'Enter features. Separate each features with new line (enter).', 'plamen-core' ),
			) );
			$this->set_option( array(
				'field_type' => 'repeater',
				'name'       => 'children',
				'title'      => esc_html__( 'Child Elements', 'plamen-core' ),
				'items'      => array(
					array(
						'field_type'    => 'text',
						'name'          => 'item_title',
						'title'         => esc_html__( 'Item Title', 'plamen-core' ),
						'default_value' => ''
					),
					array(
						'field_type'  => 'text',
						'name'        => 'highlight_positions',
						'title'       => esc_html__( 'Positions of Highlight Words', 'plamen-core' ),
						'description' => esc_html__( 'Enter the positions of the words that you would like to be colored. Separate the positions with commas (e.g. if you would like the first, third, and fourth word to have different color, you would enter "1,3,4")', 'plamen-core' ),
						'group'       => esc_html__( 'Title Style', 'plamen-core' ),
					),
					array(
						'field_type' => 'color',
						'name'       => 'highlight_color',
						'title'      => esc_html__( 'Title Highlight Color', 'plamen-core' ),
						'group'      => esc_html__( 'Title Style', 'plamen-core' ),
					),
					array(
						'field_type'    => 'select',
						'name'          => 'show_button',
						'title'         => esc_html__( 'Show Button', 'plamen-core' ),
						'options'       => plamen_core_get_select_type_options_pool( 'no_yes', false ),
						'default_value' => 'yes'
					),
					array(
						'field_type' => 'text',
						'name'       => 'button_text',
						'title'      => esc_html__( 'Button Text', 'plamen-core' ),
						'dependency' => array(
							'show' => array(
								'show_button' => array(
									'values'        => 'yes',
									'default_value' => 'yes'
								)
							)
						),
					),
					array(
						'field_type' => 'text',
						'name'       => 'button_link',
						'title'      => esc_html__( 'Button Link', 'plamen-core' ),
						'dependency' => array(
							'show' => array(
								'show_button' => array(
									'values'        => 'yes',
									'default_value' => 'yes'
								)
							)
						),
					),
					array(
						'field_type' => 'textarea',
						'name'       => 'content',
						'title'      => esc_html__( 'Content', 'plamen-core' ),
					)
				)
			) );
		}
		
		public function render( $options, $content = null ) {
			parent::render( $options );
			$atts                   = $this->get_atts();
			$atts['features']       = $this->get_features_array( $atts );
			$atts['holder_classes'] = $this->get_holder_classes( $atts );
			$atts['content']        = $content;
			$atts['items']          = $this->parse_repeater_items( $atts['children'] );
			$atts['this_shortcode'] = $this;
			
			return plamen_core_get_template_part( 'shortcodes/comparison-pricing-table', 'variations/' . $atts['layout'] . '/templates/' . $atts['layout'], '', $atts );
		}
		
		public function get_modified_title( $atts ) {
			$title = $atts['item_title'];
			
			if ( ! empty( $title ) && ! empty( $atts['highlight_positions'] ) && ! empty( $atts['highlight_color'] ) ) {
				$styles               = array();
				$split_title          = explode( ' ', $title );
				$hightlight_positions = explode( ',', str_replace( ' ', '', $atts['highlight_positions'] ) );
				
				if ( ! empty( $atts['highlight_color'] ) ) {
					$styles[] = 'color: ' . $atts['highlight_color'];
				}
				
				foreach ( $hightlight_positions as $position ) {
					if ( isset( $split_title[ $position - 1 ] ) && ! empty( $split_title[ $position - 1 ] ) ) {
						$split_title[ $position - 1 ] = '<span ' . qode_framework_get_inline_style( $styles ) . '>' . $split_title[ $position - 1 ] . '</span>';
					}
				}
				
				$title = implode( ' ', $split_title );
			}
			
			return $title;
		}
		
		public function get_button_params( $atts ) {
			$button_params = $this->populate_imported_shortcode_atts( array(
				'shortcode_base' => 'plamen_core_button',
				'type'           => 'simple',
				'size'           => 'medium',
				'target'         => '_blank'
			) );
			
			if ( ! empty( $atts['button_text'] ) ) {
				$button_params['text'] = $atts['button_text'];
			}
			
			if ( ! empty( $atts['button_link'] ) ) {
				$button_params['link'] = $atts['button_link'];
			}
			
			return $button_params;
		}
		
		private function get_holder_classes( $atts ) {
			$holder_classes = $this->init_holder_classes();
			
			$holder_classes[] = 'qodef-comparision-pricing-table clear';
			
			if ( $atts['columns'] !== '' ) {
				$holder_classes[] = 'qodef-columns--' . $atts['columns'];
			}
			
			$holder_classes[] = ! empty( $atts['layout'] ) ? 'qodef-layout--' . $atts['layout'] : '';
			
			return implode( ' ', $holder_classes );
		}
		
		private function get_features_array( $atts ) {
			$features = array();
			
			if ( ! empty( $atts['features'] ) ) {
				$features = explode( '<br />', $atts['features'] );
			}
			
			return $features;
		}
	}
}