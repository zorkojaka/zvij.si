<?php

/**
 * Global templates hooks
 */

if ( ! function_exists( 'plamen_add_main_woo_page_template_holder' ) ) {
	/**
	 * Function that render additional content for main shop page
	 */
	function plamen_add_main_woo_page_template_holder() {
		echo '<main id="qodef-page-content" class="qodef-grid qodef-layout--template qodef--no-bottom-space ' . esc_attr( plamen_get_grid_gutter_classes() ) . '"><div class="qodef-grid-inner clear">';
	}
}

if ( ! function_exists( 'plamen_add_main_woo_page_template_holder_end' ) ) {
	/**
	 * Function that render additional content for main shop page
	 */
	function plamen_add_main_woo_page_template_holder_end() {
		echo '</div></main>';
	}
}

if ( ! function_exists( 'plamen_add_main_woo_page_holder' ) ) {
	/**
	 * Function that render additional content around WooCommerce pages
	 */
	function plamen_add_main_woo_page_holder() {
		$classes = array();
		
		// add class to pages with sidebar and on single page
		if ( plamen_is_woo_page( 'shop' ) || plamen_is_woo_page( 'category' ) || plamen_is_woo_page( 'tag' ) || plamen_is_woo_page( 'single' ) ) {
			$classes[] = 'qodef-grid-item';
		}
		
		// add class to pages with sidebar
		if ( plamen_is_woo_page( 'shop' ) || plamen_is_woo_page( 'category' ) || plamen_is_woo_page( 'tag' ) ) {
			$classes[] = plamen_get_page_content_sidebar_classes();
		}
		
		$classes[] = plamen_get_woo_main_page_classes();
		
		echo '<div id="qodef-woo-page" class="' . implode( ' ', $classes ) . '">';
	}
}

if ( ! function_exists( 'plamen_add_main_woo_page_holder_end' ) ) {
	/**
	 * Function that render additional content around WooCommerce pages
	 */
	function plamen_add_main_woo_page_holder_end() {
		echo '</div>';
	}
}

if ( ! function_exists( 'plamen_add_main_woo_page_sidebar_holder' ) ) {
	/**
	 * Function that render sidebar layout for main shop page
	 */
	function plamen_add_main_woo_page_sidebar_holder() {
		
		if ( ! is_singular( 'product' ) ) {
			// Include page content sidebar
			plamen_template_part( 'sidebar', 'templates/sidebar' );
		}
	}
}

if ( ! function_exists( 'plamen_woo_render_product_categories' ) ) {
	/**
	 * Function that render product categories
	 *
	 * @param string $before
	 * @param string $after
	 */
	function plamen_woo_render_product_categories( $before = '', $after = '' ) {
		echo plamen_woo_get_product_categories( $before, $after );
	}
}

if ( ! function_exists( 'plamen_woo_get_product_categories' ) ) {
	/**
	 * Function that render product categories
	 *
	 * @param string $before
	 * @param string $after
	 *
	 * @return string
	 */
	function plamen_woo_get_product_categories( $before = '', $after = '' ) {
		$product = plamen_woo_get_global_product();
		
		return ! empty( $product ) ? wc_get_product_category_list( $product->get_id(), '<span class="qodef-category-separator"></span>', $before, $after ) : '';
	}
}

/**
 * Shop page templates hooks
 */

if ( ! function_exists( 'plamen_add_results_and_ordering_holder' ) ) {
	/**
	 * Function that render additional content around results and ordering templates on main shop page
	 */
	function plamen_add_results_and_ordering_holder() {
		echo '<div class="qodef-woo-results">';
	}
}

if ( ! function_exists( 'plamen_add_results_and_ordering_holder_end' ) ) {
	/**
	 * Function that render additional content around results and ordering templates on main shop page
	 */
	function plamen_add_results_and_ordering_holder_end() {
		echo '</div>';
	}
}

if ( ! function_exists( 'plamen_add_product_list_item_holder' ) ) {
	/**
	 * Function that render additional content around product list item on main shop page
	 */
	function plamen_add_product_list_item_holder() {
		echo '<div class="qodef-woo-product-inner">';
	}
}

if ( ! function_exists( 'plamen_add_product_list_item_holder_end' ) ) {
	/**
	 * Function that render additional content around product list item on main shop page
	 */
	function plamen_add_product_list_item_holder_end() {
		echo '</div>';
	}
}

