<div class="qodef-m-image">
		<?php if ( is_array( $image_params['image_size'] ) && count( $image_params['image_size'] ) ) {
			echo qode_framework_generate_thumbnail( $image_params['image_id'], $image_params['image_size'][0], $image_params['image_size'][1] );
		} else {
			echo wp_get_attachment_image( $image_params['image_id'], $image_params['image_size'] );
		} ?>
</div>