<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/countdown/countdown.php';

foreach ( glob( PLAMEN_CORE_SHORTCODES_PATH . '/countdown/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}