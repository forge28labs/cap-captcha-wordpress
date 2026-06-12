<?php

defined('ABSPATH') || exit;

add_action('wpforms_loaded', 'cap_captcha_wpforms_init');

function cap_captcha_wpforms_init(): void
{
	if (! class_exists('WPForms_Field')) {
		return;
	}

	require_once CAP_CAPTCHA_PLUGIN_DIR . 'includes/integrations/wpforms/class-wpforms-field-cap-captcha.php';
}
