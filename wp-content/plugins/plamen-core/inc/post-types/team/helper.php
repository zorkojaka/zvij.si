<?php

if ( ! function_exists( 'plamen_core_team_has_single' ) ) {
	function plamen_core_team_has_single() {
		return false;
	}
}

if ( ! function_exists( 'plamen_core_generate_team_single_layout' ) ) {
	function plamen_core_generate_team_single_layout() {
		$team_template = plamen_core_get_post_value_through_levels( 'qodef_team_single_layout' );
		$team_template = empty( $team_template ) ? 'default' : $team_template;
		
		return $team_template;
	}
	
	add_filter( 'plamen_core_filter_team_single_layout', 'plamen_core_generate_team_single_layout' );
}

if ( ! function_exists( 'plamen_core_get_team_holder_classes' ) ) {
	/**
	 * Function that return classes for the main team holder
	 *
	 * @return string
	 */
	function plamen_core_get_team_holder_classes() {
		$classes = array( '' );
		
		$classes[]   = 'qodef-team-single';
		
		$item_layout = plamen_core_generate_team_single_layout();
		$classes[]   = 'qodef-item-layout--' . $item_layout;
		
		return implode( ' ', $classes );
	}
}

if ( ! function_exists( 'plamen_core_generate_team_archive_with_shortcode' ) ) {
	/**
	 * Function that executes team list shortcode with params on archive pages
	 *
	 * @param string $tax - type of taxonomy
	 * @param string $tax_slug - slug of taxonomy
	 */
	function plamen_core_generate_team_archive_with_shortcode( $tax, $tax_slug ) {
		$params = array();
		
		$params['additional_params']  = 'tax';
		$params['tax']                = $tax;
		$params['tax_slug']           = $tax_slug;
		$params['layout']             = plamen_core_get_post_value_through_levels( 'qodef_team_archive_item_layout' );
		$params['behavior']           = plamen_core_get_post_value_through_levels( 'qodef_team_archive_behavior' );
		$params['columns']            = plamen_core_get_post_value_through_levels( 'qodef_team_archive_columns' );
		$params['space']              = plamen_core_get_post_value_through_levels( 'qodef_team_archive_space' );
		$params['columns_responsive'] = plamen_core_get_post_value_through_levels( 'qodef_team_archive_columns_responsive' );
		$params['columns_1440']       = plamen_core_get_post_value_through_levels( 'qodef_team_archive_columns_1440' );
		$params['columns_1366']       = plamen_core_get_post_value_through_levels( 'qodef_team_archive_columns_1366' );
		$params['columns_1024']       = plamen_core_get_post_value_through_levels( 'qodef_team_archive_columns_1024' );
		$params['columns_768']        = plamen_core_get_post_value_through_levels( 'qodef_team_archive_columns_768' );
		$params['columns_680']        = plamen_core_get_post_value_through_levels( 'qodef_team_archive_columns_680' );
		$params['columns_480']        = plamen_core_get_post_value_through_levels( 'qodef_team_archive_columns_480' );
		$params['slider_loop']        = plamen_core_get_post_value_through_levels( 'qodef_team_archive_slider_loop' );
		$params['slider_autoplay']    = plamen_core_get_post_value_through_levels( 'qodef_team_archive_slider_autoplay' );
		$params['slider_speed']       = plamen_core_get_post_value_through_levels( 'qodef_team_archive_slider_speed' );
		$params['slider_navigation']  = plamen_core_get_post_value_through_levels( 'navigation' );
		$params['slider_pagination']  = plamen_core_get_post_value_through_levels( 'pagination' );
		$params['pagination_type']    = plamen_core_get_post_value_through_levels( 'qodef_team_archive_pagination_type' );
		
		echo PlamenCoreTeamListShortcode::call_shortcode( $params );
	}
}

if ( ! function_exists( 'plamen_core_is_team_title_enabled' ) ) {
	function plamen_core_is_team_title_enabled( $is_enabled ) {
		if ( is_singular( 'team' ) ) {
			$is_enabled = plamen_core_get_post_value_through_levels( 'qodef_enable_team_title' ) !== 'no';
		}
		
		return $is_enabled;
	}
	
	add_filter( 'plamen_filter_enable_page_title', 'plamen_core_is_team_title_enabled' );
}

if ( ! function_exists( 'plamen_core_team_title_grid' ) ) {
	function plamen_core_team_title_grid( $enable_title_grid ) {
		if( is_singular( 'team' ) ) {
			$enable_title_grid = plamen_core_get_post_value_through_levels( 'qodef_set_team_title_area_in_grid' ) !== 'no';
		}
		
		return $enable_title_grid;
	}
	
	add_filter( 'plamen_core_filter_page_title_grid', 'plamen_core_team_title_grid' );
}

