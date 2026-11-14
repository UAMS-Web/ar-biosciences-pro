<?php

/**
 * Covers `functions.php`.
 *
 * The file loads other theme files, calls Genesis and WordPress at file scope, and
 * registers its hooks as it loads, so the first test is the one that loads it and
 * must stay first: any test run before it loads the file without the expectations.
 *
 * The files `functions.php` requires are replaced by marker files in a temporary
 * theme directory (each records its own relative path in
 * `$GLOBALS['brief_fake_includes']`), so loading it neither runs those files nor
 * redeclares their functions. They have their own tests.
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Actions\expectAdded as expectActionAdded;
use function Brain\Monkey\Actions\expectRemoved as expectActionRemoved;
use function Brain\Monkey\Filters\expectAdded as expectFilterAdded;
use function Brain\Monkey\Functions\when;
use function Patchwork\relay;

/**
 * Create, once per run, a theme directory holding a marker file for every file
 * `functions.php` requires, and return its path.
 */
function brief_fake_theme(): string
{
    static $root = null;

    if ($root !== null) {
        return $root;
    }

    $root = sys_get_temp_dir().'/brief-functions-test-'.getmypid();
    $files = [
        'parent/lib/init.php',
        'child/lib/theme-defaults.php',
        'child/lib/helper-functions.php',
        'child/lib/customize.php',
        'child/lib/output.php',
        'child/lib/woocommerce/woocommerce-setup.php',
        'child/lib/woocommerce/woocommerce-output.php',
        'child/lib/woocommerce/woocommerce-notice.php',
        'child/lib/gutenberg/init.php',
    ];

    foreach ($files as $file) {
        if (! is_dir(dirname($root.'/'.$file))) {
            mkdir(dirname($root.'/'.$file), 0777, true);
        }

        file_put_contents($root.'/'.$file, "<?php\n\$GLOBALS['brief_fake_includes'][] = '".$file."';\n");
    }

    register_shutdown_function(static function () use ($root, $files): void {
        foreach ($files as $file) {
            unlink($root.'/'.$file);
        }

        foreach (['child/lib/woocommerce', 'child/lib/gutenberg', 'child/lib', 'child', 'parent/lib', 'parent'] as $dir) {
            rmdir($root.'/'.$dir);
        }

        rmdir($root);
    });

    return $root;
}

/**
 * Answer `function_exists()` for the Genesis function `functions.php` checks and
 * leave every other lookup (Brain Monkey's own) to the real function.
 */
function brief_genesis_responsive_menus_exists(bool $exists): void
{
    when('function_exists')->alias(
        static fn (string $name): bool => $name === 'genesis_register_responsive_menus'
            ? $exists
            : relay(func_get_args())
    );
}

/**
 * Stub everything `functions.php` calls as it loads, then load it.
 */
function brief_load_functions(): void
{
    $fake = brief_fake_theme();

    when('get_template_directory')->justReturn($fake.'/parent');
    when('get_stylesheet_directory')->justReturn($fake.'/child');

    if (in_array(realpath(__DIR__.'/../../functions.php'), get_included_files(), true)) {
        return;
    }

    when('genesis_get_config')->justReturn([]);
    when('genesis_register_responsive_menus')->justReturn();
    when('add_image_size')->justReturn();
    when('unregister_sidebar')->justReturn();
    when('genesis_unregister_layout')->justReturn();
    when('add_shortcode')->justReturn();
    brief_genesis_responsive_menus_exists(true);

    require_once __DIR__.'/../../functions.php';
}

