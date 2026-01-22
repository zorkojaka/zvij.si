<?php if ( ! empty( $price ) ) { ?>
	<div class="qodef-m-price">
		<div class="qodef-m-price-wrapper qodef-h2" <?php qode_framework_inline_style( $price_styles ); ?>>
			<span class="qodef-m-price-value"><?php echo esc_html( $price ); ?></span>
			<?php if ( ! empty( $currency ) ) { ?>
				<span class="qodef-m-price-currency" <?php qode_framework_inline_style( $currency_styles ); ?>><?php echo esc_html( $currency ); ?></span>
			<?php } ?>
		</div>
	</div>
<?php } ?>