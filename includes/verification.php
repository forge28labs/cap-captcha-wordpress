<?php

/**
 * Server-side verification of Cap tokens via the Standalone siteverify endpoint.
 */

defined('ABSPATH') || exit;

add_filter('wp_authenticate_user', 'cap_captcha_check_login', 10, 2);
add_filter('registration_errors', 'cap_captcha_check_registration', 10, 3);
add_action('lostpassword_post', 'cap_captcha_check_lostpassword', 10, 1);
add_action('validate_password_reset', 'cap_captcha_check_password_reset', 10, 2);
add_filter('preprocess_comment', 'cap_captcha_check_comment');

function cap_captcha_verify_token(string $token): bool
{
	$options = get_option('cap_captcha_options');
	if (empty($options['instance_url']) || empty($options['site_key']) || empty($options['secret_key'])) {
		return false;
	}

	$url = trailingslashit($options['instance_url'])
		. trailingslashit($options['site_key'])
		. 'siteverify';

	$response = wp_remote_post(
		$url,
		array(
			'headers' => array('Content-Type' => 'application/json'),
			'body'    => wp_json_encode(
				array(
					'secret'   => $options['secret_key'],
					'response' => $token,
				)
			),
			'timeout' => 30,
		)
	);

	if (is_wp_error($response)) {
		return false;
	}

	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);

	return isset($data['success']) && true === $data['success'];
}

function cap_captcha_get_token_from_request(): string
{
	return sanitize_text_field(wp_unslash($_POST['cap-token'] ?? ''));
}

function cap_captcha_check_login($user, string $password)
{
	if (empty(get_option('cap_captcha_options')['protect_login'])) {
		return $user;
	}

	$token = cap_captcha_get_token_from_request();
	if (empty($token) || ! cap_captcha_verify_token($token)) {
		return new WP_Error(
			'cap_captcha_failed',
			__('CAPTCHA verification failed. Please try again.', 'cap-captcha')
		);
	}

	return $user;
}

function cap_captcha_check_registration($errors, $sanitized_user_login, $user_email)
{
	if (empty(get_option('cap_captcha_options')['protect_register'])) {
		return $errors;
	}

	$token = cap_captcha_get_token_from_request();
	if (empty($token) || ! cap_captcha_verify_token($token)) {
		$errors->add(
			'cap_captcha_failed',
			__('CAPTCHA verification failed. Please try again.', 'cap-captcha')
		);
	}

	return $errors;
}

function cap_captcha_check_lostpassword($errors): void
{
	if (empty(get_option('cap_captcha_options')['protect_lostpassword'])) {
		return;
	}

	$token = cap_captcha_get_token_from_request();
	if (empty($token) || ! cap_captcha_verify_token($token)) {
		$errors->add(
			'cap_captcha_failed',
			__('CAPTCHA verification failed. Please try again.', 'cap-captcha')
		);
	}
}

function cap_captcha_check_password_reset($errors, $user): void
{
	if (empty(get_option('cap_captcha_options')['protect_lostpassword'])) {
		return;
	}

	if (! isset($_POST['cap-token'])) {
		return;
	}

	$token = cap_captcha_get_token_from_request();
	if (empty($token) || ! cap_captcha_verify_token($token)) {
		$errors->add(
			'cap_captcha_failed',
			__('CAPTCHA verification failed. Please try again.', 'cap-captcha')
		);
	}
}

function cap_captcha_check_comment(array $commentdata): array
{
	if (empty(get_option('cap_captcha_options')['protect_comments'])) {
		return $commentdata;
	}

	$token = cap_captcha_get_token_from_request();
	if (empty($token) || ! cap_captcha_verify_token($token)) {
		wp_die(
			'<p>' . esc_html__('CAPTCHA verification failed. Please go back and try again.', 'cap-captcha') . '</p>',
			esc_html__('CAPTCHA Error', 'cap-captcha'),
			array('back_link' => true)
		);
	}

	return $commentdata;
}
