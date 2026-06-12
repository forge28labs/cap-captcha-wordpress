<?php

defined('ABSPATH') || exit;

add_filter('frm_available_fields', 'cap_captcha_frm_register_field_type');
add_filter('frm_get_field_type_class', 'cap_captcha_frm_field_class', 10, 2);
add_filter('frm_setup_new_fields_vars', 'cap_captcha_frm_setup_field_name', 10, 2);
add_filter('frm_setup_edit_fields_vars', 'cap_captcha_frm_setup_field_name', 10, 2);

function cap_captcha_frm_register_field_type(array $fields): array
{
    $fields['cap_captcha'] = array(
        'name' => __('Cap Captcha', 'cap-captcha'),
        'icon' => 'frmfont frm_shield_check2_icon',
    );
    return $fields;
}

function cap_captcha_frm_field_class(string $class, string $field_type): string
{
    if ('cap_captcha' === $field_type) {
        require_once CAP_CAPTCHA_PLUGIN_DIR . 'includes/class-frm-field-cap-captcha.php';
        return 'FrmFieldCapCaptcha';
    }
    return $class;
}

function cap_captcha_frm_setup_field_name(array $values, $field): array
{
    $type = is_object($field) ? $field->type : $field;
    if ('cap_captcha' === $type && empty($values['name'])) {
        $values['name'] = __('Cap Captcha', 'cap-captcha');
    }
    return $values;
}
