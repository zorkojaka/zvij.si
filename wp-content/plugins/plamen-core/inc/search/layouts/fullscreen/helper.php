<?php
if ( ! function_exists( 'plamen_core_register_fullscreen_search_layout' ) ) {
	function plamen_core_register_fullscreen_search_layout( $search_layouts ) {
		$search_layout = array(
			'fullscreen' => 'FullscreenSearch'
		);

		$search_layouts = array_merge( $search_layouts, $search_layout );

		return $search_layouts;
	}

	add_filter( 'plamen_core_filter_register_search_layouts', 'plamen_core_register_fullscreen_search_layout');
}