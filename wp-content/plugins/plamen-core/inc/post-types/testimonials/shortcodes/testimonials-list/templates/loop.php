<?php if ( $query_result->have_posts() ) {  $i = 1; $j = 1; ?>
    <div class="qodef-grid qodef-layout--template">
        <div class="qodef-grid-inner">
            <div class="qodef-grid-item qodef-col--3">
            <?php while ( $query_result->have_posts() ) : $query_result->the_post();
                $params['itemnum'] = $i;
                plamen_core_list_sc_template_part( 'post-types/testimonials/shortcodes/testimonials-list', 'post-info/image', '', $params ); ?>
            <?php
            $i++;

            endwhile; // End of the loop.

            wp_reset_postdata();
            ?>
            </div>
            <div class="qodef-e-content qodef-grid-item qodef-col--9 qodef-m-testimonials-swiper" <?php qode_framework_inline_attr( $slider_attr, 'data-options' ); ?>>
                <div class="swiper-wrapper">
                <?php while ( $query_result->have_posts() ) : $query_result->the_post();
                    $params['itemnum'] = $j;
                    plamen_core_list_sc_template_part( 'post-types/testimonials/shortcodes/testimonials-list', 'layouts/' . $layout, '', $params );

                    $j++;

                endwhile; // End of the loop.

                wp_reset_postdata(); ?>
                </div>

                <?php if ( $slider_pagination !== 'no' ) { ?>
                    <div class="swiper-pagination"></div>
                <?php } ?>
            </div>
        </div>
    </div>
<?php } else {
	// Include global posts not found
	plamen_core_theme_template_part( 'content', 'templates/parts/posts-not-found' );
}

wp_reset_postdata();