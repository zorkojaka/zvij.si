<?php

if ( ! function_exists( 'plamen_include_comments_in_templates' ) ) {
	/**
	 * Function which includes comments templates on pages/posts
	 */
	function plamen_include_comments_in_templates() {

		// Include comments template
		comments_template();
	}

	add_action( 'plamen_action_after_page_content', 'plamen_include_comments_in_templates', 100 ); // permission 100 is set to comments template be at the last place
	add_action( 'plamen_action_after_blog_post_item', 'plamen_include_comments_in_templates', 100 );
}

if ( ! function_exists( 'plamen_is_page_comments_enabled' ) ) {
	/**
	 * Function that check is module enabled
	 */
	function plamen_is_page_comments_enabled() {
		$is_enabled = apply_filters( 'plamen_filter_enable_page_comments', true );

		return $is_enabled;
	}
}

if ( ! function_exists( 'plamen_load_page_comments' ) ) {
	/**
	 * Function which loads page template module
	 */
	function plamen_load_page_comments() {

		if ( plamen_is_page_comments_enabled() ) {
			plamen_template_part( 'comments', 'templates/comments' );
		}
	}

	add_action( 'plamen_action_page_comments_template', 'plamen_load_page_comments' );
}

if ( ! function_exists( 'plamen_get_comments_list_template' ) ) {
	/**
	 * Function which modify default wordpress comments list template
	 *
	 * @param object $comment
	 * @param array $args
	 * @param int $depth
	 *
	 * @return string that contains comments list html
	 */
	function plamen_get_comments_list_template( $comment, $args, $depth ) {
		global $post;
		$GLOBALS['comment'] = $comment;

		$classes = array();

		$is_author_comment = $post->post_author == $comment->user_id;
		if ( $is_author_comment ) {
			$classes[] = 'qodef-comment--author';
		}

		$is_specific_comment = $comment->comment_type == 'pingback' || $comment->comment_type == 'trackback';
		if ( $is_specific_comment ) {
			$classes[] = 'qodef-comment--no-avatar';
			$classes[] = 'qodef-comment--' . esc_attr( $comment->comment_type );
		}
		?>
    <li class="qodef-comment-item qodef-e <?php echo esc_attr( implode( ' ', $classes ) ); ?>">
        <div id="comment-<?php comment_ID(); ?>" class="qodef-e-inner">
			<?php if ( ! $is_specific_comment ) { ?>
                <div class="qodef-e-image"><?php echo get_avatar( $comment, 62 ); ?></div>
			<?php } ?>
            <div class="qodef-e-content">
                <div class="qodef-e-links">
                    <?php
                    comment_reply_link( array_merge( $args, array(
                        'reply_text' => esc_html__( 'Reply', 'plamen' ),
                        'depth'      => $depth,
                        'max_depth'  => $args['max_depth'],
                    ) ) );

                    edit_comment_link( esc_html__( 'Edit', 'plamen' ) ); ?>
                </div>
                <div class="qodef-e-date commentmetadata">
                    <a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>"><?php comment_time( get_option( 'date_format' ) ); ?></a>
                </div>
                <div class="qodef-e-title vcard">
                    <h4>
                        <?php echo sprintf( '<span class="fn">%s%s</span>', $is_specific_comment ? sprintf( '%s: ', esc_attr( ucwords( $comment->comment_type ) ) ) : '', get_comment_author_link() ); ?>
                    </h4>
                </div>
				<?php if ( ! $is_specific_comment ) { ?>
                    <p class="qodef-e-text"><?php comment_text(); ?></p>
				<?php } ?>
            </div>
        </div>
		<?php //li tag will be closed by WordPress after looping through child elements ?>
		<?php
	}
}

if ( ! function_exists( 'plamen_get_comment_form_args' ) ) {
    /**
     * Function that define new comment form args in order to override default wordpress comment form
     *
     * @param array $attr - additional array which override default values
     *
     * @return array
     */
    function plamen_get_comment_form_args( $attr = array()) {
        $qodef_commenter      = wp_get_current_commenter();
        $qodef_required_attr  = get_option( 'require_name_email' ) ? ' required="required"' : '';
        $qodef_required_label = get_option( 'require_name_email' ) ? '*' : '';

        $comment_placeholder = isset( $attr['comment_placeholder'] ) && ! empty( $attr['comment_placeholder'] ) ? esc_attr( $attr['comment_placeholder'] ) : esc_attr__( 'Your Comment *', 'plamen' );

        $args = array(
            'title_reply_before' => '<h4 id="reply-title" class="comment-reply-title">',
            'title_reply_after'  => '</h4>',
            'comment_field'      => '<p class="comment-form-comment">
                                    <textarea id="comment" name="comment" placeholder="' . $comment_placeholder . '" cols="45" rows="8" maxlength="65525" required="required"></textarea>
                                    </p>',
            'fields'             => array(
                'author' => '<div class="qodef-grid qodef-layout--columns qodef-col-num--2"><div class="qodef-grid-inner"><div class="qodef-grid-item"><p class="comment-form-author">
                            <input id="author" name="author" placeholder="' . sprintf( '%1s%2s', esc_attr__( 'Your Name', 'plamen' ), esc_attr( $qodef_required_label ) ) . '" type="text" value="' . esc_attr( $qodef_commenter['comment_author'] ) . '" size="30" maxlength="245" ' . $qodef_required_attr . ' />
                            </p></div>',
                'email'  => '<div class="qodef-grid-item"><p class="comment-form-email">
                            <input id="email" name="email" placeholder="' . sprintf( '%1s%2s', esc_attr__( 'Your Email', 'plamen' ), esc_attr( $qodef_required_label ) ) . '" type="text" value="' . esc_attr( $qodef_commenter['comment_author_email'] ) . '" size="30" maxlength="100" aria-describedby="email-notes" ' . $qodef_required_attr . ' />
                            </p></div></div></div>',
                'url'    => '<p class="comment-form-url">
                            <input id="url" name="url" placeholder="' . esc_attr__( 'Website', 'plamen' ) . '" type="text" value="' . esc_attr( $qodef_commenter['comment_author_url'] ) . '" size="30" maxlength="200" />
                            </p>',
            ),
            'submit_button'      => '<button name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s"><span class="qodef-m-text">%4$s</span></button>',
            'class_submit'       => 'qodef-button qodef-layout--filled',
            'class_form'         => 'qodef-comment-form',
        );

        return apply_filters( 'plamen_filter_comment_form_args', $args );
    }
}