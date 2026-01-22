<?php

class QodeFrameworkFieldWidgetColor extends QodeFrameworkFieldWidgetType {
	public function render() { ?>
		<input class="widefat qodef-color-field" data-alpha="true" id="<?php echo esc_attr( $this->params['id'] ); ?>" name="<?php echo esc_attr( $this->params['name'] ); ?>" type="text" value="<?php echo esc_attr( $this->params['value'] ); ?>"/>
		<?php
	}
}
