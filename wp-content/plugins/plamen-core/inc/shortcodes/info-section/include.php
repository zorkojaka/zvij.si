<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/info-section/info-section.php';

foreach ( glob( PLAMEN_CORE_SHORTCODES_PATH . '/info-section/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}