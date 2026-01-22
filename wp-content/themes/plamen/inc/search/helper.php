<?php

if ( ! function_exists( 'plamen_get_search_page_excerpt_length' ) ) {
	/**
	 * Function that return number of characters for excerpt on search page
	 *
	 * @return int
	 */
	function plamen_get_search_page_excerpt_length() {
		$length = apply_filters( 'plamen_filter_search_page_excerpt_length', 180 );
		
		return intval( $length );
	}
}
