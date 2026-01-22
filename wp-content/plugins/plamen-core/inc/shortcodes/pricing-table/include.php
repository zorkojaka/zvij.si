<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/pricing-table/pricing-table.php';

foreach ( glob( PLAMEN_CORE_SHORTCODES_PATH . '/pricing-table/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}