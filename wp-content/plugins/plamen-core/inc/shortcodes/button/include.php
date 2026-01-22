<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/button/button.php';

foreach ( glob( PLAMEN_CORE_SHORTCODES_PATH . '/button/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}