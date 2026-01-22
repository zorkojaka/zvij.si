<?php if ( is_array( $post_ratings ) && count( $post_ratings ) ) {
	$average_rating_total = intval( round( plamen_core_get_total_average_rating( $post_ratings ) ) );
	?>
	<div class="qodef-reviews-list-info qodef-reviews-average-count">
		<span class="qodef-reviews-number">
            <?php echo esc_html( $rating_number ); ?>
        </span>
		<span class="qodef-reviews-label">
            <?php echo esc_html( $rating_label ); ?>
        </span>
		<span class="qodef-stars">
		    <span class="qodef-stars-wrapper-inner">
	            <span class="qodef-stars-items">
	                <?php for ( $i = 1; $i <= 5; $i ++ ) {
	                	if ( $average_rating_total >= $i ) { ?>
			                <i class="fas fa-star" aria-hidden="true"></i>
		                <?php } else { ?>
			                <i class="far fa-star" aria-hidden="true"></i>
		                <?php }
	                } ?>
	            </span>
	        </span>
		</span>
	</div>
<?php } ?>