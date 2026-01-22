<?php

if ( ! function_exists( 'plamen_core_add_instagram_list_widget' ) ) {
	/**
	 * Function that add widget into widgets list for registration
	 *
	 * @param array $widgets
	 *
	 * @return array
	 */
	function plamen_core_add_instagram_list_widget( $widgets ) {
		$widgets[] = 'PlamenCoreInstagramListWidget';
		
		return $widgets;
	}
	
	add_filter( 'plamen_core_filter_register_widgets', 'plamen_core_add_instagram_list_widget' );
}

if ( class_exists( 'QodeFrameworkWidget' ) ) {
	class PlamenCoreInstagramListWidget extends QodeFrameworkWidget {
		
		public function map_widget() {
			$this->set_widget_option(
				array(
					'field_type' => 'text',
					'name'       => 'widget_title',
					'title'      => esc_html__( 'Title', 'plamen-core' )
				)
			);
			$widget_mapped = $this->import_shortcode_options( array(
				'shortcode_base' => 'plamen_core_instagram_list'
			) );
			if( $widget_mapped ) {
				$this->set_base( 'plamen_core_instagram_list' );
				$this->set_name( esc_html__( 'Plamen Instagram List', 'plamen-core' ) );
				$this->set_description( esc_html__( 'Add a instagram list element into widget areas', 'plamen-core' ) );
			}
		}
		
		public function render( $atts ) {
			$params = $this->generate_string_params( $atts );
			
			echo do_shortcode( "[plamen_core_instagram_list $params]" ); // XSS OK
		}
	}
}