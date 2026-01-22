<?php
if ( ! function_exists( 'plamen_core_add_import_sub_page_to_list' ) ) {
	function plamen_core_add_import_sub_page_to_list( $sub_pages ) {
		$sub_pages[] = 'PlamenCoreImportPage';
		return $sub_pages;
	}
	
	add_filter( 'plamen_core_filter_add_welcome_sub_page', 'plamen_core_add_import_sub_page_to_list', 11 );
}

if ( class_exists( 'PlamenCoreSubPage' ) ) {
	class PlamenCoreImportPage extends PlamenCoreSubPage {
		
		public function __construct() {
			parent::__construct();
		}
		
		public function add_sub_page() {
			$this->set_base( 'import' );
			$this->set_title( esc_html__( 'Import', 'plamen-core' ) );
			$this->set_atts( $this->set_atributtes() );
		}
		
		public function set_atributtes() {
			$params = array();
			
			$iparams = PlamenCoreDashboard::get_instance()->get_import_params();
			if ( is_array( $iparams ) && isset( $iparams['submit'] ) ) {
				$params['submit'] = $iparams['submit'];
			}
			
			return $params;
		}
	}
}