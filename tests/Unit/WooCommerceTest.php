<?php

/**
 * Covers `lib/woocommerce/woocommerce-setup.php`, `woocommerce-output.php` and
 * `woocommerce-notice.php`.
 *
 * `class_exists()` and `function_exists()` are redefinable through `patchwork.json`,
 * so each test sets whether WooCommerce, `WC_Admin_Notices` or Genesis Connect is
 * present. The files register hooks and theme support as they load, and the first
 * test is the one that loads them, so it must stay first: any test run before it
 * loads the files without the expectations.
 *
 * Mutation notes: the first `$woo_css .= ...` in `woocommerce-output.php`
 * (`ConcatEqualToEqual`) is equivalent, since `$woo_css` is the empty string there
 * and `=` and `.=` give the same result. It is annotated in the source.
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Actions\expectAdded as expectActionAdded;
use function Brain\Monkey\Filters\expectAdded as expectFilterAdded;
use function Brain\Monkey\Functions\when;
use function Patchwork\relay;

require_once __DIR__.'/../Stubs/WC_Admin_Notices.php';

/**
 * Keep the strings of a dataset's list, which Pest hands the test untyped.
 *
 * @param  array<mixed>  $values
 * @return list<string>
 */
function brief_string_list(array $values): array
{
    return array_values(array_filter($values, is_string(...)));
}

/**
 * Build the URL the theme notice's install link is made from: the stand-in for `add_query_arg()`.
 *
 * @param  array<string, string>  $args
 */
function brief_query_arg_stub(array $args, string $url): string
{
    return sprintf('%s?action=%s&plugin=%s', $url, $args['action'], $args['plugin']);
}

/**
 * Report which classes and functions exist.
 *
 * @param  array<string>  $classes  Classes that exist.
 * @param  array<string>  $functions  Functions that exist.
 */
function brief_woocommerce_exists(array $classes, array $functions = []): void
{
    // Brain Monkey asks `function_exists()` too, as it defines each stub, so a name the
    // theme does not ask about goes to the real function.
    when('class_exists')->alias(
        static fn (string $name): bool => in_array($name, ['WooCommerce', 'WC_Admin_Notices'], true)
            ? in_array($name, $classes, true)
            : relay(func_get_args()) === true
    );
    when('function_exists')->alias(
        static fn (string $name): bool => $name === 'gencwooc_setup'
            ? in_array($name, $functions, true)
            : relay(func_get_args()) === true
    );
}

/**
 * Load the three files with WooCommerce present.
 */
function brief_load_woocommerce(): void
{
    brief_woocommerce_exists(['WooCommerce']);
    when('add_theme_support')->justReturn();

    require_once __DIR__.'/../../lib/woocommerce/woocommerce-setup.php';
    require_once __DIR__.'/../../lib/woocommerce/woocommerce-output.php';
    require_once __DIR__.'/../../lib/woocommerce/woocommerce-notice.php';
}

test('loading the files hooks every function and declares the gallery support', function (): void {
    brief_woocommerce_exists(['WooCommerce']);

    expectFilterAdded('woocommerce_style_smallscreen_breakpoint')->once()->with('brief_woocommerce_breakpoint');
    expectFilterAdded('genesiswooc_products_per_page')->once()->with('brief_default_products_per_page');
    expectFilterAdded('woocommerce_pagination_args')->once()->with('brief_woocommerce_pagination');
    expectFilterAdded('woocommerce_get_image_size_gallery_thumbnail')->once()->with('brief_gallery_image_thumbnail');
    expectFilterAdded('woocommerce_product_description_heading')->once()->with('__return_null');
    expectFilterAdded('woocommerce_enqueue_styles')->once()->with('brief_woocommerce_styles');
    expectActionAdded('after_switch_theme')->once()->with('brief_woocommerce_image_dimensions_after_theme_setup', 1);
    expectActionAdded('activated_plugin')->once()->with('brief_woocommerce_image_dimensions_after_woo_activation', 10, 2);
    expectActionAdded('wp_enqueue_scripts')->once()->with('brief_woocommerce_css');
    expectActionAdded('admin_print_styles')->once()->with('brief_remove_woocommerce_notice');
    expectActionAdded('admin_notices')->once()->with('brief_woocommerce_theme_notice');
    expectActionAdded('wp_ajax_brief_dismiss_woocommerce_notice')->once()->with('brief_dismiss_woocommerce_notice');
    expectActionAdded('admin_enqueue_scripts')->once()->with('brief_notice_script');
    expectActionAdded('switch_theme')->once()->with('brief_reset_woocommerce_notice', 10, 2);
    expectActionAdded('deactivated_plugin')->once()->with('brief_reset_woocommerce_notice_on_deactivation');

    Functions\expect('add_theme_support')->once()->with('wc-product-gallery-lightbox');
    Functions\expect('add_theme_support')->once()->with('wc-product-gallery-slider');
    Functions\expect('add_theme_support')->once()->with('wc-product-gallery-zoom');

    require_once __DIR__.'/../../lib/woocommerce/woocommerce-setup.php';
    require_once __DIR__.'/../../lib/woocommerce/woocommerce-output.php';
    require_once __DIR__.'/../../lib/woocommerce/woocommerce-notice.php';
});

