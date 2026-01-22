<div class="qodef-fullscreen-search-holder qodef-m">
    <div class="qodef-fullscreen-search-overlay"></div>
	<?php plamen_core_get_opener_icon_html( array(
		'option_name'  => 'search',
		'custom_class' => 'qodef-m-close',
		'custom_icon'  => 'search'
	), false, true ); ?>
	<div class="qodef-m-inner">
		<form action="<?php echo esc_url( home_url( '/' ) ); ?>" class="qodef-m-form" method="get">
			<input type="text" placeholder="<?php esc_attr_e( 'Type here...', 'plamen-core' ); ?>" name="s" class="qodef-m-form-field" autocomplete="off" required/>
		</form>
	</div>
</div>