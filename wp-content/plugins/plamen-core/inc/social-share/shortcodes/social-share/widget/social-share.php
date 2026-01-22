<?php

if ( ! function_exists( 'plamen_core_add_social_share_widget' ) ) {
	/**
	 * Function that add widget into widgets list for registration
	 *
	 * @param array $widgets
	 *
	 * @return array
	 */
	function plamen_core_add_social_share_widget( $widgets ) {
		$widgets[] = 'PlamenCoreSocialShareWidget';
		
		return $widgets;
	}
	
	add_filter( 'plamen_core_filter_register_widgets', 'plamen_core_add_social_share_widget' );
}

if ( class_exists( 'QodeFrameworkWidget' ) ) {
	class PlamenCoreSocialShareWidget extends QodeFrameworkWidget {
		
		public function map_widget() {
			$widget_mapped = $this->import_shortcode_options( array(
				'shortcode_base' => 'plamen_core_social_share'
			) );
			if( $widget_mapped ) {
				$this->set_base( 'plamen_core_social_share' );
				$this->set_name( esc_html__( 'Plamen Social Share', 'plamen-core' ) );
				$this->set_description( esc_html__( 'Add a social share element into widget areas', 'plamen-core' ) );
			}
		}
		
		public function render( $atts ) {
			$params = $this->generate_string_params( $atts );
			
			echo do_shortcode( "[plamen_core_social_share $params]" ); // XSS OK
		}
	}
}