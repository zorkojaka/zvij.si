<?php if ( isset( $query_result ) && intval( $query_result->max_num_pages ) > 1 ) { ?>
	<div class="qodef-m-pagination qodef--standard">
		<div class="qodef-m-pagination-inner">
			<nav class="qodef-m-pagination-items" role="navigation">
				<div class="qodef-m-pagination-item qodef--prev">
					<a href="#" data-paged="1">
                        <?php plamen_svg_icon('arrow'); ?>
					</a>
				</div>
				<?php for ( $i = 1; $i <= intval( $query_result->max_num_pages ); $i ++ ) {
					$classes     = $i === 1 ? 'qodef--active' : '';
					$formatted_i = sprintf( "%02d", $i );
					?>
					<div class="qodef-m-pagination-item qodef--number qodef--number-<?php echo esc_attr( $i ); ?> <?php echo esc_attr( $classes ); ?>">
						<a href="#" data-paged="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $formatted_i ); ?></a>
					</div>
				<?php } ?>
				<div class="qodef-m-pagination-item qodef--next">
					<a href="#" data-paged="2">
                        <?php plamen_svg_icon('arrow'); ?>
					</a>
				</div>
			</nav>
		</div>
	</div>
<?php } ?>