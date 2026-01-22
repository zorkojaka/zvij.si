<?php if ( is_array( $post_ratings ) && count( $post_ratings ) ) { ?>
	<?php $average_rating_total = plamen_core_get_total_average_rating( $post_ratings ); ?>
	<div class="qodef-reviews-list-info qodef-reviews-per-criteria">
		<div class="qodef-item-reviews-display-wrapper clearfix">
			<?php if ( ! empty( $title ) ) { ?>
				<h3 class="qodef-item-review-title"><?php echo esc_html( $title ); ?></h3>
			<?php } ?>
			
			<?php if ( ! empty( $subtitle ) ) { ?>
				<p class="qodef-item-review-subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php } ?>
			
			<div class="qodef-grid qodef-layout--template">
				<div class="qodef-grid-inner clear">
					<div class="qodef-grid-item qodef-col--3">
						<div class="qodef-item-reviews-average-wrapper">
							<div class="qodef-item-reviews-average-rating">
								<?php echo esc_html( plamen_core_reviews_format_rating_output( $average_rating_total ) ); ?>
							</div>
							<div class="qodef-item-reviews-verbal-description">
                            <span class="qodef-item-reviews-rating-icon">
                                <?php echo plamen_core_reviews_get_icon_for_rating( $average_rating_total ); ?>
                            </span>
								<span class="qodef-item-reviews-rating-description">
                                <?php echo esc_html( plamen_core_reviews_get_description_for_rating( $average_rating_total ) ); ?>
                            </span>
							</div>
						</div>
					</div>
					<div class="qodef-grid-item qodef-col--9">
						<div class="qodef-rating-percentage-wrapper">
							<?php
							foreach ( $post_ratings as $rating ) {
								$percentage = plamen_core_post_average_rating_per_criteria( $rating );
								if ( $percentage == 0 ) {
									$percentage_alt = 0.0001;
								} else {
									$percentage_alt = $percentage;
								}
								echo do_shortcode( '[plamen_core_progress_bar layout="line" number="' . esc_attr( $percentage_alt ) . '" title="' . esc_attr( $rating['label'] ) . '"]' );
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php }