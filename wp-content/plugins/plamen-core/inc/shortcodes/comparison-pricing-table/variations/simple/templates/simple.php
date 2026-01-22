<?php if ( is_array( $features ) && count( $features ) ) : ?>
	<div <?php qode_framework_class_attribute( $holder_classes ); ?>>
		<div class="qodef-m-table qodef--features">
			<?php if ( ! empty( $title ) ) { ?>
				<div class="qodef-m-table-head">
					<h4 class="qodef-m-table-head-title"><?php echo esc_html( preg_replace( '#^<\/p>|<p>$#', '', $title ) ); ?></h4>
				</div>
			<?php } ?>
			<div class="qodef-m-content">
				<ul class="qodef-m-content-list qodef-e">
					<?php foreach ( $features as $feature ) : ?>
						<li class="qodef-e-item">
							<h6 class="qodef-e-item-title"><?php echo qode_framework_wp_kses_html( 'content', $feature ); ?></h6>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="qodef-m-footer"></div>
		</div>
		<?php foreach ( $items as $item ) { ?>
			<div class="qodef-m-table">
				<?php if ( ! empty( $item['item_title'] ) ) { ?>
					<div class="qodef-m-table-head">
						<h5 class="qodef-m-table-head-title"><?php echo qode_framework_wp_kses_html( 'content', $this_shortcode->get_modified_title( $item ) ); ?></h5>
					</div>
				<?php } ?>
				<div class="qodef-m-content">
					<?php echo do_shortcode( preg_replace( '#^<\/p>|<p>$#', '', $item['content'] ) ); ?>
				</div>
				<?php if ( $item['show_button'] == 'yes' && class_exists( 'PlamenCoreButtonShortcode' ) ) { ?>
					<div class="qodef-m-footer">
						<?php echo PlamenCoreButtonShortcode::call_shortcode( $this_shortcode->get_button_params( $item ) ); ?>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
<?php endif; ?>
