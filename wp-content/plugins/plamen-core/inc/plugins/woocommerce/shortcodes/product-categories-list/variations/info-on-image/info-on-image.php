<?php

if ( ! function_exists( 'plamen_core_add_product_categories_list_variation_info_on_image' ) ) {
	function plamen_core_add_product_categories_list_variation_info_on_image( $variations ) {
		$variations['info-on-image'] = esc_html__( 'Info On Image', 'plamen-core' );

		return $variations;
	}

	add_filter( 'plamen_core_filter_product_categories_list_layouts', 'plamen_core_add_product_categories_list_variation_info_on_image' );
}