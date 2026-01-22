<?php
    $params = array(
        'layout' => 'text',
    );
?>
<?php if ( plamen_is_installed('core') ) { ?>
    <div class="qodef-e-info-item qodef-e-info-share">
        <?php plamen_render_social_share_element( $params ); ?>
    </div>
<?php } ?>
