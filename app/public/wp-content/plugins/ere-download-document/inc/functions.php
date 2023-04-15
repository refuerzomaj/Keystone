<?php
add_action('wp_footer', 'ered_load_popup_template');
function ered_load_popup_template() {
	ERED()->get_template('popup.php');
}