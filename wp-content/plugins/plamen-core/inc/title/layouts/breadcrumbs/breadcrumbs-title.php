<?php

class PlamenCoreBreadcrumbsTitle extends PlamenCoreTitle {
	private static $instance;
	
	public function __construct() {
		$this->slug = 'breadcrumbs';
	}
	
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
}