<div <?php qode_framework_class_attribute( $holder_classes ); ?>>
	<div>
		<?php
		// Include items
        if($variation !== 'simple') {
		    plamen_core_template_part( 'post-types/testimonials/shortcodes/testimonials-list', 'templates/loop', '', $params );
        } else {
            plamen_core_template_part( 'post-types/testimonials/shortcodes/testimonials-list', 'templates/loop-simple', '', $params );
        }
		?>
	</div>
</div>