if ( ! function_exists( 'plamen_add_product_list_item_image_holder' ) ) {
	/**
	 * Function that render additional content around image template on main shop page
	 */
	function plamen_add_product_list_item_image_holder() {
		echo '<div class="qodef-woo-product-image">';
	}
}

if ( ! function_exists( 'plamen_add_product_list_item_image_holder_end' ) ) {
	/**
	 * Function that render additional content around image template on main shop page
	 */
	function plamen_add_product_list_item_image_holder_end() {
		echo '</div>';
	}
}

if ( ! function_exists( 'plamen_add_product_list_item_additional_image_holder' ) ) {
	/**
	 * Function that render additional content around image and sale templates on main shop page
	 */
	function plamen_add_product_list_item_additional_image_holder() {
		echo '<div class="qodef-woo-product-image-inner">';
	}
}

if ( ! function_exists( 'plamen_add_product_list_item_additional_image_holder_end' ) ) {
	/**
	 * Function that render additional content around image and sale templates on main shop page
	 */
	function plamen_add_product_list_item_additional_image_holder_end() {
		// Hook to include additional content inside product list item image
		do_action( 'plamen_action_product_list_item_additional_image_content' );
		
		echo '</div>';
	}
}

if ( ! function_exists( 'plamen_add_product_list_item_content_holder' ) ) {
	/**
	 * Function that render additional content around product info on main shop page
	 */
	function plamen_add_product_list_item_content_holder() {
		echo '<div class="qodef-woo-product-content">';
	}
}

if ( ! function_exists( 'plamen_add_product_list_item_content_holder_end' ) ) {
	/**
	 * Function that render additional content around product info on main shop page
	 */
	function plamen_add_product_list_item_content_holder_end() {
		echo '</div>';
	}
}

if ( ! function_exists( 'plamen_add_product_list_item_categories_wrapper' ) ) {
    /**
     * Function that render wrapper around product categories
     */
    function plamen_add_product_list_item_categories_wrapper() {
        echo '<div class="qodef-woo-product-hover-info">';
    }
}

if ( ! function_exists( 'plamen_add_product_list_item_categories_wrapper_end' ) ) {
    /**
     * Function that render wrapper around product categories
     */
    function plamen_add_product_list_item_categories_wrapper_end() {
        echo '</div>';
    }
}

if ( ! function_exists( 'plamen_add_product_list_item_categories' ) ) {
	/**
	 * Function that render product categories
	 */
	function plamen_add_product_list_item_categories() {
		plamen_woo_render_product_categories( '<div class="qodef-woo-product-categories">', '</div>' );
	}
}

/**
 * Product single page templates hooks
 */

if ( ! function_exists( 'plamen_add_product_single_content_holder' ) ) {
	/**
	 * Function that render additional content around image and summary templates on single product page
	 */
	function plamen_add_product_single_content_holder() {
		echo '<div class="qodef-woo-single-inner">';
	}
}

if ( ! function_exists( 'plamen_add_product_single_content_holder_end' ) ) {
	/**
	 * Function that render additional content around image and summary templates on single product page
	 */
	function plamen_add_product_single_content_holder_end() {
		echo '</div>';
	}
}

if ( ! function_exists( 'plamen_add_product_single_image_holder' ) ) {
	/**
	 * Function that render additional content around featured image on single product page
	 */
	function plamen_add_product_single_image_holder() {
		echo '<div class="qodef-woo-single-image">';
	}
}

if ( ! function_exists( 'plamen_add_product_single_image_holder_end' ) ) {
	/**
	 * Function that render additional content around featured image on single product page
	 */
	function plamen_add_product_single_image_holder_end() {
		echo '</div>';
	}
}

/**
 * Override default WooCommerce templates
 */

if ( ! function_exists( 'plamen_woo_disable_page_heading' ) ) {
	/**
	 * Function that disable heading template on main shop page
	 *
	 * @return bool
	 */
	function plamen_woo_disable_page_heading() {
		return false;
	}
}

