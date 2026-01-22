<?php

include_once PLAMEN_CORE_SHORTCODES_PATH . '/item-showcase/item-showcase.php';

foreach ( glob( PLAMEN_CORE_SHORTCODES_PATH . '/item-showcase/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}