test('the breakpoint widens for a one-sidebar layout', function (string $layout, string $expected): void {
    brief_load_woocommerce();
    Functions\expect('genesis_site_layout')->once()->with(false)->andReturn($layout);

    expect(brief_woocommerce_breakpoint())->toBe($expected);
})->with([
    'content then sidebar' => ['content-sidebar', '1200px'],
    'sidebar then content' => ['sidebar-content', '1200px'],
    'full width' => ['full-width-content', '860px'],
    'two sidebars' => ['sidebar-content-sidebar', '860px'],
]);

test('products per page is 8', function (): void {
    brief_load_woocommerce();

    expect(brief_default_products_per_page())->toBe(8);
});

test('pagination uses the Genesis arrows and keeps the other arguments', function (): void {
    brief_load_woocommerce();
    when('__')->returnArg(1);

    expect(brief_woocommerce_pagination(['prev_text' => 'old', 'next_text' => 'old', 'end_size' => 3]))->toBe([
        'prev_text' => '&laquo; Previous Page',
        'next_text' => 'Next Page &raquo;',
        'end_size' => 3,
    ]);
});

test('the gallery thumbnail is 180 pixels square and cropped', function (): void {
    brief_load_woocommerce();

    expect(brief_gallery_image_thumbnail())->toBe(['width' => 180, 'height' => 180, 'crop' => 1]);
});

test('image sizes are set when activating the theme with WooCommerce installed', function (): void {
    brief_load_woocommerce();
    $GLOBALS['pagenow'] = 'themes.php';
    $_GET['activated'] = 'true';
    Functions\expect('update_option')->once()->with('woocommerce_single_image_width', 655);
    Functions\expect('update_option')->once()->with('woocommerce_thumbnail_image_width', 500);
    Functions\expect('update_option')->once()->with('woocommerce_thumbnail_cropping', '1:1');

    brief_woocommerce_image_dimensions_after_theme_setup();

    unset($GLOBALS['pagenow'], $_GET['activated']);
});

test('image sizes are left alone unless the theme is being activated with WooCommerce', function (
    ?string $activated,
    string $pagenow,
    bool $woocommerce
): void {
    brief_load_woocommerce();
    brief_woocommerce_exists($woocommerce ? ['WooCommerce'] : []);
    $GLOBALS['pagenow'] = $pagenow;

    if ($activated !== null) {
        $_GET['activated'] = $activated;
    }

    Functions\expect('update_option')->never();

    brief_woocommerce_image_dimensions_after_theme_setup();

    unset($GLOBALS['pagenow'], $_GET['activated']);
})->with([
    'not activating' => [null, 'themes.php', true],
    'another admin page' => ['true', 'index.php', true],
    'WooCommerce missing' => ['true', 'themes.php', false],
]);

test('image sizes are set when WooCommerce is activated', function (): void {
    brief_load_woocommerce();
    Functions\expect('update_option')->once()->with('woocommerce_single_image_width', 655);
    Functions\expect('update_option')->once()->with('woocommerce_thumbnail_image_width', 500);
    Functions\expect('update_option')->once()->with('woocommerce_thumbnail_cropping', '1:1');

    brief_woocommerce_image_dimensions_after_woo_activation('woocommerce/woocommerce.php');
});

test('image sizes are not set when another plugin is activated', function (): void {
    brief_load_woocommerce();
    Functions\expect('update_option')->never();

    brief_woocommerce_image_dimensions_after_woo_activation('akismet/akismet.php');
});

