<article <?php post_class( 'qodef-blog-item qodef-e' ); ?>>
	<div class="qodef-e-inner">
		<?php
		// Include post format part
		plamen_template_part( 'blog', 'templates/parts/post-format/quote' ); ?>
        <div class="qodef-e-info qodef-info--bottom">
            <?php
            // Include post date info
            plamen_template_part( 'blog', 'templates/parts/post-info/date' );
            // Include post category info
            plamen_template_part( 'blog', 'templates/parts/post-info/category' );
            ?>
        </div>
	</div>
</article>