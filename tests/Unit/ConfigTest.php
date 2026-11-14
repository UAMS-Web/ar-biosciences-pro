<?php

/**
 * Pins the values every file under `config/` returns.
 *
 * Genesis reads these arrays at load time, so a changed value changes the site
 * with no error. Each test loads one file with `require` (never `require_once`,
 * which would return `true` on a second load) and compares the whole array with
 * `toBe()`, so a removed, added, reordered or altered entry fails.
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Functions\when;

/**
 * Load a config file and return the array it returns.
 *
 * @return array<mixed>
 */
function brief_load_config(string $name): array
{
    $config = require __DIR__.'/../../config/'.$name.'.php';

    if (! is_array($config)) {
        throw new UnexpectedValueException("config/{$name}.php does not return an array.");
    }

    return $config;
}

test('accessibility lists the Genesis accessibility features', function (): void {
    expect(brief_load_config('accessibility'))->toBe([
        '404-page',
        'drop-down-menu',
        'headings',
        'search-form',
        'skip-links',
    ]);
});

test('custom logo allows flexible height and width', function (): void {
    expect(brief_load_config('custom-logo'))->toBe([
        'flex-height' => true,
        'flex-width' => true,
    ]);
});

test('html5 lists the elements output as HTML5', function (): void {
    expect(brief_load_config('html5'))->toBe([
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
    ]);
});

test('menus names the primary and secondary menus', function (): void {
    Functions\expect('__')->twice()->andReturnUsing(
        static fn (string $text, string $domain): string => $text.'|'.$domain
    );

    expect(brief_load_config('menus'))->toBe([
        'primary' => 'Header Menu|brief',
        'secondary' => 'Footer Menu|brief',
    ]);
});

test('post type supports adds singular images to posts and pages', function (): void {
    expect(brief_load_config('post-type-supports'))->toBe([
        'post' => ['genesis-singular-images'],
        'page' => ['genesis-singular-images'],
    ]);
});

test('responsive menus sets the script and breakpoint', function (): void {
    Functions\expect('__')->once()->with('', 'brief')->andReturn('');

    expect(brief_load_config('responsive-menus'))->toBe([
        'script' => [
            'mainMenu' => '',
            'menuClasses' => [
                'others' => ['.nav-primary'],
            ],
        ],
        'extras' => [
            'media_query_width' => '960px',
        ],
    ]);
});

test('simple social icons settings are the default styles', function (): void {
    expect(brief_load_config('simple-social-icons-settings'))->toBe([
        'alignment' => 'aligncenter',
        'background_color' => '#ffffff',
        'background_color_hover' => '#ffffff',
        'border_radius' => 0,
        'border_width' => 0,
        'icon_color' => '#333333',
        'icon_color_hover' => '#666666',
        'size' => 40,
    ]);
});

test('Genesis settings used on the settings page are the defaults', function (): void {
    expect(brief_load_config('child-theme-settings-genesis'))->toBe([
        'blog_cat_num' => 6,
        'breadcrumb_home' => 0,
        'breadcrumb_front_page' => 0,
        'breadcrumb_posts_page' => 0,
        'breadcrumb_single' => 0,
        'breadcrumb_page' => 0,
        'breadcrumb_archive' => 0,
        'breadcrumb_404' => 0,
        'breadcrumb_attachment' => 0,
        'content_archive' => 'excerpts',
        'content_archive_limit' => 0,
        'content_archive_thumbnail' => 0,
        'image_size' => 'large',
        'image_alignment' => 'none',
        'posts_nav' => 'numeric',
        'site_layout' => 'full-width-content',
    ]);
});

test('child theme settings key the Genesis settings by the settings field', function (): void {
    if (! defined('GENESIS_SETTINGS_FIELD')) {
        define('GENESIS_SETTINGS_FIELD', 'genesis-settings');
    }

    expect(brief_load_config('child-theme-settings'))->toBe([
        'genesis-settings' => [
            'blog_cat_num' => 4,
            'breadcrumb_home' => 0,
            'breadcrumb_front_page' => 0,
            'breadcrumb_posts_page' => 0,
            'breadcrumb_single' => 0,
            'breadcrumb_page' => 0,
            'breadcrumb_archive' => 0,
            'breadcrumb_404' => 0,
            'breadcrumb_attachment' => 0,
            'content_archive' => 'full',
            'content_archive_limit' => 220,
            'content_archive_thumbnail' => 0,
            'entry_meta_after_content' => '[post_categories before=""] [post_tags before=""]',
            'entry_meta_before_content' => '[post_date]',
            'image_size' => 'medium',
            'image_alignment' => 'alignleft',
            'posts_nav' => 'numeric',
            'site_layout' => 'full-width-content',
        ],
        'posts_per_page' => 4,
    ]);
});

test('theme supports registers the logo, markup, accessibility and menus', function (): void {
    when('__')->returnArg(1);

    expect(brief_load_config('theme-supports'))->toBe([
        'genesis-custom-logo' => [
            'height' => 120,
            'width' => 700,
            'flex-height' => true,
            'flex-width' => true,
        ],
        'html5' => [
            'caption',
            'comment-form',
            'comment-list',
            'gallery',
            'search-form',
        ],
        'genesis-accessibility' => [
            'drop-down-menu',
            'headings',
            'search-form',
            'skip-links',
        ],
        'genesis-after-entry-widget-area' => '',
        'genesis-footer-widgets' => 1,
        'genesis-menus' => [
            'primary' => 'Header Menu',
            'secondary' => 'Footer Menu',
        ],
    ]);
});

test('appearance reads both colors from theme mods and defaults them', function (): void {
    when('__')->returnArg(1);

    Functions\expect('get_theme_mod')->once()->with('brief_text_color', '#333')->andReturn('#111111');
    Functions\expect('get_theme_mod')->once()->with('brief_link_color', '#fff385')->andReturn('#222222');

    expect(brief_load_config('appearance'))->toBe([
        'fonts-url' => 'https://fonts.googleapis.com/css?family=Fira+Sans:300,300i400,400i,700,700i',
        'content-width' => 1062,
        'text-color' => '#111111',
        'link-color' => '#222222',
        'button-color' => '#111111',
        'default-colors' => [
            'text' => '#333',
            'link' => '#fff385',
        ],
        'editor-color-palette' => [
            [
                'name' => 'Text color',
                'slug' => 'theme-primary',
                'color' => '#111111',
            ],
            [
                'name' => 'Link color',
                'slug' => 'theme-secondary',
                'color' => '#222222',
            ],
        ],
        'editor-font-sizes' => [
            [
                'name' => 'Small',
                'size' => 14.4,
                'slug' => 'small',
            ],
            [
                'name' => 'Normal',
                'size' => 18,
                'slug' => 'normal',
            ],
            [
                'name' => 'Large',
                'size' => 20,
                'slug' => 'large',
            ],
            [
                'name' => 'Larger',
                'size' => 24,
                'slug' => 'larger',
            ],
        ],
    ]);
});
