<?php

defined('ABSPATH') || exit;

class WPForms_Field_CapCaptcha extends WPForms_Field
{
    public function init()
    {
        $this->name  = 'Cap Captcha';
        $this->type  = 'cap_captcha';
        $this->icon  = 'fa-shield';
        $this->order = 300;

        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts(): void
    {
        if (is_admin()) {
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
            'https://cdn.jsdelivr.net/npm/cap-widget',
            array(),
            null,
            array('in_footer' => true)
        );

        wp_enqueue_script(
            'cap-captcha-wpforms',
            CAP_CAPTCHA_PLUGIN_URL . 'assets/cap-captcha-wpforms.js',
            array(),
            CAP_CAPTCHA_VERSION,
            array('in_footer' => true)
        );
    }

    protected function format_field_display_value($field, $deprecated, $form_data)
    {
        return $this->field_display_value($field, $deprecated, $form_data);
    }

    public function field_options($field)
    {
        $this->field_option(
            'basic-options',
            $field,
            array('markup' => 'open')
        );

        $this->field_option('label', $field);

        $this->field_option(
            'basic-options',
            $field,
            array('markup' => 'close')
        );

        $this->field_option(
            'advanced-options',
            $field,
            array('markup' => 'open')
        );

        $this->field_option('label_hide', $field);

        $this->field_option(
            'advanced-options',
            $field,
            array('markup' => 'close')
        );
    }

    public function field_preview($field)
    {
        ?>
        <div style="padding:20px;text-align:center;background:#f0f0f1;border:1px dashed #c3c4c7;border-radius:4px;">
            <span class="fa fa-shield" style="font-size:1.5rem;"></span>
            <p style="margin:5px 0 0;"><?php esc_html_e('Cap Captcha', 'cap-captcha'); ?></p>
            <p style="margin:0;font-size:0.85em;color:#787c82;"><?php esc_html_e('Proof-of-work CAPTCHA', 'cap-captcha'); ?></p>
        </div>
        <?php
    }

    public function field_display($field, $deprecated, $form_data)
    {
        $options = get_option('cap_captcha_options');

        if (empty($options['instance_url']) || empty($options['site_key'])) {
            printf(
                '<div class="wpforms-error">%s</div>',
                esc_html__('Cap Captcha is not configured. Set up the Instance URL and Site Key in the Cap Captcha settings.', 'cap-captcha')
            );
            return;
        }

        $endpoint = trailingslashit($options['instance_url']) . trailingslashit($options['site_key']);

        printf(
            '<div class="wpforms-field-row wpforms-field-cap-captcha-row" style="margin-top:0.5rem;margin-bottom:1rem;">'
            . '<cap-widget data-cap-api-endpoint="%s" required></cap-widget>'
            . '<input type="hidden" name="wpforms[fields][%d]" value="">'
            . '</div>',
            esc_url($endpoint),
            (int) $field['id']
        );
    }

    public function validate($field_id, $field_submit, $form_data)
    {
        $raw_token = $_POST['cap-token'] ?? '';
        $token     = sanitize_text_field(wp_unslash($raw_token));

        if (empty($token)) {
            wpforms()->process->errors[$form_data['id']][$field_id] = esc_html__('Please complete the captcha.', 'cap-captcha');
            return;
        }

        $options = get_option('cap_captcha_options');

        if (empty($options['secret_key'])) {
            return;
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
            wpforms()->process->errors[$form_data['id']][$field_id] = esc_html__('There was a problem verifying the captcha', 'cap-captcha');
            return;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($data['success']) || true !== $data['success']) {
            $invalid_message = $field['invalid'] ?? __('CAPTCHA verification failed. Please try again.', 'cap-captcha');
            wpforms()->process->errors[$form_data['id']][$field_id] = $invalid_message;
        }
    }

    public function format($field_id, $field_submit, $form_data)
    {
        $name = ! empty($form_data['fields'][$field_id]['label'])
            ? sanitize_text_field($form_data['fields'][$field_id]['label'])
            : '';

        wpforms()->process->fields[$field_id] = array(
            'name'  => $name,
            'value' => esc_html__('Verified', 'cap-captcha'),
            'id'    => absint($field_id),
            'type'  => $this->type,
        );
    }
}

new WPForms_Field_CapCaptcha();