if ( ! function_exists( 'plamen_add_product_list_holder' ) ) {
	/**
	 * Function that add additional content around product lists on main shop page
	 *
	 * @param string $html
	 *
	 * @return string which contains html content
	 */
	function plamen_add_product_list_holder( $html ) {
		$classes = array();
		$layout  = plamen_get_post_value_through_levels( 'qodef_product_list_item_layout' );
		$option  = plamen_get_post_value_through_levels( 'qodef_woo_product_list_columns_space' );
		
		if ( ! empty( $layout ) ) {
			$classes[] = 'qodef-item-layout--' . $layout;
		}
		
		if ( ! empty( $option ) ) {
			$classes[] = 'qodef-gutter--' . $option;
		}
		
		$classes = implode( ' ', $classes );
		
		return '<div class="qodef-woo-product-list ' . esc_attr( $classes ) . '">' . $html;
	}
}

if ( ! function_exists( 'plamen_add_product_list_holder_end' ) ) {
	/**
	 * Function that add additional content around product lists on main shop page
	 *
	 * @param string $html
	 *
	 * @return string which contains html content
	 */
	function plamen_add_product_list_holder_end( $html ) {
		return $html . '</div>';
	}
}

if ( ! function_exists( 'plamen_woo_product_list_columns' ) ) {
	/**
	 * Function that set number of columns for main shop page
	 *
	 * @param int $columns
	 *
	 * @return int
	 */
	function plamen_woo_product_list_columns( $columns ) {
		$option = plamen_get_post_value_through_levels( 'qodef_woo_product_list_columns' );
		
		if ( ! empty( $option ) ) {
			$columns = intval( $option );
		} else {
			$columns = 3;
		}
		
		return $columns;
	}
}

if ( ! function_exists( 'plamen_woo_products_per_page' ) ) {
	/**
	 * Function that set number of items for main shop page
	 *
	 * @param int $products_per_page
	 *
	 * @return int
	 */
	function plamen_woo_products_per_page( $products_per_page ) {
		$option = plamen_get_post_value_through_levels( 'qodef_woo_product_list_products_per_page' );
		
		if ( ! empty( $option ) ) {
			$products_per_page = intval( $option );
		}
		
		return $products_per_page;
	}
}

if ( ! function_exists( 'plamen_woo_pagination_args' ) ) {
	/**
	 * Function that override pagination args on main shop page
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function plamen_woo_pagination_args( $args ) {
		$args['prev_text']          = plamen_get_svg_icon('arrow');
		$args['next_text']          = plamen_get_svg_icon('arrow');
		$args['type']               = 'plain';
		$args['before_page_number'] = '0';
		
		return $args;
	}
}

if ( ! function_exists( 'plamen_add_single_product_classes' ) ) {
	/**
	 * Function that render additional content around WooCommerce pages
	 */
	function plamen_add_single_product_classes( $classes, $class = '', $post_id = 0 ) {
		if ( ! $post_id || ! in_array( get_post_type( $post_id ), array( 'product', 'product_variation' ), true ) ) {
			return $classes;
		}
		
		$product = wc_get_product( $post_id );
		
		if ( $product ) {
			$new = get_post_meta( $post_id, 'qodef_show_new_sign', true );
			
			if ( $new === 'yes' ) {
				$classes[] = 'new';
			}
		}
		
		return $classes;
	}
}

if ( ! function_exists( 'plamen_add_sale_flash_on_product' ) ) {
	/**
	 * Function for adding on sale template for product
	 */
	function plamen_add_sale_flash_on_product() {
		$product = plamen_woo_get_global_product();
		
		if ( ! empty( $product ) && $product->is_on_sale() ) {
			echo plamen_woo_set_sale_flash();
		}
	}
}

if ( ! function_exists( 'plamen_woo_set_sale_flash' ) ) {
	/**
	 * Function that override on sale template for product
	 *
	 * @return string which contains html content
	 */
	function plamen_woo_set_sale_flash() {
		$product = plamen_woo_get_global_product();
		
		return '<span class="qodef-woo-product-mark qodef-woo-onsale">' . plamen_woo_get_woocommerce_sale( $product ) . '</span>';
	}
}

if ( ! function_exists( 'plamen_woo_get_woocommerce_sale' ) ) {
	function plamen_woo_get_woocommerce_sale( $product ) {
		$enable_percent_mark = plamen_get_post_value_through_levels( 'qodef_woo_enable_percent_sign_value' );
		$price               = intval( $product->get_regular_price() );
		$sale_price          = intval( $product->get_sale_price() );
		
		if ( $price > 0 && $enable_percent_mark === 'yes' ) {
			return '-' . ( 100 - round( ( $sale_price * 100 ) / $price ) ) . '%';
		} else {
			return esc_html__( 'Sale', 'plamen' );
		}
	}
}

