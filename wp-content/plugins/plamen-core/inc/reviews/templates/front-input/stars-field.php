<span class="qodef-rating-inner">
    <label><?php echo esc_html($label); ?></label>
    <span class="qodef-comment-rating-box">
        <?php for ( $i = 1; $i <= PLAMEN_CORE_REVIEWS_MAX_RATING; $i ++ ) { ?>
            <span class="qodef-star-rating" data-value="<?php echo esc_attr( $i ); ?>"></span>
        <?php } ?>
        <input type="hidden" name="<?php echo esc_attr($key); ?>" class="qodef-rating" value="3">
    </span>
</span>