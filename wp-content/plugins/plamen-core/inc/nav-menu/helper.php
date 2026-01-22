<?php

if ( ! function_exists( 'plamen_core_dropdown_item_classes' ) ) {
	function plamen_core_dropdown_item_classes( $classes, $item, $args, $depth ) {
		
		if ( $depth == 0 && in_array( 'menu-item-has-children', $item->classes ) ) {
			$mega_menu   = plamen_core_get_option_value( 'nav-menu', 'qodef-enable-mega-menu', '', $item->ID );
			$mega_menu_e = is_array( $mega_menu ) && in_array( 'enable', $mega_menu );
			
			if ( $mega_menu_e ) {
				$classes = array_diff( $classes, array( 'qodef-menu-item--narrow' ) );
				$classes[] = "qodef-menu-item--wide";
				
				add_filter( 'plamen_core_filter_drop_down_second_inner_classes', function ( $innerClasses ) {
					$grid_class = false;
					$full_width = plamen_core_get_post_value_through_levels( 'qodef_wide_dropdown_full_width' );
					$grid = plamen_core_get_post_value_through_levels( 'qodef_wide_dropdown_content_grid' );
					
					if ( $grid == 'yes' || $full_width == 'no') {
						$grid_class = true;
					}
					
					$grid_class = apply_filters('plamen_core_filter_drop_down_grid', $grid_class);
					
					if( $grid_class ) {
						$innerClasses[] = 'qodef-content-grid';
					}
					
					return $innerClasses;
				} );
			} else {

				add_filter( 'plamen_core_filter_drop_down_second_inner_classes', function ( $innerClasses ) {
					$innerClasses = array_diff( $innerClasses, array( 'qodef-content-grid' ) );
					
					return $innerClasses;
				} );
			}
		}
		
		return $classes;
	}
	
	add_filter( 'nav_menu_css_class', 'plamen_core_dropdown_item_classes', 11, 4 );
}

if ( ! function_exists( 'plamen_core_add_nav_menu_body_classes' ) ) {
	function plamen_core_add_nav_menu_body_classes( $classes ) {
		$full_width = plamen_core_get_post_value_through_levels( 'qodef_wide_dropdown_full_width' );
		$appearance = plamen_core_get_post_value_through_levels( 'qodef_dropdown_appearance' );

		if ( $full_width == 'yes' ) {
			$classes[] = 'qodef-drop-down-second--full-width';
		}

		if ( ! empty( $appearance ) ) {
			$classes[] = 'qodef-drop-down-second--' . esc_attr( $appearance );
		}
		
		return $classes;
	}
	
	add_filter( 'body_class', 'plamen_core_add_nav_menu_body_classes' );
}

if ( ! function_exists( 'plamen_core_set_nav_menu_styles' ) ) {
	/**
	 * Function that generates module inline styles
	 *
	 * @param string $style
	 *
	 * @return string
	 */
	function plamen_core_set_nav_menu_styles( $style ) {
		$styles = array();
		
		$dropdown_top_position = plamen_core_get_post_value_through_levels( 'qodef_dropdown_top_position' );
		
		if ( $dropdown_top_position !== '' ) {
			if ( qode_framework_string_ends_with_space_units( $dropdown_top_position, true ) ) {
				$styles['top'] = $dropdown_top_position;
			} else {
				$styles['top'] = intval( $dropdown_top_position ) . '%';
			}
		}
		
		if ( ! empty( $styles ) ) {
			$style .= qode_framework_dynamic_style( '.qodef-header-navigation ul li .qodef-drop-down-second', $styles );
		}
		
		return $style;
	}
	
	add_filter( 'plamen_filter_add_inline_style', 'plamen_core_set_nav_menu_styles' );
}