test('the theme stylesheet is added to the WooCommerce styles', function (): void {
    brief_load_woocommerce();
    when('genesis_get_theme_handle')->justReturn('brief');
    when('get_stylesheet_directory_uri')->justReturn('https://example.test/theme');
    when('genesis_get_theme_version')->justReturn('1.2.3');

    expect(brief_woocommerce_styles(['woocommerce-general' => ['src' => 'a']]))->toBe([
        'woocommerce-general' => ['src' => 'a'],
        'brief-woocommerce-styles' => [
            'src' => 'https://example.test/theme/lib/woocommerce/woocommerce-styles.css',
            'deps' => '',
            'version' => '1.2.3',
            'media' => 'screen',
        ],
    ]);
});

test('the WooCommerce CSS is skipped when WooCommerce is not active', function (): void {
    brief_load_woocommerce();
    brief_woocommerce_exists([]);
    Functions\expect('genesis_get_config')->never();
    Functions\expect('wp_add_inline_style')->never();

    brief_woocommerce_css();
});

test('the WooCommerce CSS is skipped for the default text color', function (): void {
    brief_load_woocommerce();
    Functions\expect('genesis_get_config')->once()->with('appearance')->andReturn([
        'default-colors' => ['text' => '#333', 'link' => '#fff385'],
    ]);
    Functions\expect('get_theme_mod')->once()->with('brief_text_color', '#333')->andReturn('#333');
    Functions\expect('wp_add_inline_style')->never();

    brief_woocommerce_css();
});

test('a changed text color is added to the WooCommerce stylesheet', function (): void {
    brief_load_woocommerce();
    Functions\expect('genesis_get_config')->once()->with('appearance')->andReturn([
        'default-colors' => ['text' => '#333', 'link' => '#fff385'],
    ]);
    Functions\expect('get_theme_mod')->once()->with('brief_text_color', '#333')->andReturn('#111111');
    when('genesis_get_theme_handle')->justReturn('brief');
    $calls = [];
    when('wp_add_inline_style')->alias(
        static function (string $handle, string $css) use (&$calls): void {
            $calls[] = [$handle, $css];
        }
    );

    brief_woocommerce_css();

    expect($calls)->toHaveCount(1)
        ->and($calls[0][0])->toBe('brief-woocommerce-styles')
        ->and($calls[0][1])->toContain('color: #111111;')
        ->and($calls[0][1])->toContain('background-color: #111111;');
});

test('the Genesis Connect notice is removed only when WC_Admin_Notices exists', function (): void {
    brief_load_woocommerce();
    WC_Admin_Notices::$removed = [];

    brief_woocommerce_exists([]);
    brief_remove_woocommerce_notice();

    expect(WC_Admin_Notices::$removed)->toBe([]);

    brief_woocommerce_exists(['WC_Admin_Notices']);
    brief_remove_woocommerce_notice();

    expect(WC_Admin_Notices::$removed)->toBe(['theme_support']);
});

/**
 * Run the theme notice with the given conditions and return what it echoed.
 *
 * @param  array<string>  $classes
 * @param  array<string>  $functions
 * @param  array<string>  $caps  Capabilities the user holds.
 */
function brief_run_theme_notice(array $classes, array $functions, array $caps, mixed $dismissed): string
{
    brief_woocommerce_exists($classes, $functions);
    when('current_user_can')->alias(static fn (string $cap): bool => in_array($cap, $caps, true));
    when('get_current_user_id')->justReturn(5);
    when('get_user_option')->alias(
        static fn (string $option, int $user): mixed => $option === 'brief_woocommerce_message_dismissed' && $user === 5 ? $dismissed : false
    );
    when('__')->returnArg(1);
    when('wp_kses_post')->returnArg(1);
    when('wp_get_theme')->justReturn(new class
    {
        public function get(string $key): string
        {
            return $key === 'Name' ? 'Brief' : '';
        }
    });
    when('network_admin_url')->alias(static fn (string $path): string => 'https://admin.test/'.$path);
    when('add_query_arg')->alias('brief_query_arg_stub');
    when('wp_nonce_url')->alias(static fn (string $url, string $action): string => $url.'&_wpnonce='.$action);

    ob_start();
    brief_woocommerce_theme_notice();

    return (string) ob_get_clean();
}