if ( ! function_exists( 'plamen_add_out_of_stock_mark_on_product' ) ) {
	/**
	 * Function for adding out of stock template for product
	 */
	function plamen_add_out_of_stock_mark_on_product() {
		$product = plamen_woo_get_global_product();
		
		if ( ! empty( $product ) && ! $product->is_in_stock() ) {
			echo plamen_get_out_of_stock_mark();
		}
	}
}

if ( ! function_exists( 'plamen_get_out_of_stock_mark' ) ) {
	/**
	 * Function for adding out of stock template for product
	 *
	 * @return string
	 */
	function plamen_get_out_of_stock_mark() {
		return '<span class="qodef-woo-product-mark qodef-out-of-stock">' . esc_html__( 'Sold', 'plamen' ) . '</span>';
	}
}

if ( ! function_exists( 'plamen_add_new_mark_on_product' ) ) {
	/**
	 * Function for adding out of stock template for product
	 */
	function plamen_add_new_mark_on_product() {
		$product = plamen_woo_get_global_product();
		
		if ( ! empty( $product ) && $product->get_id() !== '' ) {
			echo plamen_get_new_mark( $product->get_id() );
		}
	}
}

if ( ! function_exists( 'plamen_get_new_mark' ) ) {
	/**
	 * Function for adding out of stock template for product
	 *
	 * @param int $product_id
	 *
	 * @return string
	 */
	function plamen_get_new_mark( $product_id ) {
		$option = get_post_meta( $product_id, 'qodef_show_new_sign', true );
		
		if ( $option === 'yes' ) {
			return '<span class="qodef-woo-product-mark qodef-new">' . esc_html__( 'New', 'plamen' ) . '</span>';
		}
		
		return false;
	}
}

if ( ! function_exists( 'plamen_woo_shop_loop_item_title' ) ) {
	/**
	 * Function that override product list item title template
	 */
	function plamen_woo_shop_loop_item_title() {
		$option    = plamen_get_post_value_through_levels( 'qodef_woo_product_list_title_tag' );
		$title_tag = ! empty( $option ) ? esc_attr( $option ) : 'h4';
		
		echo '<' . $title_tag . ' class="qodef-woo-product-title woocommerce-loop-product__title">' . get_the_title() . '</' . $title_tag . '>';
	}
}

if ( ! function_exists( 'plamen_woo_template_single_title' ) ) {
	/**
	 * Function that override product single item title template
	 */
	function plamen_woo_template_single_title() {
		$option    = plamen_get_post_value_through_levels( 'qodef_woo_single_title_tag' );
		$title_tag = ! empty( $option ) ? esc_attr( $option ) : 'h2';
		
		echo '<' . $title_tag . ' class="qodef-woo-product-title product_title entry-title">' . get_the_title() . '</' . $title_tag . '>';
	}
}

if ( ! function_exists( 'plamen_woo_single_thumbnail_images_columns' ) ) {
	/**
	 * Function that set number of columns for thumbnail images on single product page
	 *
	 * @param int $columns
	 *
	 * @return int
	 */
	function plamen_woo_single_thumbnail_images_columns( $columns ) {
		$option = plamen_get_post_value_through_levels( 'qodef_woo_single_thumbnail_images_columns' );
		
		if ( ! empty( $option ) ) {
			$columns = intval( $option );
		}
		
		return $columns;
	}
}

if ( ! function_exists( 'plamen_woo_single_thumbnail_images_size' ) ) {
	/**
	 * Function that set thumbnail images size on single product page
	 *
	 * @param string $size
	 *
	 * @return string
	 */
	function plamen_woo_single_thumbnail_images_size( $size ) {
		return apply_filters( 'plamen_filter_woo_single_thumbnail_size', 'woocommerce_thumbnail' );
	}
}

if ( ! function_exists( 'plamen_woo_single_thumbnail_images_wrapper' ) ) {
	/**
	 * Function that add additional wrapper around thumbnail images on single product
	 */
	function plamen_woo_single_thumbnail_images_wrapper() {
		echo '<div class="qodef-woo-thumbnails-wrapper">';
	}
}

if ( ! function_exists( 'plamen_woo_single_thumbnail_images_wrapper_end' ) ) {
	/**
	 * Function that add additional wrapper around thumbnail images on single product
	 */
	function plamen_woo_single_thumbnail_images_wrapper_end() {
		echo '</div>';
	}
}

