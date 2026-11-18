# Customizing the theme

By the end of this page the site will use your own text color, link color and logo width. All three are set in the Customizer and saved as theme mods, so they survive a theme update.

1. **Open the Customizer.** In the dashboard, go to *Appearance*, *Customize*.
2. **Set the text color.** Open *Colors* and choose *Text Color*. It is used for body text, headings, button backgrounds and other text. The default is `#333`.
3. **Set the link color.** In the same *Colors* section, choose *Link Color*. It sets the link highlight color. The default is `#fff385`.
4. **Set the logo width.** Open *Site Identity* and enter *Logo Width*, the maximum width of the logo in pixels. The default is `350`, and the field accepts nothing below `100`.
5. **Publish.** The preview updates as you go; *Publish* saves the settings.

**Note:** both colors also appear in the block editor's color palette, as *Text color* and *Link color*, so content in the editor can use the same values.

## Reference

| Customizer control | Section | Theme mod | Default | Saved as |
| --- | --- | --- | --- | --- |
| Text Color | Colors | `brief_text_color` | `#333` | a hex color (`sanitize_hex_color`) |
| Link Color | Colors | `brief_link_color` | `#fff385` | a hex color (`sanitize_hex_color`) |
| Logo Width | Site Identity | `brief_logo_width` | `350` | a whole number (`absint`), at least 100 |

The color defaults are set in [`config/appearance.php`](Configuration.md#appearancephp). An invalid logo width is refused with a message: see [Troubleshooting](Troubleshooting.md#you-must-supply-a-valid-number).

## Further

- [Page templates](Page-templates.md)
- [Configuration](Configuration.md)

<!-- Product, identifier and organization names, kept as spelled: cspell:ignore absint Customizer -->
