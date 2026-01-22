<div <?php qode_framework_class_attribute( $holder_classes ); ?>>
	<div class="qodef-m-images">
		<?php if ( ! empty( $main_image ) ) : ?>
			<?php echo wp_get_attachment_image( $main_image, 'full', false, array( 'class' => 'qodef-e-image qodef--main' ) ); ?>
		<?php endif; ?>
		
		<?php if ( ! empty( $stacked_image ) ): ?>
			<?php echo wp_get_attachment_image( $stacked_image, 'full', false, array( 'class' => 'qodef-e-image qodef--stack' ) ); ?>
		<?php endif; ?>
	</div>
</div>