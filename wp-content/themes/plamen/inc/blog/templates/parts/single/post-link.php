<article <?php post_class( 'qodef-blog-item qodef-e' ); ?>>
	<div class="qodef-e-inner">
		<?php
		// Include post format part
		plamen_template_part( 'blog', 'templates/parts/post-format/link' ); ?>
    </div>
    <div class="qodef-e-content">
        <div class="qodef-e-info qodef-info--top">
            <?php
            // Include post date info
            plamen_template_part( 'blog', 'templates/parts/post-info/date' );
            // Include post category info
            plamen_template_part( 'blog', 'templates/parts/post-info/category' );
            ?>
        </div>
        <div class="qodef-e-text">
            <?php
            // Include post title
            plamen_template_part( 'blog', 'templates/parts/post-info/title' );

            // Include post content
            the_content();

            // Hook to include additional content after blog single content
            do_action( 'plamen_action_after_blog_single_content' );
            ?>
        </div>
        <div class="qodef-e-info qodef-info--bottom">
            <div class="qodef-e-info-left">
                <?php
                // Include post tags info
                plamen_template_part( 'blog', 'templates/parts/post-info/tags' );
                ?>
            </div>
            <div class="qodef-e-info-right">
                <?php
                // Include post tags info
                plamen_template_part( 'blog', 'templates/parts/post-info/social-share' );
                ?>
            </div>
        </div>
    </div>
</article>