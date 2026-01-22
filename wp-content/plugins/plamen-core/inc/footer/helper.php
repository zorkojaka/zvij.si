<?php

if ( ! function_exists( 'plamen_core_is_page_footer_enabled' ) ) {
	/**
	 * Function that check is module enabled
	 * 
	 * @param bool $is_enabled
	 * 
	 * @return bool
	 */
	function plamen_core_is_page_footer_enabled( $is_enabled ) {
		$option = plamen_core_get_post_value_through_levels( 'qodef_enable_page_footer' ) !== 'no';
		
		if ( ! $option ) {
			$is_enabled = false;
		}
		
		return $is_enabled;
	}
	
	add_filter( 'plamen_filter_enable_page_footer', 'plamen_core_is_page_footer_enabled' );
}

if ( ! function_exists( 'plamen_core_set_footer_holder_classes' ) ) {
	/**
	 * Function that return classes for page footer area
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	function plamen_core_set_footer_holder_classes( $classes ) {
		$uncovering_footer = plamen_core_get_post_value_through_levels( 'qodef_enable_uncovering_footer' ) === 'yes';
		
		if ( $uncovering_footer ) {
			$classes[] = 'qodef--uncover';
		}
		
		return $classes;
	}
	
	add_filter( 'plamen_filter_footer_holder_classes', 'plamen_core_set_footer_holder_classes' );
}

if ( ! function_exists( 'plamen_core_is_footer_top_area_enabled' ) ) {
	/**
	 * Function that check if page footer top area widgets are empty
	 * 
	 * @param bool $is_enabled
	 *
	 * @return bool
	 */
	function plamen_core_is_footer_top_area_enabled( $is_enabled ) {
		$option = plamen_core_get_post_value_through_levels( 'qodef_enable_top_footer_area' ) !== 'no';
		
		if ( ! $option ) {
			$is_enabled = false;
		}
		
		return $is_enabled;
	}
	
	add_filter( 'plamen_filter_enable_footer_top_area', 'plamen_core_is_footer_top_area_enabled' );
}

if ( ! function_exists( 'plamen_core_is_footer_bottom_area_enabled' ) ) {
	/**
	 * Function that check if page footer bottom area widgets are empty
	 * 
	 * @param bool $is_enabled
	 *
	 * @return bool
	 */
	function plamen_core_is_footer_bottom_area_enabled( $is_enabled ) {
		$option = plamen_core_get_post_value_through_levels( 'qodef_enable_bottom_footer_area' ) !== 'no';
		
		if ( ! $option ) {
			$is_enabled = false;
		}
		
		return $is_enabled;
	}
	
	add_filter( 'plamen_filter_enable_footer_bottom_area', 'plamen_core_is_footer_bottom_area_enabled' );
}

