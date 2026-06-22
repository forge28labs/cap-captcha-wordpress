<?php

defined('ABSPATH') || exit;

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- follows Formidable's naming convention for auto-discovery
class FrmFieldCapCaptcha extends FrmFieldType
{
    protected $type = 'cap_captcha';
    protected $has_for_label = false;

    protected function field_settings_for_type()
    {
        return array(
            'required'       => false,
            'invalid'        => true,
            'default'        => false,
            'clear_on_focus' => false,
            'unique'         => false,
            'options'        => false,
            'conf_field'     => false,
            'captcha_size'   => false,
            'captcha_theme'  => false,
        );
    }

    protected function new_field_settings()
    {
        return array(
            'invalid' => 'CAPTCHA verification failed. Please try again.',
            'label'   => 'none',
        );
    }

    protected function extra_field_opts()
    {
        return array(
            'label' => 'none',
        );
    }

    protected function include_form_builder_file()
    {
        return '';
    }

    public function show_on_form_builder($name = '')
    {
        $field = FrmFieldsHelper::setup_edit_vars($this->field);
?>
        <div class="frm-captcha-preview" style="padding:20px;text-align:center;background:#f0f0f1;border:1px dashed #c3c4c7;border-radius:4px;">
            <span class="frmfont frm_shield_check2_icon" style="font-size:1.5rem;"></span>
            <p style="margin:5px 0 0;"><?php esc_html_e('Cap Captcha', 'cap-captcha'); ?></p>
            <p style="margin:0;font-size:0.85em;color:#787c82;"><?php esc_html_e('Proof-of-work CAPTCHA', 'cap-captcha'); ?></p>
        </div>
<?php
    }

    public function front_field_input($args, $shortcode_atts)
    {
        $options = get_option('cap_captcha_options');

        if (empty($options['instance_url']) || empty($options['site_key'])) {
            return '<div class="frm_error" style="color:#a00;">'
                . esc_html__('Cap Captcha is not configured. Set up the Instance URL and Site Key in the Cap Captcha settings.', 'cap-captcha')
                . '</div>';
        }

        $endpoint = trailingslashit($options['instance_url']) . trailingslashit($options['site_key']);
        return '<div id="' . esc_attr($args['html_id']) . '_container" class="cap-captcha-wrap" style="margin-top:0.5rem;margin-bottom:1rem;">'
            . '<cap-widget data-cap-api-endpoint="' . esc_url($endpoint) . '" required></cap-widget>'
            . '</div>';
    }

    protected function load_field_scripts($args)
    {
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

        wp_enqueue_script(
            'cap-captcha-frm',
            CAP_CAPTCHA_PLUGIN_URL . 'assets/cap-captcha-frm.js',
            array(),
            CAP_CAPTCHA_VERSION,
            array('in_footer' => true)
        );
    }

    public function validate($args)
    {
        if (FrmAppHelper::is_admin()) {
            return array();
        }

        $options = get_option('cap_captcha_options');

        if (empty($options['instance_url']) || empty($options['site_key'])) {
            return array();
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- validated by Formidable's own nonce
        $token = sanitize_text_field(wp_unslash($_POST['cap-token'] ?? ''));

        if (empty($token)) {
            return array('field' . $args['id'] => __('The captcha is missing from this form', 'cap-captcha'));
        }

        if (empty($options['secret_key'])) {
            return array();
        }

        $url = trailingslashit($options['instance_url'])
            . trailingslashit($options['site_key'])
            . 'siteverify';

        $response = wp_remote_post(
            $url,
            array(
                'headers' => array('Content-Type' => 'application/json'),
                'body'    => wp_json_encode(array(
                    'secret'   => $options['secret_key'],
                    'response' => $token,
                )),
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            return array('field' . $args['id'] => __('There was a problem verifying the captcha', 'cap-captcha'));
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($data['success']) || true !== $data['success']) {
            $invalid_message = FrmField::get_option($this->field, 'invalid');
            return array('field' . $args['id'] => $invalid_message ?: __('CAPTCHA verification failed. Please try again.', 'cap-captcha'));
        }

        return array();
    }
}
