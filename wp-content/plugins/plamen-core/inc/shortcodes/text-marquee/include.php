<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/text-marquee/text-marquee.php';

foreach ( glob( PLAMEN_CORE_INC_PATH . '/shortcodes/text-marquee/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}