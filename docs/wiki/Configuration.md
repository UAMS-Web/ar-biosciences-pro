# Configuration

The theme keeps its settings in PHP files under `config/`, each returning an array. Genesis reads them by name with `genesis_get_config('<file name without .php>')`. Change a value here, in a pull request, rather than in code that reads it.

| File | What it sets | Read by |
| --- | --- | --- |
| [`appearance.php`](#appearancephp) | fonts, content width, colors, editor palette and font sizes | the theme, in many places |
| [`theme-supports.php`](#theme-supportsphp) | the theme features registered with `add_theme_support()` | `functions.php` |
| [`post-type-supports.php`](#post-type-supportsphp) | post type features | `functions.php` |
| [`responsive-menus.php`](#responsive-menusphp) | the Genesis responsive menu script | `functions.php` |
| [`simple-social-icons-settings.php`](#simple-social-icons-settingsphp) | Simple Social Icons defaults | `lib/theme-defaults.php` |
| [`child-theme-settings.php`](#child-theme-settingsphp) | Genesis and WordPress settings applied on theme activation | Genesis |
| `child-theme-settings-genesis.php` | an earlier set of Genesis setting values | nothing in the theme reads it by name |
| `accessibility.php`, `custom-logo.php`, `html5.php`, `menus.php` | lists that repeat entries of `theme-supports.php` | nothing in the theme reads them by name |

## `appearance.php`

| Key | Value | Effect |
| --- | --- | --- |
| `fonts-url` | a Google Fonts stylesheet for Fira Sans | enqueued on every page |
| `content-width` | `1062` | the global `$content_width` |
| `default-colors.text` | `#333` | default for the Customizer's Text Color |
| `default-colors.link` | `#fff385` | default for the Customizer's Link Color |
| `text-color`, `link-color`, `button-color` | the saved theme mods, or the defaults | used in the generated CSS |
| `editor-color-palette` | *Text color* (`theme-primary`), *Link color* (`theme-secondary`) | the block editor palette |
| `editor-font-sizes` | Small 14.4, Normal 18, Large 20, Larger 24 | the block editor font sizes |

## `theme-supports.php`

Each key is passed to `add_theme_support()` with its value as the arguments: `genesis-custom-logo` (700 by 120, flexible both ways), `html5`, `genesis-accessibility` (drop-down menu, headings, search form, skip links), `genesis-after-entry-widget-area`, `genesis-footer-widgets` (one area) and `genesis-menus` (*Header Menu* as `primary`, *Footer Menu* as `secondary`).

## `post-type-supports.php`

Adds `genesis-singular-images` to posts and pages, so each can show its featured image on the single view.

## `responsive-menus.php`

Makes `.nav-primary` the responsive menu, with no menu label, and switches to it below `960px`.

## `simple-social-icons-settings.php`

Defaults for the Simple Social Icons widget: centered, white backgrounds, no border, icons `#333333` and `#666666` on hover, 40 pixels.

## `child-theme-settings.php`

Values Genesis applies when the theme is activated: no breadcrumbs, full content on archives with a 220-character limit, medium left-aligned images, numeric post navigation, the full-width layout, the date before the content and categories and tags after it, and 4 posts per page.

## Reference

- [Customizing the theme](Customizing-the-theme.md)
- [Hooks](Hooks.md)

<!-- Product, identifier and organization names, kept as spelled: cspell:ignore Customizer Fira -->