test('loading functions.php loads the theme files, hooks the functions and adjusts Genesis', function (): void {
    $fake = brief_fake_theme();
    $GLOBALS['brief_fake_includes'] = [];
    $menus = ['script' => ['mainMenu' => ''], 'extras' => []];
    $shortcode = null;

    when('get_template_directory')->justReturn($fake.'/parent');
    when('get_stylesheet_directory')->justReturn($fake.'/child');
    Functions\expect('genesis_get_config')->once()->with('responsive-menus')->andReturn($menus);
    Functions\expect('genesis_register_responsive_menus')->once()->with($menus);
    brief_genesis_responsive_menus_exists(true);

    expectActionAdded('after_setup_theme')->once()->with('brief_localization_setup');
    expectActionAdded('after_setup_theme')->once()->with('genesis_child_gutenberg_support');
    expectActionAdded('after_setup_theme')->once()->with('brief_theme_support', 9);
    expectActionAdded('after_setup_theme')->once()->with('brief_post_type_support', 9);
    expectActionAdded('wp_enqueue_scripts')->once()->with('brief_enqueue_scripts_styles');
    expectActionAdded('genesis_header')->once()->with('genesis_do_nav', 12);
    expectActionAdded('genesis_footer')->once()->with('genesis_do_subnav', 5);
    expectActionAdded('genesis_entry_header')->once()->with('genesis_post_info', 9);
    expectActionAdded('genesis_entry_header')->once()->with('genesis_post_meta', 14);
    expectActionRemoved('genesis_after_header')->once()->with('genesis_do_nav');
    expectActionRemoved('genesis_after_header')->once()->with('genesis_do_subnav');
    expectActionRemoved('genesis_entry_header')->once()->with('genesis_post_info', 12);
    expectActionRemoved('genesis_entry_footer')->once()->with('genesis_post_meta');
    expectFilterAdded('wp_nav_menu_args')->once()->with('brief_secondary_menu_args');
    expectFilterAdded('genesis_edit_post_link')->once()->with('__return_false');
    expectFilterAdded('get_the_content_more_link')->once()->with('brief_read_more_link');
    expectFilterAdded('genesis_author_box_gravatar_size')->once()->with('brief_author_box_gravatar');
    expectFilterAdded('genesis_comment_list_args')->once()->with('brief_comments_gravatar');

    Functions\expect('add_image_size')->once()->with('sidebar-featured', 75, 75, true);
    Functions\expect('add_image_size')->once()->with('genesis-singular-images', 702, 526, true);
    Functions\expect('unregister_sidebar')->once()->with('header-right');
    Functions\expect('unregister_sidebar')->once()->with('sidebar-alt');
    Functions\expect('genesis_unregister_layout')->once()->with('content-sidebar-sidebar');
    Functions\expect('genesis_unregister_layout')->once()->with('sidebar-content-sidebar');
    Functions\expect('genesis_unregister_layout')->once()->with('sidebar-sidebar-content');
    Functions\expect('add_shortcode')->once()->with('abi_impact_report', Mockery::on(
        static function (mixed $callback) use (&$shortcode): bool {
            $shortcode = $callback;

            return is_callable($callback);
        }
    ));

    require_once __DIR__.'/../../functions.php';

    $embed = is_callable($shortcode) ? $shortcode() : null;

    expect($GLOBALS['brief_fake_includes'])->toBe([
        'parent/lib/init.php',
        'child/lib/theme-defaults.php',
        'child/lib/helper-functions.php',
        'child/lib/customize.php',
        'child/lib/output.php',
        'child/lib/woocommerce/woocommerce-setup.php',
        'child/lib/woocommerce/woocommerce-output.php',
        'child/lib/woocommerce/woocommerce-notice.php',
    ])->and($embed)->toBe(
        '<iframe src="https://www.blackberrybriar.com/impact-reports/abi/"'
        .' title="Arkansas Biosciences Institute Impact Report"'
        .' loading="lazy" style="width:100%;height:100vh;border:0"></iframe>'
    );
});

test('localization loads the child theme text domain from the languages folder', function (): void {
    brief_load_functions();
    when('genesis_get_theme_handle')->justReturn('brief');
    Functions\expect('load_child_theme_textdomain')->once()->with('brief', brief_fake_theme().'/child/languages');

    brief_localization_setup();
});

test('Gutenberg support loads the Gutenberg init file', function (): void {
    brief_load_functions();
    $GLOBALS['brief_fake_includes'] = [];

    genesis_child_gutenberg_support();

    expect($GLOBALS['brief_fake_includes'])->toBe(['child/lib/gutenberg/init.php']);
});

