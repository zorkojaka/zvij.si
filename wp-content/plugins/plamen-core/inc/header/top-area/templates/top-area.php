<?php if ( $show_header_area ) { ?>
	<div id="qodef-top-area">
		
			<?php
			// Include widget area top left
			if ( is_active_sidebar( 'qodef-top-area-left' ) ) { ?>
				<div class="qodef-widget-holder qodef-top-area-left">
					<?php plamen_core_get_header_widget_area( 'top-area-left' ); ?>
				</div>
			<?php } ?>

            <?php
            // Include widget area top left
            if ( is_active_sidebar( 'qodef-top-area-center' ) ) { ?>
                <div class="qodef-widget-holder qodef-top-area-center">
                    <?php plamen_core_get_header_widget_area( 'top-area-center' ); ?>
                </div>
            <?php } ?>
		
			<?php
			// Include widget area top right
			if ( is_active_sidebar( 'qodef-top-area-right' ) ) { ?>
				<div class="qodef-widget-holder qodef-top-area-right">
					<?php plamen_core_get_header_widget_area( 'top-area-right' ); ?>
				</div>
			<?php } ?>
		
		<?php do_action( 'plamen_core_action_after_top_area' ); ?>
	</div>
<?php } ?>