<?php

/**
 * Frontend: loads the Cap widget and injects it into protected forms.
 */

defined('ABSPATH') || exit;

add_action('login_enqueue_scripts', 'cap_captcha_enqueue_widget');
add_action('wp_enqueue_scripts', 'cap_captcha_enqueue_widget');

add_action('login_form', 'cap_captcha_inject_login');
add_action('register_form', 'cap_captcha_inject_register');
add_action('lostpassword_form', 'cap_captcha_inject_lostpassword');
add_action('resetpass_form', 'cap_captcha_inject_resetpass');
add_action('comment_form_after_fields', 'cap_captcha_inject_comment');
add_action('comment_form_logged_in_after', 'cap_captcha_inject_comment');

add_action('wp_head', 'cap_captcha_inline_styles');

function cap_captcha_enqueue_widget(): void
{
    if (! cap_captcha_should_load()) {
        return;
    }

    wp_enqueue_style(
        'cap-captcha',
        CAP_CAPTCHA_PLUGIN_URL . 'assets/cap-captcha.css',
        array(),
        CAP_CAPTCHA_VERSION
    );

    wp_enqueue_script(
        'cap-widget',
        CAP_CAPTCHA_PLUGIN_URL . 'assets/cap-widget.js',
        array(),
        CAP_CAPTCHA_VERSION,
        array('in_footer' => true)
    );
}

function cap_captcha_inline_styles(): void
{
    $options = get_option('cap_captcha_options');

    if (empty($options)) {
        return;
    }

    if (empty($options['instance_url']) || empty($options['site_key'])) {
        return;
    }

    $color_map = array(
        'cap_background'          => '--cap-background',
        'cap_color'               => '--cap-color',
        'cap_border_color'        => '--cap-border-color',
        'cap_checkbox_background' => '--cap-checkbox-background',
        'cap_spinner_color'       => '--cap-spinner-color',
        'cap_spinner_background'  => '--cap-spinner-background-color',
    );

    $rules = array();
    foreach ($color_map as $key => $var) {
        if (! empty($options[$key])) {
            $color = sanitize_hex_color($options[$key]);
            if (! empty($color)) {
                $rules[] = sprintf('  %s: %s;', $var, $color);
            }
        }
    }

    if (! empty($options['cap_checkbox_border_advanced']) && ! empty($options['cap_checkbox_border_custom'])) {
        $rules[] = sprintf('  --cap-checkbox-border: %s;', sanitize_text_field($options['cap_checkbox_border_custom']));
    } elseif (! empty($options['cap_checkbox_border'])) {
        $color = sanitize_hex_color($options['cap_checkbox_border']);
        if (! empty($color)) {
            $rules[] = sprintf('  --cap-checkbox-border: 1px solid %s;', $color);
        }
    }

    if (empty($rules)) {
        return;
    }

    echo '<style>' . "\n";
    echo 'cap-widget {' . "\n";
    echo esc_html(implode("\n", $rules)) . "\n";
    echo '}' . "\n";
    echo '</style>' . "\n";
}

function cap_captcha_should_load(): bool
{
    $options = get_option('cap_captcha_options');
    if (empty($options['instance_url']) || empty($options['site_key'])) {
        return false;
    }

    $pagenow = $GLOBALS['pagenow'] ?? '';

    if ('wp-login.php' === $pagenow) {
        return ! empty($options['protect_login'])
            || ! empty($options['protect_register'])
            || ! empty($options['protect_lostpassword']);
    }

    if (! empty($options['protect_comments']) && is_singular() && comments_open()) {
        return true;
    }

    return false;
}

function cap_captcha_render_widget(): void
{
    $options = get_option('cap_captcha_options');
    if (empty($options['instance_url']) || empty($options['site_key'])) {
        return;
    }

    $endpoint = trailingslashit($options['instance_url']) . trailingslashit($options['site_key']);
    echo '<div style="margin-top: 0.5rem; margin-bottom: 1.5rem;"><cap-widget data-cap-api-endpoint="' . esc_url($endpoint) . '" required></cap-widget></div>';
}

function cap_captcha_inject_login(): void
{
    if (! empty(get_option('cap_captcha_options')['protect_login'])) {
        cap_captcha_render_widget();
    }
}

function cap_captcha_inject_register(): void
{
    if (! empty(get_option('cap_captcha_options')['protect_register'])) {
        cap_captcha_render_widget();
    }
}

function cap_captcha_inject_lostpassword(): void
{
    if (! empty(get_option('cap_captcha_options')['protect_lostpassword'])) {
        cap_captcha_render_widget();
    }
}

function cap_captcha_inject_resetpass(): void
{
    if (! empty(get_option('cap_captcha_options')['protect_lostpassword'])) {
        cap_captcha_render_widget();
    }
}

function cap_captcha_inject_comment(): void
{
    if (! empty(get_option('cap_captcha_options')['protect_comments'])) {
        cap_captcha_render_widget();
    }
}
