<div <?php qode_framework_class_attribute( $holder_classes ); ?>>
	<?php foreach ( $params['working_hours_params'] as $day => $time ) : ?>
		<div class="qodef-working-hours-item qodef-e">
			<?php if ( ! empty( $day ) ) : ?>
				<h4 class="qodef-e-day"><?php echo sprintf( esc_html__( '%s', 'plamen-core' ), $day ); ?>
					<?php foreach ( $params['working_hours_special_params']['special_days'] as $special ) :
						if ( $day === $special ) :
							echo qode_framework_icons()->render_icon( 'icon_star', 'elegant-icons', array( 'icon_attributes' => array( 'class' => 'qodef-e-day-icon' ) ) );
						endif;
					endforeach; ?>
				</h4>
			<?php endif; ?>
			<?php if ( ! empty( $time ) ) { ?>
				<span class="qodef-e-time"><?php echo esc_html( $time ); ?></span>
			<?php } else { ?>
				<span class="qodef-e-time qodef--closed"><?php esc_html_e( 'Closed', 'plamen-core' ); ?></span>
			<?php } ?>
		</div>
	<?php endforeach; ?>

    <?php if ( isset( $params['working_hours_special_params']['special_text'] ) && ! empty( $params['working_hours_special_params']['special_text'] ) ) : ?>
        <div class="qodef-m-footer">
            <?php echo qode_framework_icons()->render_icon( 'icon_star', 'elegant-icons', array( 'icon_attributes' => array( 'class' => 'qodef-m-footer-icon' ) ) ); ?>
            <span class="qodef-m-footer-label"><?php echo esc_html( $params['working_hours_special_params']['special_text'] ); ?></span>
        </div>
    <?php endif; ?>
</div>