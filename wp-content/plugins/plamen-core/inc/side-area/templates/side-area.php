<?php if ( is_active_sidebar( 'qodef-side-area' ) || is_active_sidebar( 'qodef-side-area-bottom' ) ) { ?>
	<div id="qodef-side-area" <?php qode_framework_class_attribute( $classes ); ?>>
		<?php plamen_core_get_opener_icon_html( array(
			'option_name' => 'side_area',
			'custom_id'   => 'qodef-side-area-close'
		), false, true ); ?>
		<div id="qodef-side-area-inner">
			<?php dynamic_sidebar( 'qodef-side-area' ); ?>
		</div>
        <div id="qodef-side-area-bottom">
            <?php dynamic_sidebar( 'qodef-side-area-bottom' ); ?>
        </div>
	</div>
<?php } ?>