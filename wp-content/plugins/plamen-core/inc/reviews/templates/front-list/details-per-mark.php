<div class="qodef-reviews-list-info qodef-reviews-per-mark">
	<?php foreach ( $post_ratings as $rating ) { ?>
		<?php
		$average_rating     = plamen_core_post_average_rating( $rating );
		$rating_count       = $rating['count'];
		$rating_count_label = $rating_count === 1 ? esc_html__( 'Rating', 'plamen-core' ) : esc_html__( 'Ratings', 'plamen-core' );
		$rating_marks       = $rating['marks'];
		?>
		<div class="qodef-grid-row">
			<div class="qodef-grid-col-4">
				<div class="qodef-reviews-number-wrapper">
					<span class="qodef-reviews-number"><?php echo esc_html( $average_rating ); ?></span>
					<span class="qodef-stars-wrapper">
                        <span class="qodef-stars">
                            <?php
                            for ( $i = 1; $i <= $average_rating; $i ++ ) { ?>
	                            <i class="fa fa-star" aria-hidden="true"></i>
                            <?php } ?>
                        </span>
                        <span class="qodef-reviews-count">
                            <?php echo esc_html__( 'Rated', 'plamen-core' ) . ' ' . $average_rating . ' ' . esc_html__( 'out of', 'plamen-core' ) . ' ' . $rating_count . ' ' . $rating_count_label; ?>
                        </span>
                    </span>
				</div>
			</div>
			<div class="qodef-grid-col-8">
				<div class="qodef-rating-percentage-wrapper">
					<?php
					foreach ( $rating_marks as $item => $value ) {
						$percentage = $rating_count == 0 ? 0 : round( ( $value / $rating_count ) * 100 );
						echo do_shortcode( '[plamen_core_progress_bar layout="line" number="' . esc_attr( $percentage ) . '" title="' . esc_attr( $item ) . esc_html__( ' stars', 'plamen-core' ) . '"]' );
					}
					?>
				</div>
			</div>
		</div>
	<?php } ?>
</div>
