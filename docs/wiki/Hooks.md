# Hooks

What the theme changes in Genesis's and WordPress's hooks. A plugin or a site snippet that unhooks or reorders one of these needs the same priority.

## Layout and markup (`functions.php`)

| Change | Hook | Priority |
| --- | --- | --- |
| Primary navigation moved into the header | `genesis_do_nav` from `genesis_after_header` to `genesis_header` | 12 |
| Secondary navigation moved into the footer | `genesis_do_subnav` from `genesis_after_header` to `genesis_footer` | 5 |
| Post info moved above the title | `genesis_post_info` from priority 12 to 9 on `genesis_entry_header` | 9 |
| Post meta moved under the title | `genesis_post_meta` from `genesis_entry_footer` to `genesis_entry_header` | 14 |
| Footer menu limited to one level | filter `wp_nav_menu_args` (`brief_secondary_menu_args`) | 10 |
| Edit link removed | filter `genesis_edit_post_link` returns `false` | 10 |
| "Continue Reading" link | filter `get_the_content_more_link` (`brief_read_more_link`) | 10 |
| Author box avatar 90 pixels | filter `genesis_author_box_gravatar_size` | 10 |
| Comment avatars 60 pixels | filter `genesis_comment_list_args` | 10 |

Also at load: the `header-right` and `sidebar-alt` widget areas are unregistered, and the three two-sidebar layouts (`content-sidebar-sidebar`, `sidebar-content-sidebar`, `sidebar-sidebar-content`) are removed. Two image sizes are added: `sidebar-featured` (75 by 75, cropped) and `genesis-singular-images` (702 by 526, cropped).

## Setup and assets

| What | Hook | Priority |
| --- | --- | --- |
| Theme supports from [`theme-supports.php`](Configuration.md#theme-supportsphp) | `after_setup_theme` | 9 |
| Post type supports | `after_setup_theme` | 9 |
| Text domain loaded from `languages/` | `after_setup_theme` | 10 |
| Block editor support (`lib/gutenberg/init.php`) | `after_setup_theme` | 10 |
| Content width | `after_setup_theme` | 0 |
| Fonts, Dashicons and, on AMP pages, `lib/amp/amp.css` | `wp_enqueue_scripts` | 10 |
| Customizer CSS (`lib/output.php`) | `wp_enqueue_scripts` | 10 |
| Block styles on the front end and in the editor | `wp_enqueue_scripts`, `enqueue_block_editor_assets` | 10 |
| Block body classes | filter `body_class` | 10 |
| Customizer settings | `customize_register` | 10 |
| Simple Social Icons defaults | filter `simple_social_default_styles` | 10 |

## Page templates

The [Blocks and Landing templates](Page-templates.md) remove hooks only while their page renders: Blocks removes the entry header markup, the title and the breadcrumbs and forces the full-width layout; Landing removes the skip links and the header and footer markup, and drops `genesis-menus` support.

## WooCommerce

Only meaningful while WooCommerce is active; see [WooCommerce](WooCommerce.md).

| What | Hook |
| --- | --- |
| Theme stylesheet added | filter `woocommerce_enqueue_styles` |
| Inline color CSS | `wp_enqueue_scripts` |
| Small-screen breakpoint | filter `woocommerce_style_smallscreen_breakpoint` |
| Products per page | filter `genesiswooc_products_per_page` |
| Pagination labels | filter `woocommerce_pagination_args` |
| Gallery thumbnail size | filter `woocommerce_get_image_size_gallery_thumbnail` |
| Description heading removed | filter `woocommerce_product_description_heading` |
| Image sizes written | `after_switch_theme` (priority 1), `activated_plugin` |
| Genesis Connect notice | `admin_notices`, `admin_print_styles`, `admin_enqueue_scripts`, `wp_ajax_brief_dismiss_woocommerce_notice` |
| Notice reset | `switch_theme`, `deactivated_plugin` |

<!-- Product, identifier and organization names, kept as spelled: cspell:ignore Customizer Dashicons genesiswooc smallscreen subnav woocommerce -->
