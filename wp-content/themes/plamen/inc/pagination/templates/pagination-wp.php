<?php if ( get_the_posts_pagination() !== '' ): ?>

    <div class="qodef-m-pagination qodef--wp">
		<?php
		// Load posts pagination (in order to override template use navigation_markup_template filter hook)
		the_posts_pagination( array(
			'prev_text'          => plamen_get_svg_icon('arrow'),
			'next_text'          => plamen_get_svg_icon('arrow'),
			'before_page_number' => '0',
		) ); ?>
    </div>

<?php endif; ?>