<?php

if ( ! function_exists( 'plamen_is_installed' ) ) {
	/**
	 * Function that checks if forward plugin installed
	 *
	 * @param string $plugin - plugin name
	 *
	 * @return bool
	 */
	function plamen_is_installed( $plugin ) {
		
		switch ( $plugin ) {
			case 'framework';
				return class_exists( 'QodeFramework' );
				break;
			case 'core';
				return class_exists( 'PlamenCore' );
				break;
			case 'woocommerce';
				return class_exists( 'WooCommerce' );
				break;
			case 'gutenberg-page';
				$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : array();
				
				return method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor();
				break;
			case 'gutenberg-editor':
				return class_exists( 'WP_Block_Type' );
				break;
			default:
				return false;
		}
	}
}

if ( ! function_exists( 'plamen_include_theme_is_installed' ) ) {
	/**
	 * Function that set case is installed element for framework functionality
	 *
	 * @param bool $installed
	 * @param string $plugin - plugin name
	 *
	 * @return bool
	 */
	function plamen_include_theme_is_installed( $installed, $plugin ) {
		
		if ( $plugin === 'theme' ) {
			return class_exists( 'PlamenHandler' );
		}
		
		return $installed;
	}
	
	add_filter( 'qode_framework_filter_is_plugin_installed', 'plamen_include_theme_is_installed', 10, 2 );
}

if ( ! function_exists( 'plamen_template_part' ) ) {
	/**
	 * Function that echo module template part.
	 *
	 * @param string $module name of the module from inc folder
	 * @param string $template full path of the template to load
	 * @param string $slug
	 * @param array  $params array of parameters to pass to template
	 */
	function plamen_template_part( $module, $template, $slug = '', $params = array() ) {
		echo plamen_get_template_part( $module, $template, $slug, $params );
	}
}

if ( ! function_exists( 'plamen_get_template_part' ) ) {
	/**
	 * Function that load module template part.
	 *
	 * @param string $module name of the module from inc folder
	 * @param string $template full path of the template to load
	 * @param string $slug
	 * @param array  $params array of parameters to pass to template
	 *
	 * @return string - string containing html of template
	 */
	function plamen_get_template_part( $module, $template, $slug = '', $params = array() ) {
		//HTML Content from template
		$html          = '';
		$template_path = PLAMEN_INC_ROOT_DIR . '/' . $module;
		
		$temp = $template_path . '/' . $template;
		if ( is_array( $params ) && count( $params ) ) {
			extract( $params );
		}
		
		$template = '';
		
		if ( ! empty( $temp ) ) {
			if ( ! empty( $slug ) ) {
				$template = "{$temp}-{$slug}.php";
				
				if ( ! file_exists( $template ) ) {
					$template = $temp . '.php';
				}
			} else {
				$template = $temp . '.php';
			}
		}
		
		if ( $template ) {
			ob_start();
			include( $template );
			$html = ob_get_clean();
		}
		
		return $html;
	}
}

if ( ! function_exists( 'plamen_get_page_id' ) ) {
	/**
	 * Function that returns current page id
	 * Additional conditional is to check if current page is any wp archive page (archive, category, tag, date etc.) and returns -1
	 *
	 * @return int
	 */
	function plamen_get_page_id() {
		$page_id = get_queried_object_id();
		
		if ( plamen_is_wp_template() ) {
			$page_id = -1;
		}
		
		return apply_filters( 'plamen_filter_page_id', $page_id );
	}
}

if ( ! function_exists( 'plamen_is_wp_template' ) ) {
	/**
	 * Function that checks if current page default wp page
	 *
	 * @return bool
	 */
	function plamen_is_wp_template() {
		return is_archive() || is_search() || is_404() || ( is_front_page() && is_home() );
	}
}

