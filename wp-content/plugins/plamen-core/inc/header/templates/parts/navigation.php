<?php if ( has_nav_menu( 'main-navigation' ) ) : ?>
	<nav class="qodef-header-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Top Menu', 'plamen-core' ); ?>">
		<?php wp_nav_menu( array(
			'theme_location'    => 'main-navigation',
			'container'         => '',
			'link_before'       => '<span class="qodef-menu-item-text">',
			'link_after'        => '</span>',
			'walker'            => new PlamenCoreRootMainMenuWalker()
		) ); ?>
	</nav>
<?php endif; ?>