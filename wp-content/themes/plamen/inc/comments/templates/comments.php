<div id="qodef-page-comments">
	<?php if ( have_comments() ) {
		$comments_number = get_comments_number();
		?>
		<div id="qodef-page-comments-list" class="qodef-m">
			<h3 class="qodef-m-title"><?php echo sprintf( _n( '%s Comment', '%s Comments', $comments_number, 'plamen' ), $comments_number ); ?></h3>
			<ul class="qodef-m-comments">
				<?php wp_list_comments( array_unique( array_merge( array( 'callback' => 'plamen_get_comments_list_template' ), apply_filters( 'plamen_filter_comments_list_template_callback', array() ) ) ) ); ?>
			</ul>
			
			<?php if ( get_comment_pages_count() > 1 ) { ?>
				<div class="qodef-m-pagination qodef--wp">
					<?php the_comments_pagination( array(
						'prev_text'          => plamen_get_icon( 'arrow_carrot-left', 'elegant-icons', esc_html__( '< Prev', 'plamen' ) ),
						'next_text'          => plamen_get_icon( 'arrow_carrot-right', 'elegant-icons', esc_html__( 'Next >', 'plamen' ) ),
						'before_page_number' => '0',
					) ); ?>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) { ?>
		<p class="qodef-page-comments-not-found"><?php esc_html_e( 'Comments are closed.', 'plamen' ); ?></p>
	<?php } ?>
	
	<div id="qodef-page-comments-form">
		<?php
		$qodef_commenter = wp_get_current_commenter();
		$qodef_req       = get_option( 'require_name_email' );
		$qodef_html_req  = ( $qodef_req ? " required='required'" : '' );
		
		$args = array(
			'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title">',
			'title_reply'        => esc_attr__( 'Post a comment:', 'plamen' ),
			'title_reply_after'  => '</h3>',
            'comment_field'      => '<p class="comment-form-comment"><textarea id="comment" name="comment" placeholder="' . esc_attr__( 'Your Comment *', 'plamen' ) . '" cols="45" rows="8" maxlength="65525" required="required"></textarea></p>',
            'fields'             => array(
                'email'  => '<div class="qodef-grid qodef-layout--columns qodef-col-num--2 qodef-gutter--tiny"><div class="qodef-grid-inner"><div class="qodef-grid-item"><p class="comment-form-email">
							 <input id="email" name="email" type="email" placeholder="' . esc_attr__( 'Your Email *', 'plamen' ) . '" value="' . esc_attr( $qodef_commenter['comment_author_email'] ) . '" size="30" maxlength="100" aria-describedby="email-notes" ' . $qodef_html_req . ' />
							 </p></div></div>',
				'author' => '<div class="qodef-grid-inner"><div class="qodef-grid-item"><p class="comment-form-author">
							<input id="author" name="author" type="text" placeholder="' . esc_attr__( 'Your Name *', 'plamen' ) . '" value="' . esc_attr( $qodef_commenter['comment_author'] ) . '" size="30" maxlength="245" ' . $qodef_html_req . ' />
							</p></div></div></div>',
                'url'    => '<p class="comment-form-url">
                            <input id="url" name="url" placeholder="' . esc_attr__( 'Website', 'plamen' ) . '" type="text" value="' . esc_attr( $qodef_commenter['comment_author_url'] ) . '" size="30" maxlength="200" />
                            </p>',
            ),
        );

		comment_form( apply_filters( 'plamen_filter_comment_form_args', $args ) ); ?>
	</div>
</div>