<footer id="qodef-page-footer" <?php plamen_class_attribute( implode( ' ', apply_filters( 'plamen_filter_footer_holder_classes', array() ) ) ); ?>>
	<?php
	// Hook to include additional content before page footer content
	do_action( 'plamen_action_before_page_footer_content' );
	
	// Include module content template
	echo apply_filters( 'plamen_filter_footer_content_template', plamen_get_template_part( 'footer', 'templates/footer-content' ) );
	
	// Hook to include additional content after page footer content
	do_action( 'plamen_action_after_page_footer_content' );
	?>
</footer>