if ( ! function_exists( 'plamen_get_ajax_status' ) ) {
	/**
	 * Function that return status from ajax functions
	 *
	 * @param string $status - success or error
	 * @param string $message - ajax message value
	 * @param string|array $data - returned value
	 * @param string $redirect - url address
	 */
	function plamen_get_ajax_status( $status, $message, $data = null, $redirect = '' ) {
		$response = array(
			'status'   => esc_attr( $status ),
			'message'  => esc_html( $message ),
			'data'     => $data,
			'redirect' => ! empty( $redirect ) ? esc_url( $redirect ) : '',
		);
		
		$output = json_encode( $response );
		
		exit( $output );
	}
}

if ( ! function_exists( 'plamen_get_icon' ) ) {
	/**
	 * Function that return icon html
	 *
	 * @param string $icon - icon class name
	 * @param string $icon_pack - icon pack name
	 * @param string $backup_text - backup text label if framework is not installed
	 * @param array $params - icon parameters
	 *
	 * @return string|mixed
	 */
	function plamen_get_icon( $icon, $icon_pack, $backup_text, $params = array() ) {
		$value = plamen_is_installed( 'framework' ) && plamen_is_installed( 'core' ) ? qode_framework_icons()->render_icon( $icon, $icon_pack, $params ) : $backup_text;
		
		return $value;
	}
}

if ( ! function_exists( 'plamen_render_icon' ) ) {
	/**
	 * Function that render icon html
	 *
	 * @param string $icon - icon class name
	 * @param string $icon_pack - icon pack name
	 * @param string $backup_text - backup text label if framework is not installed
	 * @param array $params - icon parameters
	 */
	function plamen_render_icon( $icon, $icon_pack, $backup_text, $params = array() ) {
		echo plamen_get_icon( $icon, $icon_pack, $backup_text, $params );
	}
}

if ( ! function_exists( 'plamen_get_button_element' ) ) {
	/**
	 * Function that returns button with provided params
	 *
	 * @param array $params - array of parameters
	 *
	 * @return string - string representing button html
	 */
	function plamen_get_button_element( $params ) {
		if ( class_exists( 'PlamenCoreButtonShortcode' ) ) {
			return PlamenCoreButtonShortcode::call_shortcode( $params );
		} else {
			$link   = isset( $params['link'] ) ? $params['link'] : '#';
			$target = isset( $params['target'] ) ? $params['target'] : '_self';
			$text   = isset( $params['text'] ) ? $params['text'] : '';
			
			return '<a itemprop="url" class="qodef-theme-button" href="' . esc_url( $link ) . '" target="' . esc_attr( $target ) . '">' . esc_html( $text ) . '</a>';
		}
	}
}

if ( ! function_exists( 'plamen_render_button_element' ) ) {
	/**
	 * Function that render button with provided params
	 *
	 * @param array $params - array of parameters
	 */
	function plamen_render_button_element( $params ) {
		echo plamen_get_button_element( $params );
	}
}

if ( ! function_exists( 'plamen_get_social_share_element') ) {
    /**
     * Function that returns social share with provided params
     *
     * @param $params array - array of parameters
     *
     * @return string - string representing social share html
     */
    function plamen_get_social_share_element( $params ) {
        return PlamenCoreSocialShareShortcode::call_shortcode( $params );
    }
}

if ( ! function_exists( 'plamen_render_social_share_element' ) ) {
    /**
     * Function that render social share with provided params
     *
     * @param $params array - array of parameters
     */
    function plamen_render_social_share_element( $params ) {
        echo plamen_get_social_share_element( $params );
    }
}

if ( ! function_exists( 'plamen_class_attribute' ) ) {
	/**
	 * Function that render class attribute
	 *
	 * @param string|array $class
	 */
	function plamen_class_attribute( $class ) {
		echo plamen_get_class_attribute( $class );
	}
}

if ( ! function_exists( 'plamen_get_class_attribute' ) ) {
	/**
	 * Function that return class attribute
	 *
	 * @param string|array $class
	 *
	 * @return string|mixed
	 */
	function plamen_get_class_attribute( $class ) {
		$value = plamen_is_installed( 'framework' ) ? qode_framework_get_class_attribute( $class ) : '';
		
		return $value;
	}
}

