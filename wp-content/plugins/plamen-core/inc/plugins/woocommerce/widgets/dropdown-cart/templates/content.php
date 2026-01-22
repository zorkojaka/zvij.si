<?php plamen_core_template_part( 'plugins/woocommerce/widgets/dropdown-cart', 'templates/parts/opener' ); ?>
<div class="qodef-m-dropdown">
	<div class="qodef-m-dropdown-inner">
		<?php if ( is_object(WC()->cart) && !WC()->cart->is_empty() ) {
			plamen_core_template_part( 'plugins/woocommerce/widgets/dropdown-cart', 'templates/parts/loop' );
			
			plamen_core_template_part( 'plugins/woocommerce/widgets/dropdown-cart', 'templates/parts/order-details' );
			
			plamen_core_template_part( 'plugins/woocommerce/widgets/dropdown-cart', 'templates/parts/button' );
		} else {
		    // Include posts not found
			plamen_core_template_part( 'plugins/woocommerce/widgets/dropdown-cart', 'templates/parts/posts-not-found' );
		} ?>
	</div>
</div>