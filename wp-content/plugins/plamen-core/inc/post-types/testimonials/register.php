<?php

if ( ! function_exists( 'plamen_core_register_testimonials_for_meta_options' ) ) {
	function plamen_core_register_testimonials_for_meta_options( $post_types ) {
		$post_types[] = 'testimonials';
		return $post_types;
	}
	
	add_filter( 'qode_framework_filter_meta_box_save', 'plamen_core_register_testimonials_for_meta_options' );
	add_filter( 'qode_framework_filter_meta_box_remove', 'plamen_core_register_testimonials_for_meta_options' );
}

if ( ! function_exists( 'plamen_core_add_testimonials_custom_post_type' ) ) {
	/**
	 * Function that adds testimonials custom post type
	 *
	 * @param array $cpts
	 *
	 * @return array
	 */
	function plamen_core_add_testimonials_custom_post_type( $cpts ) {
		$cpts[] = 'PlamenCoreTestimonialsCPT';
		
		return $cpts;
	}
	
	add_filter( 'plamen_core_filter_register_custom_post_types', 'plamen_core_add_testimonials_custom_post_type' );
}

if ( class_exists( 'QodeFrameworkCustomPostType' ) ) {
	class PlamenCoreTestimonialsCPT extends QodeFrameworkCustomPostType {
		
		public function map_post_type() {
			$name = esc_html__( 'Testimonials', 'plamen-core' );
			$this->set_base( 'testimonials' );
			$this->set_menu_position( 10 );
			$this->set_menu_icon( 'dashicons-format-status' );
			$this->set_slug( 'testimonials' );
			$this->set_name( $name );
			$this->set_path( PLAMEN_CORE_CPT_PATH . '/testimonials' );
			$this->set_labels( array(
				'name'          => esc_html__( 'Plamen Testimonials', 'plamen-core' ),
				'singular_name' => esc_html__( 'Testimonial', 'plamen-core' ),
				'add_item'      => esc_html__( 'New Testimonial', 'plamen-core' ),
				'add_new_item'  => esc_html__( 'Add New Testimonial', 'plamen-core' ),
				'edit_item'     => esc_html__( 'Edit Testimonial', 'plamen-core' )
			) );
			$this->set_public( false );
			$this->set_archive( false );
			$this->set_supports( array(
				'title',
				'thumbnail'
			) );
			$this->add_post_taxonomy( array(
				'base'          => 'testimonials-category',
				'slug'          => 'testimonials-category',
				'singular_name' => esc_html__( 'Category', 'plamen-core' ),
				'plural_name'   => esc_html__( 'Categories', 'plamen-core' ),
			) );
		}
		
	}
}