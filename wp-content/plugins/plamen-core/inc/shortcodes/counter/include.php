<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/counter/counter.php';

foreach ( glob( PLAMEN_CORE_SHORTCODES_PATH . '/counter/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}