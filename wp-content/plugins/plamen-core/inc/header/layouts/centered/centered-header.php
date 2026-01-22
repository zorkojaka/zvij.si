<?php

class CenteredHeader extends PlamenCoreHeader {
	private static $instance;

	public function __construct() {
		$this->set_slug( 'centered' );
        $this->search_layout         = 'fullscreen';
		$this->default_header_height = 210;

		parent::__construct();
	}
	
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
}