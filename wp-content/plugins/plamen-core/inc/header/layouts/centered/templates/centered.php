<?php

// Include logo
plamen_core_get_header_logo_image(); ?>

<div class="qodef-centered-header-wrapper">
	<?php
	// Include widget area two
     ?>
    <div class="qodef-widget-holder">
        <?php plamen_core_get_header_widget_area( '', 'two' ); ?>
    </div>
    <?php
	// Include main navigation
	plamen_core_template_part( 'header', 'templates/parts/navigation' );
	
	// Include widget area one
    ?>
    <div class="qodef-widget-holder">
        <?php plamen_core_get_header_widget_area(); ?>
    </div>
</div>