if ( ! function_exists( 'plamen_core_team_breadcrumbs_title' ) ) {
	function plamen_core_team_breadcrumbs_title( $wrap_child, $settings ) {
		if ( is_tax( 'team-category' ) ) {
			$wrap_child  = '';
			$term_object = get_term( get_queried_object_id(), 'team-category' );
			
			if ( isset( $term_object->parent ) && $term_object->parent !== 0 ) {
				$parent     = get_term( $term_object->parent );
				$wrap_child .= sprintf( $settings['link'], get_term_link( $parent->term_id ), $parent->name ) . $settings['separator'];
			}
			
			$wrap_child .= sprintf( $settings['current_item'], single_cat_title( '', false ) );
		} elseif ( is_singular( 'team' ) ) {
			$wrap_child = '';
			$post_terms = wp_get_post_terms( get_the_ID(), 'team-category' );
			
			if ( ! empty ( $post_terms ) ) {
				$post_term = $post_terms[0];
				if ( isset( $post_term->parent ) && $post_term->parent !== 0 ) {
					$parent     = get_term( $post_term->parent );
					$wrap_child .= sprintf( $settings['link'], get_term_link( $parent->term_id ), $parent->name ) . $settings['separator'];
				}
				$wrap_child .= sprintf( $settings['link'], get_term_link( $post_term ), $post_term->name ) . $settings['separator'];
			}
			
			$wrap_child .= sprintf( $settings['current_item'], get_the_title() );
		}
		
		return $wrap_child;
	}
	
	add_filter( 'plamen_core_filter_breadcrumbs_content', 'plamen_core_team_breadcrumbs_title', 10, 2 );
}

if ( ! function_exists( 'plamen_core_set_team_custom_sidebar_name' ) ) {
	/**
	 * Function that return sidebar name
	 *
	 * @param string $sidebar_name
	 *
	 * @return string
	 */
	function plamen_core_set_team_custom_sidebar_name( $sidebar_name ) {
		
		if( is_singular( 'team' ) ) {
			$option = plamen_core_get_post_value_through_levels( 'qodef_team_single_custom_sidebar' );
		} elseif ( is_tax() ) {
			$taxonomies = get_object_taxonomies( 'team' );
			
			foreach ( $taxonomies as $tax ) {
				if ( is_tax( $tax ) ) {
					$option = plamen_core_get_post_value_through_levels( 'qodef_team_archive_custom_sidebar' );
				}
			}
		}
		
		if ( isset( $option ) && ! empty( $option ) ) {
			$sidebar_name = $option;
		}
		
		return $sidebar_name;
	}
	
	add_filter( 'plamen_filter_sidebar_name', 'plamen_core_set_team_custom_sidebar_name' );
}

if ( ! function_exists( 'plamen_core_set_team_sidebar_layout' ) ) {
	/**
	 * Function that return sidebar layout
	 *
	 * @param string $layout
	 *
	 * @return string
	 */
	function plamen_core_set_team_sidebar_layout( $layout ) {
		
		if( is_singular( 'team' ) ) {
			$option = plamen_core_get_post_value_through_levels( 'qodef_team_single_sidebar_layout' );
		} elseif( is_tax() ) {
			$taxonomies = get_object_taxonomies( 'team' );
			foreach ( $taxonomies as $tax ) {
				if( is_tax( $tax ) ) {
					$option = plamen_core_get_post_value_through_levels( 'qodef_team_archive_sidebar_layout' );
				}
			}
		}
		
		if ( isset( $option ) && ! empty( $option ) ) {
			$layout = $option;
		}
		
		return $layout;
	}
	
	add_filter( 'plamen_filter_sidebar_layout', 'plamen_core_set_team_sidebar_layout' );
}

if ( ! function_exists( 'plamen_core_set_team_sidebar_grid_gutter_classes' ) ) {
	/**
	 * Function that returns grid gutter classes
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	function plamen_core_set_team_sidebar_grid_gutter_classes( $classes ) {
		
		if( is_singular( 'team' ) ) {
			$option = plamen_core_get_post_value_through_levels( 'qodef_team_single_sidebar_grid_gutter' );
		} elseif( is_tax() ) {
			$taxonomies = get_object_taxonomies( 'team' );
			foreach ( $taxonomies as $tax ) {
				if( is_tax( $tax ) ) {
					$option = plamen_core_get_post_value_through_levels( 'qodef_team_archive_sidebar_grid_gutter' );
				}
			}
		}
		if ( isset( $option ) && ! empty( $option ) ) {
			$classes = 'qodef-gutter--' . esc_attr( $option );
		}
		
		return $classes;
	}
	
	add_filter('plamen_filter_grid_gutter_classes', 'plamen_core_set_team_sidebar_grid_gutter_classes');
}

if ( ! function_exists( 'plamen_core_team_set_admin_options_map_position' ) ) {
	/**
	 * Function that set dashboard admin options map position for this module
	 *
	 * @param int $position
	 * @param string $map
	 *
	 * @return int
	 */
	function plamen_core_team_set_admin_options_map_position( $position, $map ) {
		
		if ( $map === 'team' ) {
			$position = 52;
		}
		
		return $position;
	}
	
	add_filter( 'plamen_core_filter_admin_options_map_position', 'plamen_core_team_set_admin_options_map_position', 10, 2 );
}