test(
    'the theme notice stays quiet unless it applies',
    function (
        array $classes,
        array $functions,
        array $caps,
        mixed $dismissed
    ): void {
        brief_load_woocommerce();

        expect(brief_run_theme_notice(
            brief_string_list($classes),
            brief_string_list($functions),
            brief_string_list($caps),
            $dismissed
        ))->toBe('');
    }
)->with([
    'WooCommerce missing' => [[], [], ['manage_woocommerce'], false],
    'Genesis Connect active' => [['WooCommerce'], ['gencwooc_setup'], ['manage_woocommerce'], false],
    'no WooCommerce access' => [['WooCommerce'], [], [], false],
    'already dismissed' => [['WooCommerce'], [], ['manage_woocommerce'], '1'],
]);

test('the theme notice names the plugin for a user who cannot install plugins', function (): void {
    brief_load_woocommerce();

    expect(brief_run_theme_notice(['WooCommerce'], [], ['manage_woocommerce'], false))->toBe(
        '<div class="notice notice-info is-dismissible brief-woocommerce-notice"><p>'
        .'Please install and activate <a href="https://wordpress.org/plugins/genesis-connect-woocommerce/" target="_blank">Genesis Connect for WooCommerce</a> to <strong>enable WooCommerce support for Brief</strong>.'
        .'</p></div>'
    );
});

test('the theme notice links the installer for a user who can install plugins', function (): void {
    brief_load_woocommerce();

    expect(brief_run_theme_notice(['WooCommerce'], [], ['manage_woocommerce', 'install_plugins'], false))->toBe(
        '<div class="notice notice-info is-dismissible brief-woocommerce-notice"><p>'
        .'Please <a href="https://admin.test/update.php?action=install-plugin&plugin=genesis-connect-woocommerce&_wpnonce=install-plugin_genesis-connect-woocommerce">install and activate Genesis Connect for WooCommerce</a> to <strong>enable WooCommerce support for Brief</strong>.'
        .'</p></div>'
    );
});

test('dismissing the notice records it for the current user', function (): void {
    brief_load_woocommerce();
    when('get_current_user_id')->justReturn(5);
    Functions\expect('update_user_option')->once()->with(5, 'brief_woocommerce_message_dismissed', 1);

    brief_dismiss_woocommerce_notice();
});

test('the dismissal script is enqueued', function (): void {
    brief_load_woocommerce();
    when('get_stylesheet_directory_uri')->justReturn('https://example.test/theme');
    Functions\expect('wp_enqueue_script')->once()->with(
        'brief_notice_script',
        'https://example.test/theme/lib/woocommerce/js/notice-update.js',
        ['jquery'],
        '1.0',
        true
    );

    brief_notice_script();
});

test('switching themes clears every recorded dismissal', function (): void {
    brief_load_woocommerce();
    $GLOBALS['wpdb'] = (object) ['prefix' => 'wp_'];
    Functions\expect('get_users')->once()->with([
        'meta_key' => 'wp_brief_woocommerce_message_dismissed',
        'meta_value' => '1',
        'fields' => 'ID',
    ])->andReturn(['3', '4']);
    Functions\expect('delete_user_option')->once()->with(Mockery::on(static fn (mixed $id): bool => $id === 3), 'brief_woocommerce_message_dismissed');
    Functions\expect('delete_user_option')->once()->with(Mockery::on(static fn (mixed $id): bool => $id === 4), 'brief_woocommerce_message_dismissed');

    brief_reset_woocommerce_notice();

    unset($GLOBALS['wpdb']);
});

test('switching themes with no dismissals deletes nothing', function (): void {
    brief_load_woocommerce();
    $GLOBALS['wpdb'] = (object) ['prefix' => 'wp_'];
    Functions\expect('get_users')->once()->andReturn([]);
    Functions\expect('delete_user_option')->never();

    brief_reset_woocommerce_notice();

    unset($GLOBALS['wpdb']);
});

test('deactivating WooCommerce or Genesis Connect clears the dismissals', function (string $plugin): void {
    brief_load_woocommerce();
    $GLOBALS['wpdb'] = (object) ['prefix' => 'wp_'];
    Functions\expect('get_users')->once()->andReturn([]);

    brief_reset_woocommerce_notice_on_deactivation($plugin);

    unset($GLOBALS['wpdb']);
})->with([
    'WooCommerce' => ['woocommerce/woocommerce.php'],
    'Genesis Connect' => ['genesis-connect-woocommerce/genesis-connect-woocommerce.php'],
]);

test('deactivating another plugin leaves the dismissals', function (): void {
    brief_load_woocommerce();
    Functions\expect('get_users')->never();

    brief_reset_woocommerce_notice_on_deactivation('akismet/akismet.php');
});
