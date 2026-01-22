<div <?php qode_framework_class_attribute( $holder_classes ); ?> <?php qode_framework_inline_attr( $slider_attr, 'data-options' ); ?>>
	<div class="swiper-wrapper">
		<?php
		// Include items
		if ( ! empty( $images ) ) {
			foreach ( $images as $image ) {
				$image['item_classes'] = $item_classes;
				$image['image_action'] = $image_action;
				$image['target']       = $target;
				
				plamen_core_template_part( 'shortcodes/image-gallery', 'templates/parts/image', '', $image );
			}
		}
		?>
	</div>
	<?php if ( $slider_navigation !== 'no' ) { ?>
		<div class="swiper-button-next"></div>
		<div class="swiper-button-prev"></div>
	<?php } ?>
	<?php if ( $slider_pagination !== 'no' ) { ?>
		<div class="swiper-pagination"></div>
	<?php } ?>
</div>