<?php

include_once PLAMEN_CORE_CPT_PATH . '/testimonials/shortcodes/testimonials-list/testimonials-list.php';

foreach ( glob( PLAMEN_CORE_CPT_PATH . '/testimonials/shortcodes/testimonials-list/variations/*/include.php' ) as $variation ) {
	include_once $variation;
}