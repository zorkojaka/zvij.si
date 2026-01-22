<div class="qodef-m-content">
	<?php if ( ! WC()->cart->is_empty() ) {
		plamen_core_template_part( 'plugins/woocommerce/widgets/side-area-cart', 'templates/parts/loop' );
		
		plamen_core_template_part( 'plugins/woocommerce/widgets/side-area-cart', 'templates/parts/order-details' );
		
		plamen_core_template_part( 'plugins/woocommerce/widgets/side-area-cart', 'templates/parts/button' );
	} else {
		// Include posts not found
		plamen_core_template_part( 'plugins/woocommerce/widgets/side-area-cart', 'templates/parts/posts-not-found' );
	}
	
	plamen_core_template_part( 'plugins/woocommerce/widgets/side-area-cart', 'templates/parts/close' );
	?>
</div>