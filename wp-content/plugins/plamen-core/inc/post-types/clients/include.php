<?php

include_once PLAMEN_CORE_CPT_PATH . '/clients/helper.php';

foreach ( glob( PLAMEN_CORE_CPT_PATH . '/clients/dashboard/meta-box/*.php' ) as $module ) {
	include_once $module;
}