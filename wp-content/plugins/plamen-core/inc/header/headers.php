<?php

class PlamenCoreHeaders {
	private static $instance;
	private $layout_meta;
	private $layouts;
	private $header_object;

	public function __construct() {

		// Includes header layouts
		$this->include_elements();

		// Set module variables
		add_action( 'wp', array( $this, 'set_variables' ) ); // wp hook is set because we need to wait global wp_query object to instance in order to get page id

		// Overrides default header template of theme
		add_action( 'wp', array( $this, 'render_template' ) );

		// Includes header scroll appearance template
		add_action( 'plamen_action_after_page_header_inner', array( $this, 'scroll_appearance' ) );

		// Add module body classes
		add_filter( 'body_class', array( $this, 'add_body_classes' ) );

		//Add widget areas
		add_action( 'widgets_init', array( $this, 'add_header_widget_areas' ) );
	}
	
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	public function get_layout_meta() {
		return $this->layout_meta;
	}

	public function set_layout_meta( $layout_meta ) {
		$this->layout_meta = $layout_meta;
	}

	public function get_layouts() {
		return $this->layouts;
	}

	public function set_layouts( $layouts ) {
		$this->layouts = $layouts;
	}

	public function get_header_object() {
		return $this->header_object;
	}

	public function set_header_object( $header_object ) {
		$this->header_object = $header_object;
	}

	function include_elements() {

		foreach ( glob( PLAMEN_CORE_INC_PATH . '/header/dashboard/*/*.php' ) as $admin ) {
			include_once $admin;
		}
		
		foreach ( glob( PLAMEN_CORE_INC_PATH . '/header/layouts/*/include.php' ) as $layout ) {
			include_once $layout;
		}
		
		foreach ( glob( PLAMEN_CORE_INC_PATH . '/header/*/include.php' ) as $header_part ) {
			include_once $header_part;
		}
	}

	function set_variables() {
		$layout_meta = plamen_core_get_post_value_through_levels( 'qodef_header_layout' );
		$layouts     = apply_filters( 'plamen_core_filter_register_header_layouts', $header_layouts_option = array() );

		$this->set_layout_meta( $layout_meta );
		$this->set_layouts( $layouts );

		if ( ! empty( $layout_meta ) && ! empty( $layouts ) ) {
			foreach ( $layouts as $key => $value ) {
				if ( $layout_meta === $key ) {

					$this->set_header_object( $value::get_instance() );
				}
			}
		}
	}

	function load_template() {
		// template is properly escaped inside html file
		echo $this->get_header_object()->load_template(); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	}

	function render_template() {
		$header_object = $this->get_header_object();

		if ( ! empty( $header_object ) ) {
			$template_hook = $header_object->overriding_whole_header ? 'plamen_filter_header_template' : 'plamen_filter_header_content_template';

			add_filter( $template_hook, array( $this, 'load_template' ), 11 );
		}
	}

	function add_body_classes( $classes ) {
		$header_layout            = plamen_core_get_post_value_through_levels( 'qodef_header_layout' );
		$header_skin              = plamen_core_get_post_value_through_levels( 'qodef_header_skin' );
		$header_scroll_appearance = plamen_core_get_post_value_through_levels( 'qodef_header_scroll_appearance' );

		$classes[] = ( ! empty( $header_skin ) && $header_skin !== 'none' ) ? 'qodef-header--' . $header_skin : '';
		$classes[] = ! empty( $header_layout ) ? 'qodef-header--' . $header_layout : '';
		$classes[] = ! empty( $header_scroll_appearance ) ? 'qodef-header-appearance--' . $header_scroll_appearance : '';

		return $classes;
	}

	function scroll_appearance() {
		$header_object = $this->get_header_object();

		if ( ! empty( $header_object ) ) {
			$appearance_type = plamen_core_get_post_value_through_levels( 'qodef_header_scroll_appearance' );

			if ( file_exists( PLAMEN_CORE_HEADER_LAYOUTS_PATH . '/' . $header_object->slug . '/templates/' . $appearance_type . '.php' ) ) {
				$scroll_appearance_layout = 'layouts/' . $header_object->slug;
			} else {
				$scroll_appearance_layout = 'scroll-appearance/' . $appearance_type;
			}

			plamen_core_template_part( 'header/' . $scroll_appearance_layout, 'templates/' . $appearance_type, '', array() );
		}
	}

	function add_header_widget_areas() {
		register_sidebar(
			array(
				'id'            => 'qodef-header-widget-area-one',
				'name'          => esc_html__( 'Header - Area One', 'plamen-core' ),
				'description'   => esc_html__( 'Widgets added here will appear in header widget area one', 'plamen-core' ),
				'before_widget' => '<div id="%1$s" class="widget %2$s qodef-header-widget-area-one" data-area="header-widget-one">',
				'after_widget'  => '</div>'
			)
		);

		register_sidebar(
			array(
				'id'            => 'qodef-header-widget-area-two',
				'name'          => esc_html__( 'Header - Area Two', 'plamen-core' ),
				'description'   => esc_html__( 'Widgets added here will appear in header widget area two', 'plamen-core' ),
				'before_widget' => '<div id="%1$s" class="widget %2$s qodef-header-widget-area-two" data-area="header-widget-two">',
				'after_widget'  => '</div>'
			)
		);

		// Hooks that allows you to add additional header widgets area
		do_action( 'plamen_core_action_additional_header_widgets_area' );
	}
}

PlamenCoreHeaders::get_instance();