<?php

include_once PLAMEN_CORE_CPT_PATH . '/team/shortcodes/team-list/team-list.php';

foreach ( glob( PLAMEN_CORE_CPT_PATH . '/team/shortcodes/team-list/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}