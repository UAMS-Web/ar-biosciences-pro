# Page templates

The theme adds two page templates, chosen per page under *Template* in the page's settings.

| Template | File | Use it for |
| --- | --- | --- |
| Blocks | `page-templates/blocks.php` | a page built entirely from blocks, where the blocks supply the title |
| Landing | `page-templates/landing.php` | a stand-alone page with no site header, navigation or footer |

## Blocks

The page is laid out full width, and the entry title and its header markup are removed, as are the breadcrumbs. Use it when the first block on the page is the page's heading.

## Landing

The site header, the navigation menus (the theme drops `genesis-menus` support on this template), the footer and the skip links are removed, and the page's `body` gets the class `landing-page`. Use it for a campaign or event page that should not lead visitors elsewhere on the site.

**Note:** with the skip links gone, the page has no "skip to content" link. Keep the page's own structure simple enough that a keyboard user does not need one.

## Classes added to every singular page

On a single post or page, the theme adds body classes that the block styles use:

- `has-no-blocks` when the content has no blocks.
- `first-block-<block name>`, naming the first block with its slash as a hyphen (`first-block-core-cover`).
- `first-block-align-<alignment>` when the first block has an alignment set (`first-block-align-full`), so the layout can adjust the space above a wide or full-width block.

## Reference

- [Hooks](Hooks.md#page-templates)
- [Customizing the theme](Customizing-the-theme.md)
