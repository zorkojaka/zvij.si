<div id="qodef-age-verification-modal" class="qodef-m <?php echo esc_attr( $holder_classes ); ?>" <?php qode_framework_inline_style( $content_style ); ?>>
	<?php if ( ! empty( $logo_image ) ) :

        $logo_image = intval($logo_image);

        ?>
		<div class="qodef-m-logo">
			<a itemprop="url" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php echo is_int( $logo_image ) ? wp_get_attachment_image( $logo_image, 'full' ) : qode_framework_get_image_html_from_src( $logo_image ); ?>
			</a>
		</div>
	<?php endif; ?>
	<div class="qodef-m-content">
		<?php if ( ! empty( $title ) ) : ?>
			<h2 class="qodef-m-content-title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		
		<?php if ( ! empty( $subtitle ) ): ?>
			<p class="qodef-m-content-subtitle"><?php echo qode_framework_wp_kses_html( 'content', $subtitle ); ?></p>
		<?php endif; ?>
		
		<?php if ( ! empty( $note ) ): ?>
			<p class="qodef-m-content-note"><?php echo qode_framework_wp_kses_html( 'content', $note ); ?></p>
		<?php endif; ?>
		<?php if ( class_exists( 'PlamenCoreButtonShortcode' ) ) { ?>
			<div class="qodef-m-content-prevent">
				<?php
				$button_params = array(
					'link'          => '#',
					'text'          => esc_html__( 'Yes I am', 'plamen-core' ),
					'size'          => 'normal',
					'button_layout' => 'filled',
					'custom_class'  => 'qodef-prevent--yes',
				);
				echo PlamenCoreButtonShortcode::call_shortcode( $button_params );
				
				$button_params = array(
					'link'          => esc_url( $params['link'] ),
					'text'          => esc_html__( 'No I am not', 'plamen-core' ),
					'size'          => 'normal',
					'button_layout' => 'outlined',
					'custom_class'  => 'qodef-prevent--no',
				);
				echo PlamenCoreButtonShortcode::call_shortcode( $button_params ); ?>
			</div>
		<?php } ?>
	</div>
</div>