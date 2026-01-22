<?php

include_once PLAMEN_CORE_INC_PATH . '/header/top-area/top-area.php';
include_once PLAMEN_CORE_INC_PATH . '/header/top-area/helper.php';

foreach ( glob( PLAMEN_CORE_INC_PATH . '/header/top-area/dashboard/*/*.php' ) as $dashboard ) {
	include_once $dashboard;
}