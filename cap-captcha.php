<?php

/**
 * Plugin Name:     Cap Captcha
 * Plugin URI:      https://github.com/forge28labs/cap-captcha-wordpress
 * Description:     Protects WordPress forms (login, register, lost password, comments) with Cap, a self-hosted proof-of-work CAPTCHA.
 * Version:         1.0.0
 * Requires at least: 7.0
 * Requires PHP:    7.4
 * Author:          Forge28
 * Author URI:      https://forge28.com
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     cap-captcha
 * Domain Path:     /languages
 */

defined('ABSPATH') || exit;

define('CAP_CAPTCHA_VERSION', '1.0.0');
define('CAP_CAPTCHA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CAP_CAPTCHA_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once CAP_CAPTCHA_PLUGIN_DIR . 'includes/admin-settings.php';
require_once CAP_CAPTCHA_PLUGIN_DIR . 'includes/frontend.php';
require_once CAP_CAPTCHA_PLUGIN_DIR . 'includes/verification.php';

add_action('plugins_loaded', function () {
    if (class_exists('FrmFieldType')) {
        require_once CAP_CAPTCHA_PLUGIN_DIR . 'includes/integrations/formidable/formidable-integration.php';
    }
});
