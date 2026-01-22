<?php

$image = plamen_core_get_post_value_through_levels( 'qodef_page_spinner_image', qode_framework_get_page_id() );
$text = plamen_core_get_post_value_through_levels( 'qodef_page_spinner_text', qode_framework_get_page_id() );
$smoke = plamen_core_get_post_value_through_levels( 'qodef_page_spinner_smoke', qode_framework_get_page_id() );
$src = PLAMEN_CORE_INC_URL_PATH . '/spinner/layouts/plamen/assets/img/preloader-effect.mp4#t=2';

?>

<div class="qodef-m-plamen">
    <div class="qodef-m-plamen-frame">
        <?php echo plamen_get_svg_icon('frame'); ?>
        <div class="qodef-m-plamen-image">
            <?php echo wp_get_attachment_image( $image, 'full' ); ?>
        </div>
        <div class="qodef-m-plamen-text">
            <span><?php echo esc_html( $text ); ?></span>
        </div>
    </div>
    <?php if ( $smoke == 'yes' ) { ?>
        <div class="qodef-m-plamen-smoke">
            <video src="<?php echo esc_url( $src ); ?>" muted autoplay loop></video>
        </div>
    <?php } ?>
</div>