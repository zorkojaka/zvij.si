<div class="qodef-m-item qodef-e">
	<?php if ( ! empty( $item['item_title'] ) ) { ?>
		<<?php echo esc_attr( $item['item_title_tag'] ); ?> class="qodef-e-title" <?php qode_framework_inline_style( $this_shortcode->get_title_styles( $item ) ); ?>>
			<?php echo sprintf( '%s %s %s',
				! empty( $item['item_link'] ) ? '<a itemprop="url" class="qodef-e-title-link" href="' . esc_url( $item['item_link'] ) . '" target="' . esc_attr( $item['item_link_target'] ) . '">' : '',
				qode_framework_wp_kses_html( 'content', $item['item_title'] ),
				! empty( $item['item_link'] ) ? '</a>' : ''
			) ?>
		</<?php echo esc_attr( $item['item_title_tag'] ); ?>>
	<?php } ?>
	<?php if ( ! empty( $item['item_text'] ) ) { ?>
		<p class="qodef-e-text" <?php qode_framework_inline_style( $this_shortcode->get_text_styles( $item ) ); ?>><?php echo qode_framework_wp_kses_html( 'content', $item['item_text'] ); ?></p>
	<?php } ?>
</div>