if ( ! function_exists( 'plamen_get_post_value_through_levels' ) ) {
	/**
	 * Function that returns meta value if exists
	 *
	 * @param string $name name of option
	 * @param int    $post_id id of
	 *
	 * @return string value of option
	 */
	function plamen_get_post_value_through_levels( $name, $post_id = null ) {
		return plamen_is_installed( 'framework' ) && plamen_is_installed( 'core' ) ? plamen_core_get_post_value_through_levels( $name, $post_id ) : '';
	}
}

if ( ! function_exists( 'plamen_get_space_value' ) ) {
	/**
	 * Function that returns spacing value based on selected option
	 *
	 * @param string $text_value - textual value of spacing
	 *
	 * @return int
	 */
	function plamen_get_space_value( $text_value ) {
		return plamen_is_installed( 'core' ) ? plamen_core_get_space_value( $text_value ) : 0;
	}
}

if ( ! function_exists( 'plamen_wp_kses_html' ) ) {
	/**
	 * Function that does escaping of specific html.
	 * It uses wp_kses function with predefined attributes array.
	 *
	 * @see wp_kses()
	 *
	 * @param string $type - type of html element
	 * @param string $content - string to escape
	 *
	 * @return string escaped output
	 */
	function plamen_wp_kses_html( $type, $content ) {
		return plamen_is_installed( 'framework' ) ? qode_framework_wp_kses_html( $type, $content ) : $content;
	}
}

if ( ! function_exists( 'plamen_svg_icon' ) ) {
    /**
     * Function that echo svg html icon
     *
     * @param $name string - icon name
     * @param $class_name string - custom html tag class name
     */
    function plamen_svg_icon( $name, $class_name = '' ) {
        echo plamen_get_svg_icon( $name, $class_name );
    }
}

