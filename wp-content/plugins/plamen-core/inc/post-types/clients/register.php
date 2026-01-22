<?php

if ( ! function_exists( 'plamen_core_register_clients_for_meta_options' ) ) {
	function plamen_core_register_clients_for_meta_options( $post_types ) {
		$post_types[] = 'clients';
		return $post_types;
	}
	
	add_filter( 'qode_framework_filter_meta_box_save', 'plamen_core_register_clients_for_meta_options' );
	add_filter( 'qode_framework_filter_meta_box_remove', 'plamen_core_register_clients_for_meta_options' );
}

if ( ! function_exists( 'plamen_core_add_clients_custom_post_type' ) ) {
	/**
	 * Function that adds clients custom post type
	 *
	 * @param array $cpts
	 *
	 * @return array
	 */
	function plamen_core_add_clients_custom_post_type( $cpts ) {
		$cpts[] = 'PlamenCoreClientsCPT';
		
		return $cpts;
	}
	
	add_filter( 'plamen_core_filter_register_custom_post_types', 'plamen_core_add_clients_custom_post_type' );
}

if ( class_exists( 'QodeFrameworkCustomPostType' ) ) {
	class PlamenCoreClientsCPT extends QodeFrameworkCustomPostType {
		
		public function map_post_type() {
			$name = esc_html__( 'Clients', 'plamen-core' );
			$this->set_base( 'clients' );
			$this->set_menu_position( 10 );
			$this->set_menu_icon( 'dashicons-groups' );
			$this->set_slug( 'clients' );
			$this->set_name( $name );
			$this->set_path( PLAMEN_CORE_CPT_PATH . '/clients' );
			$this->set_labels( array(
				'name'          => esc_html__( 'Plamen Clients', 'plamen-core' ),
				'singular_name' => esc_html__( 'Client', 'plamen-core' ),
				'add_item'      => esc_html__( 'New Client', 'plamen-core' ),
				'add_new_item'  => esc_html__( 'Add New Client', 'plamen-core' ),
				'edit_item'     => esc_html__( 'Edit Client', 'plamen-core' )
			) );
			$this->set_public( false );
			$this->set_archive( false );
			$this->set_supports( array(
				'title',
				'thumbnail'
			) );
			$this->add_post_taxonomy( array(
				'base'          => 'clients-category',
				'slug'          => 'clients-category',
				'singular_name' => esc_html__( 'Category', 'plamen-core' ),
				'plural_name'   => esc_html__( 'Categories', 'plamen-core' ),
			) );
		}
	}
}