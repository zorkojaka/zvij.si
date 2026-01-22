<?php if ( has_post_thumbnail() ) {
	$image_dimension     = isset( $image_dimension ) && ! empty( $image_dimension ) ? esc_attr( $image_dimension['size'] ) : 'full';
	$custom_image_width  = isset( $custom_image_width ) && $custom_image_width !== '' ? intval( $custom_image_width ) : 0;
	$custom_image_height = isset( $custom_image_height ) && $custom_image_height !== '' ? intval( $custom_image_height ) : 0;
	?>
	<div class="qodef-e-media-image">
		<?php if ( $has_single ) { ?>
			<a itemprop="url" href="<?php the_permalink(); ?>">
		<?php } ?>
			<?php echo plamen_core_get_list_shortcode_item_image( $image_dimension, 0, $custom_image_width, $custom_image_height ); ?>
		<?php if ( $has_single ) { ?>
			</a>
		<?php } ?>
	</div>
<?php } ?>