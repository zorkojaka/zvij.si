<div <?php qode_framework_class_attribute( $holder_classes ); ?>>
    <div class="qodef-grid-inner">
        <div class="qodef-grid-item">
            <?php plamen_core_template_part( 'shortcodes/image-with-content', 'templates/parts/image', '', $params ) ?>
        </div>
        <div class="qodef-grid-item qodef-content-holder">
            <div class="qodef-content-wrapper">
                <div class="qodef-m-holder">
                    <?php plamen_core_template_part( 'shortcodes/image-with-content', 'templates/parts/title', '', $params ) ?>
                    <div class="qodef-m-text-wrapper">
                        <?php plamen_core_template_part( 'shortcodes/image-with-content', 'templates/parts/text', '', $params ) ?>
                        <?php plamen_core_template_part( 'shortcodes/image-with-content', 'templates/parts/background_text', '', $params ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>