if ( ! function_exists( 'plamen_woo_single_related_product_list_columns' ) ) {
	/**
	 * Function that set number of columns for related product list on single product page
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function plamen_woo_single_related_product_list_columns( $args ) {
		$option = plamen_get_post_value_through_levels( 'qodef_woo_single_related_product_list_columns' );
		
		if ( ! empty( $option ) ) {
			$args['posts_per_page'] = intval( $option );
			$args['columns']        = intval( $option );
		}
		
		return $args;
	}
}

if ( ! function_exists( 'plamen_woo_product_get_rating_html' ) ) {
	/**
	 * Function that override ratings templates
	 *
	 * @param string $html - contains html content
	 * @param float $rating
	 * @param int $count - total number of ratings
	 *
	 * @return string
	 */
	function plamen_woo_product_get_rating_html( $html, $rating, $count ) {
		if ( ! empty( $rating ) ) {
			$html = '<div class="qodef-woo-ratings qodef-m"><div class="qodef-m-inner">';
			$html .= '<div class="qodef-m-star qodef--initial">';
			for ( $i = 0; $i < 5; $i ++ ) {
				$html .= plamen_get_icon( 'icon_star_alt', 'elegant-icons', '<span class="qodef-m-star-item">&#9734;</span>' );
			}
			$html .= '</div>';
			$html .= '<div class="qodef-m-star qodef--active" style="width:' . ( ( $rating / 5 ) * 100 ) . '%">';
			for ( $i = 0; $i < 5; $i ++ ) {
				$html .= plamen_get_icon( 'icon_star', 'elegant-icons', '<span class="qodef-m-star-item">&#9733;</span>' );
			}
			$html .= '</div>';
			$html .= '</div></div>';
		}
		
		return $html;
	}
}

if ( ! function_exists( 'plamen_woo_get_product_search_form' ) ) {
	/**
	 * Function that override product search widget form
	 *
	 * @param string $html
	 *
	 * @return string which contains html content
	 */
	function plamen_woo_get_product_search_form( $html ) {
		return plamen_get_template_part( 'woocommerce', 'templates/product-searchform' );
	}
}

if ( ! function_exists( 'plamen_woo_get_content_widget_product' ) ) {
	/**
	 * Function that override product content widget
	 *
	 * @param string $located
	 * @param string $template_name
	 * @param array $args
	 * @param string $template_path
	 * @param string $default_path
	 *
	 * @return string which contains html content
	 */
	function plamen_woo_get_content_widget_product( $located, $template_name, $args, $template_path, $default_path ) {
		
		if ( $template_name === 'content-widget-product.php' && file_exists( PLAMEN_INC_ROOT_DIR . '/woocommerce/templates/content-widget-product.php' ) ) {
			$located = PLAMEN_INC_ROOT_DIR . '/woocommerce/templates/content-widget-product.php';
		}
		
		return $located;
	}
}

if ( ! function_exists( 'plamen_woo_get_quantity_input' ) ) {
	/**
	 * Function that override quantity input
	 *
	 * @param string $located
	 * @param string $template_name
	 * @param array $args
	 * @param string $template_path
	 * @param string $default_path
	 *
	 * @return string which contains html content
	 */
	function plamen_woo_get_quantity_input( $located, $template_name, $args, $template_path, $default_path ) {
		
		if ( $template_name === 'global/quantity-input.php' && file_exists( PLAMEN_INC_ROOT_DIR . '/woocommerce/templates/global/quantity-input.php' ) ) {
			$located = PLAMEN_INC_ROOT_DIR . '/woocommerce/templates/global/quantity-input.php';
		}
		
		return $located;
	}
}

if ( ! function_exists( 'plamen_woo_get_single_product_meta' ) ) {
	/**
	 * Function that override single product meta
	 *
	 * @param string $located
	 * @param string $template_name
	 * @param array $args
	 * @param string $template_path
	 * @param string $default_path
	 *
	 * @return string which contains html content
	 */
	function plamen_woo_get_single_product_meta( $located, $template_name, $args, $template_path, $default_path ) {
		
		if ( $template_name === 'single-product/meta.php' && file_exists( PLAMEN_INC_ROOT_DIR . '/woocommerce/templates/single-product/meta.php' ) ) {
			$located = PLAMEN_INC_ROOT_DIR . '/woocommerce/templates/single-product/meta.php';
		}
		
		return $located;
	}
}