test('the fonts and dashicons are enqueued, and the AMP stylesheet only on AMP', function (bool $amp): void {
    brief_load_functions();
    when('genesis_get_theme_handle')->justReturn('brief');
    when('genesis_get_theme_version')->justReturn('1.2.3');
    when('get_stylesheet_directory_uri')->justReturn('https://example.test/theme');
    Functions\expect('genesis_get_config')->once()->with('appearance')->andReturn(['fonts-url' => 'https://fonts.example/css']);
    Functions\expect('genesis_is_amp')->once()->andReturn($amp);
    Functions\expect('wp_enqueue_style')->once()->with('brief-fonts', 'https://fonts.example/css', [], '1.2.3');
    Functions\expect('wp_enqueue_style')->once()->with('dashicons');

    if ($amp) {
        Functions\expect('wp_enqueue_style')->once()->with(
            'brief-amp',
            'https://example.test/theme/lib/amp/amp.css',
            ['brief'],
            '1.2.3'
        );
    }

    brief_enqueue_scripts_styles();
})->with([
    'a regular page' => [false],
    'an AMP page' => [true],
]);

test('every configured theme support is added', function (): void {
    brief_load_functions();
    Functions\expect('genesis_get_config')->once()->with('theme-supports')->andReturn([
        'html5' => ['caption', 'gallery'],
        'genesis-footer-widgets' => 1,
    ]);
    Functions\expect('add_theme_support')->once()->with('html5', ['caption', 'gallery']);
    Functions\expect('add_theme_support')->once()->with('genesis-footer-widgets', 1);

    brief_theme_support();
});

test('no theme support is added when none is configured', function (): void {
    brief_load_functions();
    Functions\expect('genesis_get_config')->once()->with('theme-supports')->andReturn([]);
    Functions\expect('add_theme_support')->never();

    brief_theme_support();
});

test('every configured post type support is added', function (): void {
    brief_load_functions();
    Functions\expect('genesis_get_config')->once()->with('post-type-supports')->andReturn([
        'post' => ['genesis-singular-images'],
        'page' => ['genesis-singular-images', 'excerpt'],
    ]);
    Functions\expect('add_post_type_support')->once()->with('post', ['genesis-singular-images']);
    Functions\expect('add_post_type_support')->once()->with('page', ['genesis-singular-images', 'excerpt']);

    brief_post_type_support();
});

test('no post type support is added when none is configured', function (): void {
    brief_load_functions();
    Functions\expect('genesis_get_config')->once()->with('post-type-supports')->andReturn([]);
    Functions\expect('add_post_type_support')->never();

    brief_post_type_support();
});

test('the secondary menu is limited to one level', function (): void {
    brief_load_functions();

    expect(brief_secondary_menu_args(['theme_location' => 'secondary', 'depth' => 0, 'echo' => false]))->toBe([
        'theme_location' => 'secondary',
        'depth' => 1,
        'echo' => false,
    ]);
});

test('other menus keep their arguments', function (): void {
    brief_load_functions();

    expect(brief_secondary_menu_args(['theme_location' => 'primary', 'depth' => 0]))->toBe([
        'theme_location' => 'primary',
        'depth' => 0,
    ]);
});

test('the read more link points at the permalink', function (): void {
    brief_load_functions();
    when('get_permalink')->justReturn('https://example.test/post/');

    expect(brief_read_more_link())->toBe('... <a class="more-link" href="https://example.test/post/">Continue Reading</a>');
});

test('the author box Gravatar is 90 pixels', function (): void {
    brief_load_functions();

    expect(brief_author_box_gravatar())->toBe(90);
});

test('the comment Gravatar is 60 pixels and keeps the other arguments', function (): void {
    brief_load_functions();

    expect(brief_comments_gravatar(['avatar_size' => 48, 'type' => 'comment']))->toBe([
        'avatar_size' => 60,
        'type' => 'comment',
    ]);
});
