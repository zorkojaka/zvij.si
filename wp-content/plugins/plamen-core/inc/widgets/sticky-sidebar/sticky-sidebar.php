<?php

if ( ! function_exists( 'plamen_core_add_sticky_sidebar_widget' ) ) {
	/**
	 * Function that add widget into widgets list for registration
	 *
	 * @param array $widgets
	 *
	 * @return array
	 */
	function plamen_core_add_sticky_sidebar_widget( $widgets ) {
		$widgets[] = 'PlamenCoreStickySidebarWidget';
		
		return $widgets;
	}
	
	add_filter( 'plamen_core_filter_register_widgets', 'plamen_core_add_sticky_sidebar_widget' );
}

if ( class_exists( 'QodeFrameworkWidget' ) ) {
	class PlamenCoreStickySidebarWidget extends QodeFrameworkWidget {
		
		public function map_widget() {
			$this->set_base( 'plamen_core_sticky_sidebar' );
			$this->set_name( esc_html__( 'Plamen Sticky Sidebar', 'plamen-core' ) );
			$this->set_description( esc_html__( 'Use this widget to make the sidebar sticky. Drag it into the sidebar above the widget which you want to be the first element in the sticky sidebar', 'plamen-core' ) );
		}
		
		public function render( $atts ) {
		}
	}
}
