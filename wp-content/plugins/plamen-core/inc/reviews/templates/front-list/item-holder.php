<li>
	<div class="<?php echo esc_attr( $comment_class ); ?>">
		<?php if ( ! $is_pingback_comment ) { ?>
			<div class="qodef-comment-image"> <?php echo qode_framework_get_image_html_from_src( get_avatar( $comment, 'thumbnail' ) ); ?> </div>
		<?php } ?>
		<div class="qodef-comment-text">
			<div class="qodef-comment-info">
				<h5 class="qodef-comment-name vcard">
					<?php comment_author_link(); ?>
				</h5>
				<div class="qodef-review-rating">
					<?php foreach ( $rating_criteria as $rating ) { ?>
						<?php if ( ! isset( $rating['show'] ) || ( isset( $rating['show'] ) && $rating['show'] ) ) { ?>
							<span class="qodef-rating-inner">
                                <?php if ( isset( $rating['label'] ) && ! empty( $rating['label'] ) ) { ?>
	                                <label><?php echo esc_html( $rating['label'] ); ?></label>
                                <?php } ?>
								<span class="qodef-rating-value">
                                    <?php
                                    $review_rating = get_comment_meta( $comment->comment_ID, $rating['key'], true );

                                    echo plamen_core_reviews_get_rating_html( '', $review_rating, 0 );
                                    
                                    ?>
                                </span>
                            </span>
						<?php } ?>
					<?php } ?>
				</div>
			</div>
			<?php if ( ! $is_pingback_comment ) { ?>
				<div class="qodef-text-holder" id="comment-<?php comment_ID(); ?>">
					<div class="qodef-review-title">
						<span><?php echo esc_html( $review_title ); ?></span>
					</div>
					<?php comment_text(); ?>
				</div>
			<?php } ?>
		</div>
	</div>
	<!-- li is closed by wordpress after comment rendering -->