if ( ! function_exists( 'plamen_core_set_footer_top_area_classes' ) ) {
	/**
	 * Function that return classes for page footer top area
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	function plamen_core_set_footer_top_area_classes( $classes ) {
		$is_grid_enabled = plamen_core_get_post_value_through_levels( 'qodef_set_footer_top_area_in_grid' ) !== 'no';
		
		if ( ! $is_grid_enabled ) {
			$classes = 'qodef-content-full-width';
		}
		
		return $classes;
	}
	
	add_filter( 'plamen_filter_footer_top_area_classes', 'plamen_core_set_footer_top_area_classes' );
}

if ( ! function_exists( 'plamen_core_set_footer_bottom_area_classes' ) ) {
	/**
	 * Function that return classes for page footer bottom area
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	function plamen_core_set_footer_bottom_area_classes( $classes ) {
		$is_grid_enabled = plamen_core_get_post_value_through_levels( 'qodef_set_footer_bottom_area_in_grid' ) !== 'no';
		
		if ( ! $is_grid_enabled ) {
			$classes = 'qodef-content-full-width';
		}
		
		return $classes;
	}
	
	add_filter( 'plamen_filter_footer_bottom_area_classes', 'plamen_core_set_footer_bottom_area_classes' );
}

if ( ! function_exists( 'plamen_core_set_footer_sidebars_config' ) ) {
	/**
	 * Function that override default page footer sidebars config
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	function plamen_core_set_footer_sidebars_config( $config ) {
		$top_area_columns    = plamen_core_get_post_value_through_levels( 'qodef_set_footer_top_area_columns' );
		$bottom_area_columns = plamen_core_get_post_value_through_levels( 'qodef_set_footer_bottom_area_columns' );
		
		if ( ! empty( $top_area_columns ) ) {
			$config['footer_top_sidebars_number'] = $top_area_columns;
		}
		
		if ( ! empty( $bottom_area_columns ) ) {
			$config['footer_bottom_sidebars_number'] = $bottom_area_columns;
		}
		
		return $config;
	}
	
	add_filter( 'plamen_filter_page_footer_sidebars_config', 'plamen_core_set_footer_sidebars_config' );
    add_filter( 'plamen_core_filter_footer_areas_columns_size', 'plamen_core_set_footer_sidebars_config' );
}

if ( ! function_exists( 'plamen_core_set_footer_top_area_columns_classes' ) ) {
	/**
	 * Function that set classes for page footer top area columns
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	function plamen_core_set_footer_top_area_columns_classes( $classes ) {
		$gutter_size = plamen_core_get_post_value_through_levels( 'qodef_set_footer_top_area_grid_gutter' );
		
		if ( ! empty( $gutter_size ) ) {
			$classes[] = 'qodef-gutter--' . esc_attr( $gutter_size );
		}
		
		return $classes;
	}
	
	add_filter( 'plamen_filter_footer_top_area_columns_classes', 'plamen_core_set_footer_top_area_columns_classes' );
}

if ( ! function_exists( 'plamen_core_set_footer_bottom_area_columns_classes' ) ) {
	/**
	 * Function that set classes for page footer bottom area columns
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	function plamen_core_set_footer_bottom_area_columns_classes( $classes ) {
		$gutter_size = plamen_core_get_post_value_through_levels( 'qodef_set_footer_bottom_area_grid_gutter' );
		
		if ( ! empty( $gutter_size ) ) {
			$classes[] = 'qodef-gutter--' . esc_attr( $gutter_size );
		}
		
		return $classes;
	}
	
	add_filter( 'plamen_filter_footer_bottom_area_columns_classes', 'plamen_core_set_footer_bottom_area_columns_classes' );
}

if ( ! function_exists( 'plamen_core_set_custom_footer_widget_area' ) ) {
    /**
     * This function set custom footer widgets area
     *
     * @param string $widget_id
     * @param string $widget_area
     * @param string $column
     *
     * @return string
     */
    function plamen_core_set_custom_footer_widget_area( $widget_id, $widget_area, $column ) {
        $page_id            = qode_framework_get_page_id();
        $custom_widget_id   = 'qodef_footer_' . esc_attr( $widget_area ) . '_area_custom_widget_' . esc_attr( $column );
        $custom_widget_area = get_post_meta( $page_id, $custom_widget_id, true );

        if ( ! empty( $custom_widget_area ) ) {
            return $custom_widget_area;
        }

        return $widget_id;
    }

    add_filter( 'plamen_filter_footer_widget_area', 'plamen_core_set_custom_footer_widget_area', 10, 3 );
}

if ( ! function_exists( 'plamen_core_set_page_footer_area_styles' ) ) {
	/**
	 * Function that generates module inline styles
	 *
	 * @param string $style
	 *
	 * @return string
	 */
	function plamen_core_set_page_footer_area_styles( $style ) {
		$footer_area = array( 'top', 'bottom' );
		
		foreach ( $footer_area as $area ) {
			$styles           = array();
			$background_color = plamen_core_get_post_value_through_levels( 'qodef_' . $area . '_footer_area_background_color' );
			$background_image = plamen_core_get_post_value_through_levels( 'qodef_' . $area . '_footer_area_background_image' );
			$top_border_color = plamen_core_get_post_value_through_levels( 'qodef_' . $area . '_footer_area_top_border_color' );
			$top_border_width = plamen_core_get_post_value_through_levels( 'qodef_' . $area . '_footer_area_top_border_width' );
			
			if ( ! empty( $background_color ) ) {
				$styles['background-color'] = $background_color;
			}
			
			if ( ! empty( $background_image ) ) {
				$styles['background-image'] = 'url(' . esc_url( wp_get_attachment_image_url( $background_image, 'full' ) ) . ')';
			}
			
			if ( ! empty( $top_border_color ) ) {
				$styles['border-top-color'] = $top_border_color;
				
				if ( $top_border_width === '' ) {
					$styles['border-top-width'] = '1px';
				}
			}
			
			if ( $top_border_width !== '' ) {
				$styles['border-top-width'] = intval( $top_border_width ) . 'px';
			}

			if ( ! empty( $styles ) ) {
				$style .= qode_framework_dynamic_style( '#qodef-page-footer-' . $area . '-area', $styles );
			}
		}
		
		return $style;
	}
	
	add_filter( 'plamen_filter_add_inline_style', 'plamen_core_set_page_footer_area_styles' );
}