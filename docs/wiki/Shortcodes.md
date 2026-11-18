# Shortcodes

The theme registers one shortcode.

## `[abi_impact_report]`

Embeds the Arkansas Biosciences Institute impact report in the page, in a frame that fills the content width and the height of the browser window. It takes no attributes.

```text
[abi_impact_report]
```

Place it in a Shortcode block, or anywhere shortcodes run. The frame loads lazily, so it costs nothing until the reader scrolls to it.

**Note:** the shortcode is registered by the theme, so it stops working, and shows as plain text, on a site that switches to another theme.

## Reference

- Registered in `functions.php` with `add_shortcode()`.
- [Page templates](Page-templates.md): the Blocks template suits a page that holds only the report.
