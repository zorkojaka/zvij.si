<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/custom-font/custom-font.php';

foreach ( glob( PLAMEN_CORE_SHORTCODES_PATH . '/custom-font/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}