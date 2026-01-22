<?php

class PlamenCoreElementorSectionHandler {
    private static $instance;
    public $sections = array();

    public function __construct() {
        add_action( 'elementor/element/section/_section_responsive/after_section_end', array( $this, 'render_parallax_options' ), 10, 2 );
        add_action( 'elementor/element/section/_section_responsive/after_section_end', array( $this, 'render_offset_options' ), 10, 2 );
        add_action( 'elementor/element/section/_section_responsive/after_section_end', array( $this, 'render_grid_options' ), 10, 2 );
        add_action( 'elementor/frontend/section/before_render', array( $this, 'section_before_render' ) );
        add_action( 'elementor/frontend/element/before_render', array( $this, 'section_before_render' ) );
        add_action( 'elementor/frontend/before_enqueue_styles', array( $this, 'enqueue_styles' ), 9 );
        add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ), 9 );
    }

    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function render_parallax_options( $section, $args ) {
        $section->start_controls_section(
            'qodef_parallax',
            [
                'label' => esc_html__( 'Plamen Parallax', 'plamen-core' ),
                'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
            ]
        );

        $section->add_control(
            'qodef_parallax_type',
            [
                'label'       => esc_html__( 'Enable Parallax', 'plamen-core' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'default'     => 'no',
                'options'     => [
                    'no'       => esc_html__( 'No', 'plamen-core' ),
                    'parallax' => esc_html__( 'Yes', 'plamen-core' ),
                ],
                'render_type' => 'template',
            ]
        );

        $section->add_control(
            'qodef_parallax_image',
            [
                'label'       => esc_html__( 'Parallax Background Image', 'plamen-core' ),
                'type'        => \Elementor\Controls_Manager::MEDIA,
                'condition'   => [
                    'qodef_parallax_type' => 'parallax'
                ],
                'render_type' => 'template',
            ]
        );

        $section->end_controls_section();
    }

    public function render_offset_options( $section, $args ) {
        $section->start_controls_section(
            'qodef_offset',
            [
                'label' => esc_html__( 'Plamen Offset Image', 'plamen-core' ),
                'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
            ]
        );

        $section->add_control(
            'qodef_offset_type',
            [
                'label'       => esc_html__( 'Enable Offset Image', 'plamen-core' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'default'     => 'no',
                'options'     => [
                    'no'     => esc_html__( 'No', 'plamen-core' ),
                    'offset' => esc_html__( 'Yes', 'plamen-core' ),
                ],
                'render_type' => 'template',
            ]
        );

        $section->add_control(
            'qodef_offset_image',
            [
                'label'       => esc_html__( 'Offset Image', 'plamen-core' ),
                'type'        => \Elementor\Controls_Manager::MEDIA,
                'condition'   => [
                    'qodef_offset_type' => 'offset'
                ],
                'render_type' => 'template',
            ]
        );

        $section->add_control(
            'qodef_offset_top',
            [
                'label'       => esc_html__( 'Offset Image Top Position', 'plamen-core' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '50%',
                'condition'   => [
                    'qodef_offset_type' => 'offset'
                ],
                'render_type' => 'template',
            ]
        );

        $section->add_control(
            'qodef_offset_left',
            [
                'label'       => esc_html__( 'Offset Image Left Position', 'plamen-core' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '50%',
                'condition'   => [
                    'qodef_offset_type' => 'offset'
                ],
                'render_type' => 'template',
            ]
        );

        $section->end_controls_section();
    }

    public function render_grid_options( $section, $args ) {
        $section->start_controls_section(
            'qodef_grid_row',
            [
                'label' => esc_html__( 'Plamen Grid', 'plamen-core' ),
                'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
            ]
        );

        $section->add_control(
            'qodef_enable_grid_row',
            [
                'label'        => esc_html__( 'Make this row "In Grid"', 'plamen-core' ),
                'type'         => \Elementor\Controls_Manager::SELECT,
                'default'      => 'no',
                'options'      => [
                    'no'   => esc_html__( 'No', 'plamen-core' ),
                    'grid' => esc_html__( 'Yes', 'plamen-core' ),
                ],
                'prefix_class' => 'qodef-elementor-content-'
            ]
        );

        $section->end_controls_section();
    }

    public function section_before_render( $widget ) {
        $data     = $widget->get_data();
        $type     = isset( $data['elType'] ) ? $data['elType'] : 'section';
        $settings = $data['settings'];

        if ( 'section' === $type ) {
            if ( isset( $settings['qodef_parallax_type'] ) && $settings['qodef_parallax_type'] == 'parallax' ) {
                $parallax_type  = $widget->get_settings_for_display( 'qodef_parallax_type' );
                $parallax_image = $widget->get_settings_for_display( 'qodef_parallax_image' );

                if ( ! in_array( $data['id'], $this->sections ) ) {
                    $this->sections[ $data['id'] ][] = array(
                        'parallax_type'  => $parallax_type,
                        'parallax_image' => $parallax_image
                    );
                }
            }

            if ( isset( $settings['qodef_offset_type'] ) && $settings['qodef_offset_type'] == 'offset' ) {
                $offset_type  = $widget->get_settings_for_display( 'qodef_offset_type' );
                $offset_image = $widget->get_settings_for_display( 'qodef_offset_image' );
                $offset_top   = $widget->get_settings_for_display( 'qodef_offset_top' );
                $offset_left  = $widget->get_settings_for_display( 'qodef_offset_left' );

                if ( ! in_array( $data['id'], $this->sections ) ) {
                    $this->sections[ $data['id'] ][] = array(
                        'offset_type'  => $offset_type,
                        'offset_image' => $offset_image,
                        'offset_top'   => $offset_top,
                        'offset_left'  => $offset_left,
                    );
                }
            }
        }
    }

    public function enqueue_styles() {
        wp_enqueue_style( 'plamen-core-elementor', PLAMEN_CORE_PLUGINS_URL_PATH . '/elementor/assets/css/elementor.min.css' );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( 'plamen-core-elementor', PLAMEN_CORE_PLUGINS_URL_PATH . '/elementor/assets/js/elementor.js', array( 'jquery', 'elementor-frontend' ) );

        $elementor_global_vars = array(
            'elementorSectionHandler' => $this->sections,
        );

        wp_localize_script( 'plamen-core-elementor', 'qodefElementorGlobal', array(
            'vars' => $elementor_global_vars,
        ) );
    }
}

if ( ! function_exists( 'plamen_core_init_elementor_section_handler' ) ) {
    /**
     * Function that initialize main page builder handler
     */
    function plamen_core_init_elementor_section_handler() {
        PlamenCoreElementorSectionHandler::get_instance();
    }

    add_action( 'init', 'plamen_core_init_elementor_section_handler', 1 );
}