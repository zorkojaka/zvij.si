<article <?php post_class( $item_classes ); ?>>
	<div class="qodef-e-inner">
		<?php plamen_core_template_part( 'blog/shortcodes/blog-list', 'templates/post-info/image', '', $params ); ?>
		<div class="qodef-e-content">
            <div class="qodef-e-info qodef-info--top">
                <?php
                // Include post date info
                plamen_core_template_part( 'blog/shortcodes/blog-list', 'templates/post-info/date' );
                ?>
            </div>
			<?php plamen_core_template_part( 'blog/shortcodes/blog-list', 'templates/post-info/title', '', $params ); ?>
		</div>
	</div>
</article>