<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/stacked-images/stacked-images.php';

foreach ( glob( PLAMEN_CORE_SHORTCODES_PATH . '/stacked-images/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}