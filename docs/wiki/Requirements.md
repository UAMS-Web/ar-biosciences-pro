# Requirements

What the theme needs to run, and the plugins it works with when they are present.

| Requirement | Version | Where it is declared |
| --- | --- | --- |
| WordPress | 6.8 or later | `Requires at least:` in `style.css` |
| PHP | 8.2 or later | `Requires PHP:` in `style.css` |
| Genesis Framework | installed in the `genesis` theme folder | `Template: genesis` in `style.css` |

The theme is a Genesis child theme: Genesis must be in `wp-content/themes/genesis/` beside it, and the theme loads Genesis's `lib/init.php` before anything else. Activate the child theme, not Genesis.

## Optional plugins

The theme checks for each of these and changes nothing when it is absent.

| Plugin | What the theme does with it |
| --- | --- |
| WooCommerce | Adds product gallery support, styles and image sizes. See [WooCommerce](WooCommerce.md). |
| Genesis Connect for WooCommerce | Expected beside WooCommerce; the theme shows an admin notice asking for it until it is active. |
| Simple Social Icons | Supplies the widget's default styles (alignment, colors, size). See [Configuration](Configuration.md#simple-social-icons-settingsphp). |

## Next

- [Installation (README)](https://github.com/UAMS-Web/ar-biosciences-pro/blob/main/README.txt)
- [Customizing the theme](Customizing-the-theme.md)
