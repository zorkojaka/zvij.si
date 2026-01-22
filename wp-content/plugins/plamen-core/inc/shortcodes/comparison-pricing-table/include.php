<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/comparison-pricing-table/comparison-pricing-table.php';

foreach ( glob( PLAMEN_CORE_SHORTCODES_PATH . '/comparison-pricing-table/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}