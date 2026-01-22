<?php

if ( ! function_exists( 'plamen_core_add_social_text_widget' ) ) {
    /**
     * Function that add widget into widgets list for registration
     *
     * @param $widgets array
     *
     * @return array
     */
    function plamen_core_add_social_text_widget( $widgets ) {
        $widgets[] = 'PlamenCoreSocialTextWidget';

        return $widgets;
    }

    add_filter( 'plamen_core_filter_register_widgets', 'plamen_core_add_social_text_widget' );
}

if ( class_exists( 'QodeFrameworkWidget' ) ) {
    class PlamenCoreSocialTextWidget extends QodeFrameworkWidget {

        public function map_widget() {
            $this->set_base( 'plamen_core_social_text' );
            $this->set_name( esc_html__( 'Plamen Social Text', 'plamen-core' ) );
            $this->set_description( esc_html__( 'Add social text links into widget areas', 'plamen-core' ) );
            $this->set_widget_option(
                array(
                    'field_type' => 'text',
                    'name'       => 'widget_title',
                    'title'      => esc_html__( 'Title', 'plamen-core' )
                )
            );
            for ( $n = 1; $n <= 4; $n ++ ) {
                $this->set_widget_option(
                    array(
                        'field_type' => 'text',
                        'name'       => 'social_text_' . $n,
                        'title'      => sprintf( esc_html__( 'Social Text %s', 'plamen-core' ), $n )
                    )
                );
                $this->set_widget_option(
                    array(
                        'field_type' => 'text',
                        'name'       => 'social_link_' . $n,
                        'title'      => sprintf( esc_html__( 'Social Link %s', 'plamen-core' ), $n )
                    )
                );
                $this->set_widget_option(
                    array(
                        'field_type' => 'select',
                        'name'       => 'social_link_target_' . $n,
                        'title'      => sprintf( esc_html__( 'Social Link Target %s', 'plamen-core' ), $n ),
                        'options'    => array(
                            '_self'  => esc_html__( 'Same Window', 'plamen-core' ),
                            '_blank' => esc_html__( 'New Window', 'plamen-core' ),
                        )
                    )
                );
            }
            $this->set_widget_option(
                array(
                    'field_type' => 'text',
                    'name'       => 'text_size',
                    'title'      => esc_html__( 'Text Size', 'plamen-core' )
                )
            );
            $this->set_widget_option(
                array(
                    'field_type' => 'color',
                    'name'       => 'text_color',
                    'title'      => esc_html__( 'Text Color', 'plamen-core' )
                )
            );
            $this->set_widget_option(
                array(
                    'field_type' => 'color',
                    'name'       => 'widget_background_color',
                    'title'      => esc_html__( 'Widget Background Color', 'plamen-core' )
                )
            );
            $this->set_widget_option(
                array(
                    'field_type' => 'text',
                    'name'       => 'widget_margin',
                    'title'      => esc_html__( 'Widget Margin', 'plamen-core' ),
                    'description' => 'Insert margin in top right bottom left (e.g. 10px 5px 10px 5px)'
                )
            );
        }

        public function render( $atts ) {
            $widget_styles = array();
            $text_styles   = array();

            if ( ! empty( $atts[ 'text_size' ] ) ) {
                if ( qode_framework_string_ends_with_typography_units( $atts[ 'text_size' ] ) ) {
                    $text_styles[] = 'font-size: ' . $atts[ 'text_size' ];
                } else {
                    $text_styles[] = 'font-size: ' . intval( $atts[ 'text_size' ] ) . 'px';
                }
            }

            if ( ! empty( $atts[ 'text_color' ] ) ) {
                $text_styles[] = 'color: ' . $atts[ 'text_color' ] . ';';
            }

            if ( ! empty( $atts[ 'widget_background_color' ] ) ) {
                $widget_styles[] = 'background-color: ' . $atts[ 'widget_background_color'] . ';';
            }

            if ( ! empty( $atts[ 'widget_margin' ] ) ) {
                $widget_styles[] = 'margin: ' . $atts[ 'widget_margin'] . ';';
            }

            echo '<div ' . qode_framework_get_inline_style( $widget_styles ) . ' class="widget qodef-social-text">';

            for ( $n = 1; $n <= 4; $n ++ ) {
                $text   = ! empty( $atts[ 'social_text_' . $n ] ) ? $atts[ 'social_text_' . $n ] : '';
                $link   = ! empty( $atts[ 'social_link_' . $n ] ) ? $atts[ 'social_link_' . $n ] : '#';
                $target = ! empty( $atts[ 'social_link_target_' . $n ] ) ? $atts[ 'social_link_target_' . $n ] : '_self';

                if ( ! empty( $text ) ) {
                    echo '<a ' . qode_framework_get_inline_style($text_styles) . ' href="' . esc_url($link) . '" target="' . esc_attr($target) . '" class="qodef-social-text-link">' . esc_html($text) . '</a>';
                }
            }

            echo '</div>';
        }
    }
}