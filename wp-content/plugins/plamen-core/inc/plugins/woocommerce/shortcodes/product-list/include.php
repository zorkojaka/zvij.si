<?php

include_once PLAMEN_CORE_PLUGINS_PATH . '/woocommerce/shortcodes/product-list/product-list.php';

foreach ( glob( PLAMEN_CORE_PLUGINS_PATH . '/woocommerce/shortcodes/product-list/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}