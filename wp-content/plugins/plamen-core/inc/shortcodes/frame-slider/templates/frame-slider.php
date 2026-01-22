<div class="qodef-frame-slider-holder swiper-container-horizontal">
    <div class="qodef-frame-slider-image">
        <img src="<?php echo esc_url( PLAMEN_CORE_SHORTCODES_URL_PATH . '/frame-slider/assets/img/frame-slider.png' ); ?>" alt="<?php esc_attr_e( 'Frame slider phone', 'plamen-core' ); ?>">
    </div>
    <div class="qodef-m-items">
        <div class="qodef-m-swiper">
            <div class="swiper-wrapper">
				<?php foreach ( $items as $item ) :
					$image_alt = get_post_meta( $item['item_image'], '_wp_attachment_image_alt', true ); ?>

                    <div class="qodef-m-item swiper-slide">
						<?php if ( ! empty( $item['item_link'] ) ) : ?>
                            <a class="qodef-m-item-link" href="<?php echo esc_url( $item['item_link'] ) ?>" target="<?php echo esc_attr( $link_target ); ?>">
						<?php endif; ?>

						<?php echo wp_get_attachment_image( $item['item_image'], 'full' ); ?>

						<?php if ( ! empty( $item['item_link'] ) ) : ?>
                            </a>
		    			<?php endif; ?>
                    </div>
				<?php endforeach; ?>
            </div><!-- .swiper-wrapper -->
        </div><!-- .qodef-m-swiper -->
    </div><!-- .qodef-m-items -->

    <div class="swiper-pagination"></div>
</div>