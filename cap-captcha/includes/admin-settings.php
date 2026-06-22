<?php

/**
 * Admin settings page for Cap Captcha.
 */

defined('ABSPATH') || exit;

add_action('admin_menu', 'cap_captcha_add_admin_menu');
add_action('admin_init', 'cap_captcha_settings_init');
add_filter(
    'plugin_action_links_' . plugin_basename(CAP_CAPTCHA_PLUGIN_DIR . 'cap-captcha.php'),
    'cap_captcha_plugin_action_links'
);

function cap_captcha_add_admin_menu(): void
{
    $hook = add_options_page(
        __('Cap Captcha Settings', 'cap-captcha'),
        __('Cap Captcha', 'cap-captcha'),
        'manage_options',
        'cap-captcha',
        'cap_captcha_options_page'
    );
    add_action("admin_print_scripts-{$hook}", 'cap_captcha_admin_scripts');
}

function cap_captcha_admin_scripts(): void
{
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_add_inline_script(
        'wp-color-picker',
        'jQuery(function($){ $(".cap-captcha-color-picker").wpColorPicker(); });'
    );
}

function cap_captcha_settings_init(): void
{
    register_setting(
        'cap_captcha_settings',
        'cap_captcha_options',
        'cap_captcha_sanitize_options'
    );

    add_settings_section(
        'cap_captcha_server_section',
        __('Server Configuration', 'cap-captcha'),
        '__return_null',
        'cap_captcha_settings'
    );

    add_settings_field(
        'instance_url',
        __('Instance URL', 'cap-captcha'),
        'cap_captcha_field_render',
        'cap_captcha_settings',
        'cap_captcha_server_section',
        array(
            'field'       => 'instance_url',
            'type'        => 'url',
            'description' => __('Your Cap Standalone server URL, e.g. https://cap.example.com', 'cap-captcha'),
        )
    );

    add_settings_field(
        'site_key',
        __('Site Key', 'cap-captcha'),
        'cap_captcha_field_render',
        'cap_captcha_settings',
        'cap_captcha_server_section',
        array(
            'field'       => 'site_key',
            'type'        => 'text',
            'description' => __('The site key from your Cap dashboard.', 'cap-captcha'),
        )
    );

    add_settings_field(
        'secret_key',
        __('Secret Key', 'cap-captcha'),
        'cap_captcha_field_render',
        'cap_captcha_settings',
        'cap_captcha_server_section',
        array(
            'field'       => 'secret_key',
            'type'        => 'password',
            'description' => __('The secret key from your Cap dashboard (not the admin key).', 'cap-captcha'),
        )
    );

    add_settings_section(
        'cap_captcha_forms_section',
        __('Form Protection', 'cap-captcha'),
        '__return_null',
        'cap_captcha_settings'
    );

    add_settings_field(
        'protect_login',
        __('Login', 'cap-captcha'),
        'cap_captcha_checkbox_render',
        'cap_captcha_settings',
        'cap_captcha_forms_section',
        array(
            'field' => 'protect_login',
            'label' => __('Protect the login form', 'cap-captcha'),
        )
    );

    add_settings_field(
        'protect_register',
        __('Registration', 'cap-captcha'),
        'cap_captcha_checkbox_render',
        'cap_captcha_settings',
        'cap_captcha_forms_section',
        array(
            'field' => 'protect_register',
            'label' => __('Protect the registration form', 'cap-captcha'),
        )
    );

    add_settings_field(
        'protect_lostpassword',
        __('Lost Password', 'cap-captcha'),
        'cap_captcha_checkbox_render',
        'cap_captcha_settings',
        'cap_captcha_forms_section',
        array(
            'field' => 'protect_lostpassword',
            'label' => __('Protect the lost-password and reset-password forms', 'cap-captcha'),
        )
    );

    add_settings_field(
        'protect_comments',
        __('Comments', 'cap-captcha'),
        'cap_captcha_checkbox_render',
        'cap_captcha_settings',
        'cap_captcha_forms_section',
        array(
            'field' => 'protect_comments',
            'label' => __('Protect the comment form', 'cap-captcha'),
        )
    );

    add_settings_section(
        'cap_captcha_styling_section',
        __('Custom Styling', 'cap-captcha'),
        'cap_captcha_styling_section_callback',
        'cap_captcha_settings'
    );

    $color_fields = array(
        'cap_background'              => __('Background', 'cap-captcha'),
        'cap_color'                   => __('Text Color', 'cap-captcha'),
        'cap_border_color'            => __('Border Color', 'cap-captcha'),
        'cap_checkbox_background'     => __('Checkbox Background', 'cap-captcha'),
        'cap_spinner_color'           => __('Spinner Color', 'cap-captcha'),
        'cap_spinner_background'      => __('Spinner Background', 'cap-captcha'),
    );

    foreach ($color_fields as $field => $label) {
        add_settings_field(
            $field,
            $label,
            'cap_captcha_color_render',
            'cap_captcha_settings',
            'cap_captcha_styling_section',
            array('field' => $field)
        );
    }

    add_settings_field(
        'cap_checkbox_border',
        __('Checkbox Border', 'cap-captcha'),
        'cap_captcha_border_render',
        'cap_captcha_settings',
        'cap_captcha_styling_section'
    );
}

