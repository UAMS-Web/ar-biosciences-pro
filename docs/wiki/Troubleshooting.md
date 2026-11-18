# Troubleshooting

- [You must supply a valid number](#you-must-supply-a-valid-number)
- [The logo width cannot be less than 100](#the-logo-width-cannot-be-less-than-100)
- [Please install and activate Genesis Connect for WooCommerce](#please-install-and-activate-genesis-connect-for-woocommerce)
- [The impact report shows as text](#the-impact-report-shows-as-text)
- [WooCommerce image sizes changed by themselves](#woocommerce-image-sizes-changed-by-themselves)

## You must supply a valid number

**Where:** under *Logo Width* in the Customizer's *Site Identity* section, and *Publish* is refused.

**Cause:** the field is empty or holds something that is not a number.

**Fix:** enter a whole number of pixels, `100` or more. See [Customizing the theme](Customizing-the-theme.md).

## The logo width cannot be less than 100

**Where:** under *Logo Width*, and *Publish* is refused.

**Cause:** the theme does not accept a logo narrower than 100 pixels.

**Fix:** enter `100` or more. To show a smaller logo, upload a smaller image instead.

## Please install and activate Genesis Connect for WooCommerce

**Where:** an information notice at the top of the dashboard, while WooCommerce is active.

**Cause:** the theme expects Genesis Connect for WooCommerce beside WooCommerce, and it is not active.

**Fix:** install and activate it (the notice carries an install link for a user who can install plugins), or dismiss the notice. A dismissed notice comes back for every user when the theme is switched, or when WooCommerce or Genesis Connect is deactivated. See [WooCommerce](WooCommerce.md#the-genesis-connect-notice).

## The impact report shows as text

**Where:** a page shows `[abi_impact_report]` instead of the report.

**Cause:** the shortcode is registered by this theme, and another theme is active, or the shortcode sits somewhere shortcodes do not run.

**Fix:** activate this theme, and put the shortcode in a Shortcode block. See [Shortcodes](Shortcodes.md).

## WooCommerce image sizes changed by themselves

**Where:** *WooCommerce*, *Settings*, *Products*: the single image width reads `655`, the thumbnail width `500` and the cropping `1:1`.

**Cause:** the theme writes those values when it is activated with WooCommerce active, or when WooCommerce is activated under it.

**Fix:** set the values again after activation. See [WooCommerce](WooCommerce.md#image-sizes-set-on-activation).

<!-- Product, identifier and organization names, kept as spelled: cspell:ignore Customizer -->
