<?php

if ( ! function_exists( 'plamen_core_add_separator_widget' ) ) {
	/**
	 * Function that add widget into widgets list for registration
	 *
	 * @param array $widgets
	 *
	 * @return array
	 */
	function plamen_core_add_separator_widget( $widgets ) {
		$widgets[] = 'PlamenCoreSeparatorWidget';
		
		return $widgets;
	}
	
	add_filter( 'plamen_core_filter_register_widgets', 'plamen_core_add_separator_widget' );
}

if ( class_exists( 'QodeFrameworkWidget' ) ) {
	class PlamenCoreSeparatorWidget extends QodeFrameworkWidget {
		
		public function map_widget() {
			$widget_mapped = $this->import_shortcode_options( array(
				'shortcode_base' => 'plamen_core_separator'
			) );
			if( $widget_mapped ) {
				$this->set_base( 'plamen_core_separator' );
				$this->set_name( esc_html__( 'Plamen Separator', 'plamen-core' ) );
				$this->set_description( esc_html__( 'Add a separator element into widget areas', 'plamen-core' ) );
			}
		}
		
		public function render( $atts ) {
			$params = $this->generate_string_params( $atts );
			
			echo do_shortcode( "[plamen_core_separator $params]" ); // XSS OK
		}
	}
}