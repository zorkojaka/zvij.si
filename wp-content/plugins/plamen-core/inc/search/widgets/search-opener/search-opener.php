<?php

if ( ! function_exists( 'plamen_core_add_search_opener_widget' ) ) {
	/**
	 * Function that add widget into widgets list for registration
	 *
	 * @param array $widgets
	 *
	 * @return array
	 **/
	function plamen_core_add_search_opener_widget( $widgets ) {
		$widgets[] = 'PlamenCoreSearchOpener';
		
		return $widgets;
	}
	
	add_filter( 'plamen_core_filter_register_widgets', 'plamen_core_add_search_opener_widget' );
}

if ( class_exists( 'QodeFrameworkWidget' ) ) {
	class PlamenCoreSearchOpener extends QodeFrameworkWidget {
		
		public function __construct() {
			add_filter( 'plamen_filter_add_inline_style', array( $this, 'set_inline_search_opener_styles' ) );
			parent::__construct();
		}
		
		public function map_widget() {
			$this->set_base( 'plamen_core_search_opener' );
			$this->set_name( esc_html__( 'Plamen Search Opener', 'plamen-core' ) );
			$this->set_description( esc_html__( 'Display a "search" icon that opens the search form', 'plamen-core' ) );
			$this->set_widget_option(
				array(
					'field_type' => 'color',
					'name'       => 'search_icon_color',
					'title'      => esc_html__( 'Icon Color', 'plamen-core' )
				)
			);
			$this->set_widget_option(
				array(
					'field_type' => 'color',
					'name'       => 'search_icon_hover_color',
					'title'      => esc_html__( 'Icon Hover Color', 'plamen-core' )
				)
			);
			$this->set_widget_option(
				array(
					'field_type'  => 'text',
					'name'        => 'search_icon_margin',
					'title'       => esc_html__( 'Icon Margin', 'plamen-core' ),
					'description' => esc_html__( 'Insert margin in format: top right bottom left', 'plamen-core' )
				)
			);
			$this->set_widget_option(
				array(
					'field_type' => 'select',
					'name'       => 'search_icon_label',
					'title'      => esc_html__( 'Enable Search Icon Label', 'plamen-core' ),
					'options'    => plamen_core_get_select_type_options_pool( 'no_yes' )
				)
			);
			$this->set_widget_option(
				array(
					'field_type' => 'text',
					'name'       => 'search_icon_size',
					'title'      => esc_html__( 'Icon Size (px)', 'plamen-core' ),
				)
			);
		}
		
		public function render( $atts ) {
			$enable_search_icon_text = plamen_core_get_option_value( 'admin', 'qodef_search_icon_label' );
			
			$styles           = array();
			$show_search_text = $atts['search_icon_label'] == 'yes' || $enable_search_icon_text == 'yes';
			
			if ( ! empty( $atts['search_icon_size'] ) ) {
				$styles[] = 'font-size: ' . intval( $atts['search_icon_size'] ) . 'px';
			}
			
			if ( ! empty( $atts['search_icon_color'] ) ) {
				$styles[] = 'color: ' . $atts['search_icon_color'] . ';';
			}
			
			if ( ! empty( $atts['search_icon_margin'] ) ) {
				$styles[] = 'margin: ' . $atts['search_icon_margin'] . ';';
			}
			
			plamen_core_get_opener_icon_html( array(
				'option_name'  => 'search',
				'custom_icon'  => 'search',
				'custom_class' => 'qodef-search-opener',
				'inline_style' => $styles,
				'inline_attr'  => qode_framework_get_inline_attr( $atts['search_icon_hover_color'], 'data-hover-color' ),
				'custom_html'  => $show_search_text ? '<span class="qodef-search-opener-text">' . esc_html__( 'Search', 'plamen-core' ) . '</span>' : ''
			) );
		}
		
		public function set_inline_search_opener_styles( $style ) {
			$styles       = array();
			$hover_styles = array();
			
			$color       = plamen_core_get_option_value( 'admin', 'qodef_search_icon_color' );
			$hover_color = plamen_core_get_option_value( 'admin', 'qodef_search_icon_hover_color' );
			$font_size   = plamen_core_get_option_value( 'admin', 'qodef_search_icon_size' );
			
			if ( $color !== '' ) {
				$styles['color'] = $color;
			}
			
			if ( $font_size !== '' ) {
				$styles['font-size'] = $font_size;
			}
			
			if ( ! empty( $hover_color ) ) {
				$hover_styles['color'] = $hover_color;
			}
			
			if ( ! empty( $styles ) ) {
				$style .= qode_framework_dynamic_style( '.qodef-search-opener', $styles );
			}
			
			if ( ! empty( $hover_styles ) ) {
				$style .= qode_framework_dynamic_style( '.qodef-search-opener:hover', $hover_styles );
			}
			
			return $style;
		}
	}
}