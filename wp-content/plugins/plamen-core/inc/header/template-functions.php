<?php

if ( ! function_exists( 'plamen_core_get_header_logo_image' ) ) {
	/**
	 * This function print header logo image
	 *
	 * @param array $parameters
	 */
	function plamen_core_get_header_logo_image( $parameters = array() ) {
		$header_skin     = plamen_core_get_post_value_through_levels( 'qodef_header_skin' );
		$logo_height     = plamen_core_get_post_value_through_levels( 'qodef_logo_height' );
		$customizer_logo = plamen_core_get_customizer_logo();

		$parameters = array_merge( $parameters, array(
			'logo_classes' => ! empty( $logo_height ) ? 'qodef-height--set' : 'qodef-height--not-set',
			'logo_height'  => ! empty( $logo_height ) ? 'height:' . intval( $logo_height ) . 'px' : '',
		) );

		$available_logos = array(
			'main',
			'dark',
			'light',
		);

		$is_enabled = false;

		foreach ( $available_logos as $logo ) {
			$parameters[ 'logo_' . $logo . '_image' ] = '';

			$logo_image_id = plamen_core_get_post_value_through_levels( 'qodef_logo_' . $logo );
			
			if ( empty( $logo_image_id ) && ! empty( $header_skin ) ) {
				$logo_image_id = plamen_core_get_post_value_through_levels( 'qodef_logo_main' );
			}
			
			if ( ! empty( $logo_image_id ) ) {
				$logo_image_attr = array(
					'class'    => 'qodef-header-logo-image qodef--' . $logo,
					'itemprop' => 'image',
					'alt'      => sprintf( esc_attr__( 'logo %s', 'plamen-core' ), $logo )
				);

				$image      = wp_get_attachment_image( $logo_image_id, 'full', false, $logo_image_attr );
				$image_html = ! empty( $image ) ? $image : qode_framework_get_image_html_from_src( $logo_image_id, $logo_image_attr );

				$parameters[ 'logo_' . $logo . '_image' ] = $image_html;

				$is_enabled = true;
			}
		}

		if ( $is_enabled ) {
			echo apply_filters( 'plamen_core_filter_get_header_logo_image', plamen_core_get_template_part( 'header/templates', 'parts/logo', '', $parameters ), $parameters );
		} elseif ( ! empty( $customizer_logo ) ) {
			echo qode_framework_wp_kses_html( 'html', $customizer_logo );
		}
	}
}

if ( ! function_exists( 'plamen_core_get_header_widget_area' ) ) {
	/**
	 * This function return header widgets area
	 *
	 * @param string $header_layout
	 * @param string $widget_area
	 */
	function plamen_core_get_header_widget_area( $header_layout = '', $widget_area = 'one' ) {
		$page_id = qode_framework_get_page_id();

		$widget_area_map = apply_filters( 'plamen_core_filter_header_widget_area', array(
			'page_id'             => $page_id,
			'header_layout'       => $header_layout,
			'is_enabled'          => get_post_meta( $page_id, 'qodef_show_header_widget_areas', true ) !== 'no',
			'default_widget_area' => 'qodef-header-widget-area-' . esc_attr( $widget_area ),
			'custom_widget_area'  => get_post_meta( $page_id, 'qodef_header_custom_widget_area_' . esc_attr( $widget_area ), true )
		) );

		extract( $widget_area_map );

		if ( $is_enabled ) {
			if ( is_active_sidebar( $default_widget_area ) && empty( $custom_widget_area ) ) {
				dynamic_sidebar( $default_widget_area );
			} elseif ( ! empty( $custom_widget_area ) && is_active_sidebar( $custom_widget_area ) ) {
				dynamic_sidebar( $custom_widget_area );
			}
		}
	}
}