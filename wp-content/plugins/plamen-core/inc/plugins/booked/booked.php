<?php

if ( ! class_exists( 'PlamenCoreBooked' ) ) {
	class PlamenCoreBooked {
		private static $instance;
		
		public function __construct() {
			// Include helper functions
			include_once PLAMEN_CORE_PLUGINS_PATH . '/booked/helper.php';
			
			if ( qode_framework_is_installed( 'booked' ) ) {
				// Init
				$this->init();
			}
		}
		
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			
			return self::$instance;
		}
		
		function init() {
			// Add calendars option into pool
			add_filter( 'plamen_core_filter_select_type_option', array( $this, 'extend_options_pool' ), 10, 2 );
			
			// Include shortcodes
			add_action( 'qode_framework_action_before_shortcodes_register', array( $this, 'include_shortcodes' ) );
		}
		
		function extend_options_pool( $options, $type ) {
			if ( $type == 'booked_calendars' ) {
				global $wpdb;
				
				if ( qode_framework_is_installed( 'wpml' ) ) {
					$lang = ICL_LANGUAGE_CODE;
					
					$sql = "SELECT t.term_id AS id, t.name AS name
				    FROM {$wpdb->prefix}terms t
				    INNER JOIN {$wpdb->prefix}term_taxonomy tt ON tt.term_id = t.term_id
				    INNER JOIN {$wpdb->prefix}icl_translations icl_t ON icl_t.element_id = t.term_id
				    WHERE icl_t.element_type = 'tax_booked_custom_calendars'
				    AND icl_t.language_code='$lang'
				    ORDER BY name ASC";
				} else {
					$sql = "SELECT t.term_id AS id, t.name AS name
				    FROM {$wpdb->prefix}terms t
				    INNER JOIN {$wpdb->prefix}term_taxonomy tt ON tt.term_id = t.term_id
				    WHERE tt.taxonomy = 'booked_custom_calendars'
				    ORDER BY name ASC";
				}
				
				$calendars = $wpdb->get_results( $sql );
				
				if ( ! empty( $calendars ) ) {
					foreach ( $calendars as $calendar ) {
						$options[ $calendar->id ] = $calendar->name;
					}
				}
			}
			
			return $options;
		}
		
		function include_shortcodes() {
			foreach ( glob( PLAMEN_CORE_PLUGINS_PATH . '/booked/shortcodes/*/include.php' ) as $shortcode ) {
				include_once $shortcode;
			}
		}
	}
	
	PlamenCoreBooked::get_instance();
}