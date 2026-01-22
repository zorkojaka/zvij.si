<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/banner/banner.php';

foreach ( glob( PLAMEN_CORE_INC_PATH . '/shortcodes/banner/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}