if ( ! function_exists( 'plamen_get_svg_icon' ) ) {
    /**
     * Returns svg html
     *
     * @param $name string - icon name
     * @param $class_name string - custom html tag class name
     *
     * @return string|html
     */
    function plamen_get_svg_icon( $name, $class_name = '' ) {
        $html  = '';
        $class = isset( $class_name ) && ! empty( $class_name ) ? $class_name : '';

        switch ( $name ) {
            case 'arrow':
                $html = '<svg class="' . esc_attr( $class ) . '" xmlns="http://www.w3.org/2000/svg" width="16px" height="10px" viewBox="0 0 27 18"><g>
                            <path fill="#010101" d="M2.628,9.569l7.239,7.239c0.319,0.309,0.829,0.301,1.137-0.02c0.301-0.313,0.301-0.807,0-1.117
                                L5.138,9.803h19.775c0.444,0,0.805-0.359,0.805-0.805c0-0.443-0.36-0.804-0.805-0.804H5.138l5.866-5.866
                                c0.309-0.319,0.3-0.828-0.02-1.138c-0.312-0.3-0.806-0.3-1.118,0L2.627,8.43C2.313,8.745,2.313,9.253,2.626,9.568
                                C2.627,9.569,2.627,9.569,2.628,9.569L2.628,9.569z"/>
                        </g></svg>';
                break;
            case 'angle-arrow':
                $html = '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 32 32">
                            <path d="M16,23c-0.3,0-0.5-0.1-0.7-0.3l-11-11c-0.4-0.4-0.4-1,0-1.4c0.4-0.4,1-0.4,1.4,0L16,20.6l10.3-10.3c0.4-0.4,1-0.4,1.4,0
                                c0.4,0.4,0.4,1,0,1.4l-11,11C16.5,22.9,16.3,23,16,23z"/>
                        </svg>';
                break;
            case 'search':
                $html = '<svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" viewBox="0 0 16 16">
                            <path d="M15.3,6.7c0,1.7-0.6,3.1-1.8,4.3c-1.2,1.2-2.6,1.8-4.3,1.8c-1.4,0-2.7-0.4-3.8-1.3l-4,4c-0.1,0.1-0.3,0.2-0.5,0.2
                                c-0.2,0-0.3-0.1-0.5-0.2c-0.1-0.1-0.2-0.3-0.2-0.5s0.1-0.3,0.2-0.5l4-4C3.6,9.4,3.1,8.1,3.1,6.7c0-1.7,0.6-3.1,1.8-4.3
                                c1.2-1.2,2.6-1.8,4.3-1.8s3.1,0.6,4.3,1.8C14.7,3.6,15.3,5,15.3,6.7z M14.4,6.7c0-1.4-0.5-2.6-1.5-3.6c-1-1-2.2-1.5-3.6-1.5
                                S6.6,2.1,5.6,3.1c-1,1-1.5,2.2-1.5,3.6c0,1.4,0.5,2.6,1.5,3.6c1,1,2.2,1.5,3.6,1.5s2.6-0.5,3.6-1.5C13.9,9.4,14.4,8.1,14.4,6.7z"/>
                        </svg>';
                break;
            case 'frame':
                $html = '<svg xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 1236 350" width="100%" height="100%" xml:space="preserve">
                            <path fill="none" stroke="#FFFFFF" stroke-width="2" stroke-miterlimit="10" d="M71.4 140.1V97.5M71.4 98.5h497M1163.8 140.1V97.5v42.6zM1164.8 97.5H667.7M73 280.4V323M73 323h421M1164.4 323H742M83 201.3v-90.6M82 110.7h498.8M1151.4 201v-90.6M1152.4 110.4H653.6M83 220.6v90.6M82 311.2h425M1151.4 220.9v90.6M1152.4 311.5H729M594 122.3H95.4M95.4 122.3v177.3M94.4 299.6H520M640.9 122.3h498.6M1139.5 122.3v177.3M1139.5 299.6H714.9"/>
                            <path transform="rotate(-45.001 617.445 99.75)" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-miterlimit="10" d="M606.2 88.5h22.6v22.6h-22.6z"/>
                            <path transform="rotate(-45.001 617.445 84.947)" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-miterlimit="10" d="M610.2 77.7h14.4v14.4h-14.4z"/>
                            <path transform="rotate(-45.001 617.445 114.943)" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-miterlimit="10" d="M610.2 107.7h14.4v14.4h-14.4z"/>
                            <path class="st0" d="M617.4 115.7v9.4M617.4 49.5v34.3"/>
                            <path transform="rotate(-134.999 1089.225 212.656)" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-miterlimit="10" d="M1077.9 201.4h22.6V224h-22.6z"/>
                            <path transform="rotate(-134.999 1074.422 212.656)" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-miterlimit="10" d="M1067.2 205.5h14.4v14.4h-14.4z"/>
                            <path transform="rotate(-134.999 1104.418 212.656)" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-miterlimit="10" d="M1097.2 205.5h14.4v14.4h-14.4z"/>
                            <path fill="none" stroke="#FFFFFF" stroke-width="2" stroke-miterlimit="10" d="M1105.2 212.7h100.1M1038.9 212.7h34.4"/>
                            <path transform="rotate(-45.001 147.755 210.97)" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-miterlimit="10" d="M136.5 199.7h22.6v22.6h-22.6z"/>
                            <path transform="rotate(-45.001 162.558 210.97)" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-miterlimit="10" d="M155.4 203.8h14.4v14.4h-14.4z"/>
                            <path transform="rotate(-45.001 132.562 210.97)" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-miterlimit="10" d="M125.4 203.8h14.4v14.4h-14.4z"/>
                            <path fill="none" stroke="#FFFFFF" stroke-width="2" stroke-miterlimit="10" d="M131.8 211H31.7M198 211h-34.3M1163.8 323.4v-42.6 42.6z"/>
                        </svg>';
                break;

            case 'menu':
                $html = '<svg xmlns="http://www.w3.org/2000/svg" width="58" height="15">
                            <path fill="none" stroke="currentColor" stroke-miterlimit="10" d="M1.003 1.567h55.993M1.003 7.567h55.993M1.003 13.587h55.993"/>
                        </svg>';
                break;
        }

        return $html;
    }
}