<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/call-to-action/call-to-action.php';

foreach ( glob( PLAMEN_CORE_SHORTCODES_PATH . '/call-to-action/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}