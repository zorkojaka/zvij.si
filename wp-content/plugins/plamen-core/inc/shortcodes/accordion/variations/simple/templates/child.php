<<?php echo esc_attr( $title_tag ); ?> class="qodef-accordion-title">
	<span class="qodef-tab-title">
		<?php echo esc_html( $title ); ?>
	</span>
	<span class="qodef-accordion-mark">
		<span class="qodef-icon--arrow"><?php plamen_svg_icon('angle-arrow'); ?></span>
	</span>
</<?php echo esc_attr( $title_tag ); ?>>
<div class="qodef-accordion-content">
	<div class="qodef-accordion-content-inner">
		<?php echo do_shortcode( $content ); ?>
	</div>
</div>