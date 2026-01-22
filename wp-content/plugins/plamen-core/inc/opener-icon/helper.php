<?php

if ( ! function_exists( 'plamen_core_get_opener_icon_class' ) ) {
	/**
	 * Returns class for icon sources
	 *
	 * @param string $option_name
	 * @param string $custom_class
	 *
	 * @return string
	 */
	function plamen_core_get_opener_icon_class( $option_name, $custom_class = '' ) {
		$class = array();
		
		if ( ! empty( $option_name ) ) {
			$icon_source  = plamen_core_get_option_value( 'admin', 'qodef_' . esc_attr( $option_name ) . '_icon_source' );
			$class_prefix = 'qodef-source';
			
			if ( $icon_source === 'icon_pack' ) {
				$class[] = $class_prefix . '--icon-pack';
			} elseif ( $icon_source === 'svg_path' ) {
				$class[] = $class_prefix . '--svg-path';
			} elseif ( $icon_source === 'predefined' ) {
				$class[] = $class_prefix . '--predefined';
			}
			
			if ( ! empty( $custom_class ) ) {
				$class[] = esc_attr( $custom_class );
			}
		}
		
		return implode( ' ', $class );
	}
}

if ( ! function_exists( 'plamen_core_get_opener_icon_html' ) ) {
	/**
	 * Returns html for opener icon sources
	 *
	 * @param array $params - opener settings
	 * @param bool  $has_close_icon
	 * @param bool  $set_icon_as_close
	 */
	function plamen_core_get_opener_icon_html( $params = array(), $has_close_icon = false, $set_icon_as_close = false ) {
		$args = array(
			'html_tag'          => '',
			'option_name'       => '',
			'custom_icon'       => '',
			'custom_id'         => '',
			'custom_class'      => '',
			'inline_style'      => '',
			'inline_attr'       => '',
			'custom_html'       => '',
			'set_icon_as_close' => $set_icon_as_close,
			'has_close_icon'    => $has_close_icon
		);
		
		$args = array_merge( $args, $params );
		
		plamen_core_template_part( 'opener-icon', 'templates/opener-icon', $args['html_tag'], $args );
	}
}

if ( ! function_exists( 'plamen_core_get_opener_icon_html_content' ) ) {
	/**
	 * Returns html for opener icon sources
	 *
	 * @param string $option_name - option name
	 * @param bool  $is_close_icon
	 * @param string  $custom_icon
	 *
	 * @return string/html
	 */
	function plamen_core_get_opener_icon_html_content( $option_name, $is_close_icon = false, $custom_icon = '' ) {
		$html = '';
		
		if ( empty( $option_name ) ) {
			return '';
		}
		
		if ( empty( $custom_icon ) ) {
			$custom_icon = 'menu';
		}
		
		$icon_source         = plamen_core_get_option_value( 'admin', 'qodef_' . esc_attr( $option_name ) . '_icon_source' );
		$icon_pack           = plamen_core_get_option_value( 'admin', 'qodef_' . esc_attr( $option_name ) . '_icon_pack' );
		$icon_svg_path       = plamen_core_get_option_value( 'admin', 'qodef_' . esc_attr( $option_name ) . '_icon_svg_path' );
		$close_icon_svg_path = plamen_core_get_option_value( 'admin', 'qodef_' . esc_attr( $option_name ) . '_close_icon_svg_path' );
		
		if ( $icon_source === 'icon_pack' && ! empty( $icon_pack ) ) {
			
			if ( $is_close_icon ) {
				$html .= qode_framework_icons()->get_specific_icon_from_pack( 'close', $icon_pack );
			} else {
				$html .= qode_framework_icons()->get_specific_icon_from_pack( $custom_icon, $icon_pack );
			}
			
		} elseif ( $icon_source === 'svg_path' && ( ( isset( $icon_svg_path ) && ! empty( $icon_svg_path ) ) || ( isset( $close_icon_svg_path ) && ! empty( $close_icon_svg_path ) ) ) ) {
			
			if ( $is_close_icon ) {
				$html .= $close_icon_svg_path;
			} else {
				$html .= $icon_svg_path;
			}
			
		} elseif ( $icon_source === 'predefined' ) {
			$html .= '<span class="qodef-m-lines">';
			$html .= '<span class="qodef-m-line qodef--1"></span>';
			$html .= '<span class="qodef-m-line qodef--2"></span>';
			$html .= '</span>';
		}
		
		return $html;
	}
}