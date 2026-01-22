<?php

if ( ! function_exists( 'plamen_core_manage_clients_custom_columns' ) ) {
	function plamen_core_manage_clients_custom_columns( $columns ) {
		$columns['logo_image'] = esc_html__( 'Logo Image', 'plamen-core' );
		
		return $columns;
	}
	
	add_filter( 'manage_clients_posts_columns', 'plamen_core_manage_clients_custom_columns' );
}

if ( ! function_exists( 'plamen_core_manage_clients_custom_columns_data' ) ) {
	function plamen_core_manage_clients_custom_columns_data( $column, $post_id ) {
		switch ( $column ) {
			case 'logo_image':
				$client_image = get_post_meta( $post_id, 'qodef_logo_image', true );
				echo wp_get_attachment_image( $client_image, 'thumbnail' );
				break;
		}
	}
	
	add_action( 'manage_clients_posts_custom_column', 'plamen_core_manage_clients_custom_columns_data', 10, 2 );
}
