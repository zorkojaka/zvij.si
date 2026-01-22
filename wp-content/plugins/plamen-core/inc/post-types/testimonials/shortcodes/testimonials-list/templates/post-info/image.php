<?php if ( has_post_thumbnail() ) { ?>
	<div class="qodef-e-media-image qodef-testimonial-image" data-slide-index="<?php echo esc_attr($params['itemnum']); ?>">
		<?php the_post_thumbnail( array(245,245) ); ?>
	</div>
<?php } ?>