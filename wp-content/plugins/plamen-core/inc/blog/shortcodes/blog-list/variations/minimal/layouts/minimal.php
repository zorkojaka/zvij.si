<article <?php post_class( $item_classes ); ?>>
	<div class="qodef-e-inner">
        <div class="qodef-e-info qodef-info--top">
            <?php
            // Include post date info
            plamen_core_template_part( 'blog/shortcodes/blog-list', 'templates/post-info/date' );

            // Include post category
            plamen_core_template_part( 'blog/shortcodes/blog-list', 'templates/post-info/category' );
            ?>
        </div>
		<?php plamen_core_template_part( 'blog/shortcodes/blog-list', 'templates/post-info/title', '', $params ); ?>
		<?php plamen_core_template_part( 'blog/shortcodes/blog-list', 'templates/post-info/excerpt', '', $params ); ?>
		<div class="qodef-e-info qodef-info--bottom">
			<?php
			// Include post date info
			plamen_core_template_part( 'blog/shortcodes/blog-list', 'templates/post-info/read-more' );
			?>
		</div>
	</div>
</article>