function cap_captcha_sanitize_options(array $input): array
{
    $options = get_option('cap_captcha_options', array());

    if (isset($input['instance_url'])) {
        $options['instance_url'] = untrailingslashit(esc_url_raw($input['instance_url']));
    }
    if (isset($input['site_key'])) {
        $options['site_key'] = sanitize_text_field(wp_unslash($input['site_key']));
    }
    if (! empty($input['secret_key'])) {
        $options['secret_key'] = sanitize_text_field(wp_unslash($input['secret_key']));
    }

    $protect_fields = array('protect_login', 'protect_register', 'protect_lostpassword', 'protect_comments');
    foreach ($protect_fields as $field) {
        $options[$field] = ! empty($input[$field]) ? 1 : 0;
    }

    $color_fields = array(
        'cap_background',
        'cap_color',
        'cap_border_color',
        'cap_checkbox_background',
        'cap_spinner_color',
        'cap_spinner_background',
    );
    foreach ($color_fields as $field) {
        if (isset($input[$field])) {
            $color = sanitize_hex_color($input[$field]);
            $options[$field] = ! empty($color) ? $color : '';
        }
    }

    if (isset($input['cap_checkbox_border'])) {
        $color = sanitize_hex_color($input['cap_checkbox_border']);
        $options['cap_checkbox_border'] = ! empty($color) ? $color : '';
    }

    $options['cap_checkbox_border_advanced'] = ! empty($input['cap_checkbox_border_advanced']) ? 1 : 0;

    if (isset($input['cap_checkbox_border_custom'])) {
        $options['cap_checkbox_border_custom'] = sanitize_text_field(wp_unslash($input['cap_checkbox_border_custom']));
    } elseif (empty($options['cap_checkbox_border_advanced'])) {
        $options['cap_checkbox_border_custom'] = '';
    }

    return $options;
}

function cap_captcha_styling_section_callback(): void
{
    echo '<p class="description">' . esc_html__('Optional: override widget colors for dark themes or brand consistency. Enter hex values including the # prefix. Leave blank to use widget defaults.', 'cap-captcha') . '</p>';
}

function cap_captcha_color_render(array $args): void
{
    $options = get_option('cap_captcha_options');
    $value   = $options[$args['field']] ?? '';
    $css_var = str_replace('_', '-', $args['field']);
    ?>
	<div style="display: flex; align-items: center; gap: 8px;">
		<input
			type="text"
			name="cap_captcha_options[<?php echo esc_attr($args['field']); ?>]"
			value="<?php echo esc_attr($value); ?>"
			class="cap-captcha-color-picker"
			data-default-color="" />
		<code style="font-size: 11px; color: #666;">--<?php echo esc_html($css_var); ?></code>
	</div>
<?php
}

