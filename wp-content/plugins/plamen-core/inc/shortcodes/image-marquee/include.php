<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/image-marquee/image-marquee.php';

foreach ( glob( PLAMEN_CORE_INC_PATH . '/shortcodes/image-marquee/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}