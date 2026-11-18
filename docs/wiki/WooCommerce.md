# WooCommerce

The theme works without WooCommerce. When WooCommerce is active, it adds the following.

## What changes

- **Product galleries** get lightbox, slider and zoom support.
- **Styles:** the theme adds its stylesheet `lib/woocommerce/woocommerce-styles.css` to the ones WooCommerce loads, and, when the Customizer's text color differs from the default, applies it to WooCommerce elements in an inline style.
- **Small-screen breakpoint:** `1200px` on a layout with one sidebar, `860px` otherwise.
- **Products per page:** 8, through Genesis Connect for WooCommerce.
- **Pagination labels:** *Previous Page* and *Next Page*.
- **Gallery thumbnails:** 180 by 180 pixels, cropped.
- **The product description heading** is removed.

## Image sizes set on activation

When the theme is activated from *Appearance*, *Themes* with WooCommerce already active, or WooCommerce is activated while the theme is active, the theme writes these WooCommerce settings once:

| WooCommerce option | Value |
| --- | --- |
| `woocommerce_single_image_width` | `655` |
| `woocommerce_thumbnail_image_width` | `500` |
| `woocommerce_thumbnail_cropping` | `1:1` |

**Warning:** this overwrites values set by hand under *WooCommerce*, *Settings*. Change them again afterward if the site needs other sizes.

## The Genesis Connect notice

While WooCommerce is active and Genesis Connect for WooCommerce is not, users who can manage WooCommerce see an admin notice asking them to install and activate it. A user who can install plugins gets an install link in the notice. Dismissing the notice hides it for that user; it returns for everyone when the theme is switched, or when WooCommerce or Genesis Connect is deactivated. The theme also removes WooCommerce's own theme-support notice.

## Reference

- [Hooks](Hooks.md#woocommerce)
- [Troubleshooting](Troubleshooting.md#please-install-and-activate-genesis-connect-for-woocommerce)

<!-- Product, identifier and organization names, kept as spelled: cspell:ignore Customizer lightbox woocommerce -->