function cap_captcha_border_render(): void
{
    $options            = get_option('cap_captcha_options');
    $color              = $options['cap_checkbox_border'] ?? '';
    $advanced           = ! empty($options['cap_checkbox_border_advanced']);
    $custom             = $options['cap_checkbox_border_custom'] ?? '';
    $advanced_hidden    = $advanced ? '' : ' style="display:none;"';
    $simple_hidden      = $advanced ? ' style="display:none;"' : '';
    ?>
	<div id="cap-border-simple" <?php echo esc_attr($simple_hidden); ?>>
		<div style="display: flex; align-items: center; gap: 8px;">
			<input
				type="text"
				name="cap_captcha_options[cap_checkbox_border]"
				value="<?php echo esc_attr($color); ?>"
				class="cap-captcha-color-picker"
				data-default-color="" />
			<code style="font-size: 11px; color: #666;">--cap-checkbox-border (1px solid &lt;color&gt;)</code>
		</div>
		<p class="description"><?php echo esc_html__('Pick a color. The plugin will wrap it as 1px solid your color.', 'cap-captcha'); ?></p>
	</div>
	<div id="cap-border-advanced" <?php echo esc_attr($advanced_hidden); ?>>
		<div style="display: flex; align-items: center; gap: 8px;">
			<input
				type="text"
				name="cap_captcha_options[cap_checkbox_border_custom]"
				value="<?php echo esc_attr($custom); ?>"
				placeholder="2px solid #e94560"
				class="regular-text code"
				style="width: 220px; font-family: monospace;" />
			<code style="font-size: 11px; color: #666;">--cap-checkbox-border</code>
		</div>
		<p class="description"><?php echo esc_html__('Full CSS border value. Overrides the simple color picker above.', 'cap-captcha'); ?></p>
	</div>
	<label style="margin-top: 6px; display: inline-block;">
		<input type="checkbox" name="cap_captcha_options[cap_checkbox_border_advanced]" value="1" <?php checked($advanced); ?> />
		<?php echo esc_html__('Use custom border value', 'cap-captcha'); ?>
	</label>
	<script>
		jQuery(function($) {
			var $cb = $('input[name="cap_captcha_options[cap_checkbox_border_advanced]"]');
			$cb.on('change', function() {
				$('#cap-border-simple, #cap-border-advanced').toggle();
			});
		});
	</script>
<?php
}

function cap_captcha_field_render(array $args): void
{
    $options = get_option('cap_captcha_options');
    $value   = $options[$args['field']] ?? '';
    ?>
	<input
		type="<?php echo esc_attr($args['type']); ?>"
		name="cap_captcha_options[<?php echo esc_attr($args['field']); ?>]"
		value="<?php echo esc_attr($value); ?>"
		class="regular-text code" />
	<?php if (! empty($args['description'])) : ?>
		<p class="description"><?php echo esc_html($args['description']); ?></p>
	<?php endif; ?>
<?php
}

function cap_captcha_checkbox_render(array $args): void
{
    $options = get_option('cap_captcha_options');
    ?>
	<label>
		<input
			type="checkbox"
			name="cap_captcha_options[<?php echo esc_attr($args['field']); ?>]"
			value="1"
			<?php checked($options[$args['field']] ?? false); ?>
		<?php echo esc_html($args['label']); ?>
	</label>
<?php
}

function cap_captcha_options_page(): void
{
    ?>
	<div class="wrap">
		<h1><?php echo esc_html__('Cap Captcha Settings', 'cap-captcha'); ?></h1>
		<form action="options.php" method="post">
			<?php
                settings_fields('cap_captcha_settings');
    do_settings_sections('cap_captcha_settings');
    submit_button();
    ?>
		</form>
	</div>
<?php
}

function cap_captcha_plugin_action_links(array $links): array
{
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=cap-captcha')) . '">'
        . esc_html__('Settings', 'cap-captcha')
        . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
