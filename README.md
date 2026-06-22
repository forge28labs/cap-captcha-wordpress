# Cap Captcha for WordPress

![GitHub Release](https://img.shields.io/github/v/release/forge28labs/cap-captcha-wordpress) ![GitHub License](https://img.shields.io/github/license/forge28labs/cap-captcha-wordpress)

An unofficial WordPress plugin that integrates Cap Captcha with WordPress and its comment/authentication flows.

> This project is not affiliated with or endorsed by the Cap Captcha team.

## What is Cap?

Cap is a self-hosted proof-of-work CAPTCHA system. It allows you to protect your WordPress forms from bots by requiring users to solve a CAPTCHA challenge. Unlike traditional CAPTCHAs, Cap does not rely on third-party services or user data collection. Instead, it uses a proof-of-work algorithm to verify that the user is human. You can learn more about Cap at https://trycap.dev - we'd also recommend reading the [effectiveness page](https://trycap.dev/guide/effectiveness.html) for more information about how it works and why it's effective.

## Hosting a Cap Instance

To use this plugin, you need to host your own Cap instance. You can do this by following the instructions on the [Cap documentation](https://trycap.dev/guide/standalone/).

## Features

- Protects WordPress login, registration, lost password, and comment forms
- WordPress-native settings page
- Lightweight implementation
- Open source
- Support for 3rd-party form builders (Formidable for now, more coming soon)

## Requirements

- WordPress 7.0+
- PHP 7.4+
- A Cap Captcha instance and API credentials

## Installation

1. Download the latest release.
2. Upload the plugin to `/wp-content/plugins/`.
3. Activate the plugin from the WordPress admin panel.
4. Enter your Cap Captcha credentials in the plugin settings.
5. Enable protection on the desired forms.

## Form Builder Integrations

### Formidable / WP Forms

Just pop in and go! The plugin is registered as a field type in these plugins, so you can add it to any of your forms. It will then protect that form with Cap Captcha. All settings are configured in the plugin settings page.

## Development

```bash
git clone https://github.com/forge28labs/cap-captcha-wordpress.git
```

No external dependencies are required. Just clone the repository into the `wp-content/plugins/` directory and activate the plugin in WordPress.

## Contributing

Issues and pull requests are welcome.

## Disclaimer

This project is not affiliated with or endorsed by Cap Captcha.

## License

Copyright (c) 2026 Forge28

Licensed under GPL-2.0-or-later.

This plugin includes third-party components. See THIRD-PARTY-NOTICES.txt for full license details.
