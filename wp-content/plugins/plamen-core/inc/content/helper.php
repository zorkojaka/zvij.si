<?php

if ( ! function_exists( 'plamen_core_set_page_content_styles' ) ) {
	/**
	 * Function that generates module inline styles
	 *
	 * @param string $style
	 *
	 * @return string
	 */
	function plamen_core_set_page_content_styles( $style ) {
		$styles = array();
		
		$content_margin = apply_filters( 'plamen_core_filter_content_margin', 0 );
		
		if ( $content_margin !== 0 ) {
			$styles['margin-top'] = '-' . intval( $content_margin ) . 'px';
		}
		
		if ( ! empty( $styles ) ) {
			$style .= qode_framework_dynamic_style( '#qodef-page-outer', $styles );
		}


		$style_mobile = array();
		$content_margin_mobile = apply_filters( 'plamen_core_filter_content_margin_mobile', 0 );

		if ( $content_margin_mobile !== 0 ) {
			$style_mobile['margin-top'] = '-' . intval( $content_margin_mobile ) . 'px';
		}

		if ( ! empty( $style_mobile ) ) {
			$style .= qode_framework_dynamic_style_responsive( '#qodef-page-outer', $style_mobile, '', '1024' );
		}

		return $style;
	}
	
	add_filter( 'plamen_filter_add_inline_style', 'plamen_core_set_page_content_styles' );
}