if ( ! function_exists( 'plamen_core_set_nav_menu_typography_styles' ) ) {
	/**
	 * Function that generates module inline styles
	 *
	 * @param string $style
	 *
	 * @return string
	 */
	function plamen_core_set_nav_menu_typography_styles( $style ) {
		$scope = PLAMEN_CORE_OPTIONS_NAME;
		
		$first_lvl_styles             = plamen_core_get_typography_styles( $scope, 'qodef_nav_1st_lvl' );
		$first_lvl_hover_styles       = plamen_core_get_typography_hover_styles( $scope, 'qodef_nav_1st_lvl' );
		$second_lvl_styles            = plamen_core_get_typography_styles( $scope, 'qodef_nav_2nd_lvl' );
        $second_lvl_hover_styles      = plamen_core_get_typography_hover_styles( $scope, 'qodef_nav_2nd_lvl' );
		$second_lvl_wide_styles       = plamen_core_get_typography_styles( $scope, 'qodef_nav_2nd_lvl_wide' );
        $second_lvl_wide_hover_styles = plamen_core_get_typography_hover_styles( $scope, 'qodef_nav_2nd_lvl_wide' );
		$third_lvl_wide_styles        = plamen_core_get_typography_styles( $scope, 'qodef_nav_3rd_lvl_wide' );
        $third_lvl_wide_hover_styles  = plamen_core_get_typography_hover_styles( $scope, 'qodef_nav_3rd_lvl_wide' );
		
		$header_selector      = apply_filters( 'plamen_core_filter_nav_menu_header_selector', '.qodef-header-navigation' );
		$narrow_menu_selector = apply_filters( 'plamen_core_filter_nav_menu_narrow_header_selector', '.qodef-menu-item--narrow' );
		$wide_menu_selector   = '.qodef-menu-item--wide';
		
		$first_lvl_side_padding = plamen_core_get_option_value( 'admin', 'qodef_nav_1st_lvl_padding' );
		if ( $first_lvl_side_padding !== '' ) {
			if ( qode_framework_string_ends_with_space_units( $first_lvl_side_padding, true ) ) {
				$first_lvl_styles['padding-left'] = $first_lvl_side_padding;
				$first_lvl_styles['padding-right'] = $first_lvl_side_padding;
			} else {
				$first_lvl_styles['padding-left'] = intval( $first_lvl_side_padding ) . 'px';
				$first_lvl_styles['padding-right'] = intval( $first_lvl_side_padding ) . 'px';
			}
		}
		
		if ( ! empty( $first_lvl_styles ) ) {
			$style .= qode_framework_dynamic_style( $header_selector . '> ul > li > a', $first_lvl_styles );
		}
		
		if ( ! empty( $first_lvl_hover_styles ) ) {
			$style .= qode_framework_dynamic_style( $header_selector . '> ul > li > a:hover', $first_lvl_hover_styles );
		}
		
		$first_lvl_active_color = plamen_core_get_option_value( 'admin', 'qodef_nav_1st_lvl_active_color' );
		
		if ( ! empty( $first_lvl_active_color ) ) {
			$first_lvl_active_styles = array(
				'color' => $first_lvl_active_color
			);
			
			$style .= qode_framework_dynamic_style( array(
				$header_selector . '> ul > li.current-menu-ancestor > a',
				$header_selector . '> ul > li.current-menu-item > a'
			), $first_lvl_active_styles );
		}
		
		$first_lvl_side_margin = plamen_core_get_option_value( 'admin', 'qodef_nav_1st_lvl_margin' );
		if ( $first_lvl_side_margin !== '' ) {
			$first_lvl_li_styles = array();
			
			if ( qode_framework_string_ends_with_space_units( $first_lvl_side_margin, true ) ) {
				$first_lvl_li_styles['margin-left'] = $first_lvl_side_margin;
				$first_lvl_li_styles['margin-right'] = $first_lvl_side_margin;
			} else {
				$first_lvl_li_styles['margin-left'] = intval( $first_lvl_side_margin ) . 'px';
				$first_lvl_li_styles['margin-right'] = intval( $first_lvl_side_margin ) . 'px';
			}
			
			$style .= qode_framework_dynamic_style( array( $header_selector . '> ul > li' ), $first_lvl_li_styles );
		}
		
		if ( ! empty( $second_lvl_styles ) ) {
			$style .= qode_framework_dynamic_style( $header_selector . ' > ul > li' . $narrow_menu_selector . ' .qodef-drop-down-second ul li a', $second_lvl_styles );
		}

        if ( ! empty( $second_lvl_hover_styles ) ) {
            $style .= qode_framework_dynamic_style( $header_selector . ' > ul > li' . $narrow_menu_selector . ' .qodef-drop-down-second ul li:hover > a', $second_lvl_hover_styles );
        }
		
		$second_lvl_active_color = plamen_core_get_option_value( 'admin', 'qodef_nav_2nd_lvl_active_color' );
		
		if ( ! empty( $second_lvl_active_color ) ) {
			$second_lvl_active_styles = array(
				'color' => $second_lvl_active_color
			);
			
			$style .= qode_framework_dynamic_style( array(
				$header_selector . ' > ul > li' . $narrow_menu_selector . ' .qodef-drop-down-second ul li.current-menu-ancestor > a',
				$header_selector . ' > ul > li' . $narrow_menu_selector . ' .qodef-drop-down-second ul li.current-menu-item > a'
			), $second_lvl_active_styles );
		}
	
		if ( ! empty( $second_lvl_wide_styles ) ) {
			$style .= qode_framework_dynamic_style( $header_selector . ' > ul > li' . $wide_menu_selector . ' .qodef-drop-down-second-inner > ul > li > a', $second_lvl_wide_styles );
		}

        if ( ! empty( $second_lvl_wide_hover_styles ) ) {
            $style .= qode_framework_dynamic_style( $header_selector . ' > ul > li' . $wide_menu_selector . ' .qodef-drop-down-second-inner > ul > li > a:hover', $second_lvl_wide_hover_styles );
        }
		
		$second_lvl_wide_active_color = plamen_core_get_option_value( 'admin', 'qodef_nav_2nd_lvl_wide_active_color' );
		
		if ( ! empty( $second_lvl_wide_active_color ) ) {
			$second_lvl_wide_active_styles = array(
				'color' => $second_lvl_wide_active_color
			);
			
			$style .= qode_framework_dynamic_style( array(
				$header_selector . ' > ul > li' . $wide_menu_selector . ' .qodef-drop-down-second-inner > ul > li.current-menu-ancestor > a',
				$header_selector . ' > ul > li' . $wide_menu_selector . ' .qodef-drop-down-second-inner > ul > li.current-menu-item > a'
			), $second_lvl_wide_active_styles );
		}
		
		if ( ! empty( $third_lvl_wide_styles ) ) {
			$style .= qode_framework_dynamic_style( $header_selector . ' > ul > li' . $wide_menu_selector . ' .qodef-drop-down-second ul li ul li a', $third_lvl_wide_styles );
		}

        if ( ! empty( $third_lvl_wide_hover_styles ) ) {
            $style .= qode_framework_dynamic_style( $header_selector . ' > ul > li' . $wide_menu_selector . ' .qodef-drop-down-second ul li ul li:hover > a', $third_lvl_wide_hover_styles );
        }
		
		$third_lvl_wide_active_color = plamen_core_get_option_value( 'admin', 'qodef_nav_3rd_lvl_wide_active_color' );
		
		if ( ! empty( $third_lvl_wide_active_color ) ) {
			$third_lvl_wide_active_styles = array(
				'color' => $third_lvl_wide_active_color
			);
			
			$style .= qode_framework_dynamic_style( array(
				$header_selector . ' > ul > li' . $wide_menu_selector . ' .qodef-drop-down-second ul li ul li.current-menu-ancestor > a',
				$header_selector . ' > ul > li' . $wide_menu_selector . ' .qodef-drop-down-second ul li ul li.current-menu-item > a'
			), $third_lvl_wide_active_styles );
		}
		
		return $style;
	}
	
	add_filter( 'plamen_filter_add_inline_style', 'plamen_core_set_nav_menu_typography_styles' );
}