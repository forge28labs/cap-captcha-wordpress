=== Cap Captcha ===
Contributors: forge28, rtstubbs
Tags: captcha, cap captcha, security, spam protection, bot protection
Donate link: https://ryanstubbs.co.uk/support-my-work/
Stable tag: 1.2.0
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Self-hosted proof-of-work CAPTCHA for WordPress login, registration, comments, and form plugins.

== Description ==
Cap Captcha is a self-hosted proof-of-work CAPTCHA system for WordPress. It helps protect forms from spam and bots without relying on third-party tracking services.

== Installation ==
1. Download the latest release zip file.
2. Upload the plugin to `/wp-content/plugins/` or install via the WordPress plugin uploader.
3. Activate the plugin in the WordPress admin panel.
4. Go to Settings → Cap Captcha and enter your Instance URL, Site Key, and Secret Key.
5. Enable protection for the desired forms.

== Frequently Asked Questions ==
= Does this plugin use third-party CAPTCHA services? =
No. Cap Captcha is self-hosted. You run your own Cap instance and the plugin communicates directly with it.

= Does it track users? =
No. The system uses proof-of-work verification and does not rely on user tracking or behavioural profiling.

= Which forms are supported? =
WordPress login, registration, password reset, comments, and supported form builders such as WPForms and Formidable Forms.

= Do I need an API key? =
Yes. You need a Cap instance URL, site key, and secret key from your self-hosted Cap server.