<div <?php qode_framework_class_attribute( $holder_classes ); ?>>
	<div class="qodef-m-items qodef--left">
		<?php foreach ( $items[0] as $item ) {
			plamen_core_template_part( 'shortcodes/item-showcase', 'variations/list/templates/parts/item', '', array_merge( $params, array( 'item' => $item ) ) );
		} ?>
	</div>
	<?php plamen_core_template_part( 'shortcodes/item-showcase', 'variations/list/templates/parts/image', '', $params ); ?>
	<div class="qodef-m-items qodef--right">
		<?php foreach ( $items[1] as $item ) {
			plamen_core_template_part( 'shortcodes/item-showcase', 'variations/list/templates/parts/item', '', array_merge( $params, array( 'item' => $item ) ) );
		} ?>
	</div>
</div>