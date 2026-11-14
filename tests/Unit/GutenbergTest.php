<?php

/**
 * Covers `lib/gutenberg/init.php` and `lib/gutenberg/inline-styles.php`.
 *
 * Order matters in this file, for two reasons:
 *
 * - `init.php` registers hooks and theme support as it loads, and the first test
 *   is the one that loads it (and, through it, `inline-styles.php`). Any test run
 *   before it loads the files without the expectations.
 * - `brief_blocks_body_classes()` guards on `function_exists('has_blocks')` and
 *   `function_exists('parse_blocks')`. Brain Monkey cannot undefine a function once
 *   a test defines it, so the two tests that need them missing come before every
 *   test that defines them, and `parse_blocks` is defined only after the test that
 *   needs it missing.
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Actions\expectAdded as expectActionAdded;
use function Brain\Monkey\Filters\expectAdded as expectFilterAdded;
use function Brain\Monkey\Functions\when;

/**
 * The appearance config the files read.
 *
 * @return array<string, mixed>
 */
function brief_gutenberg_appearance(): array
{
    return [
        'fonts-url' => 'https://fonts.example/css',
        'content-width' => 1062,
        'text-color' => '#111111',
        'link-color' => '#222222',
        'editor-font-sizes' => [
            ['name' => 'Small', 'size' => 14, 'slug' => 'small'],
        ],
        'editor-color-palette' => [
            ['name' => 'Text', 'slug' => 'theme-primary', 'color' => '#111111'],
            ['name' => 'Link', 'slug' => 'theme-secondary', 'color' => '#222222'],
        ],
    ];
}

/**
 * Stub what `init.php` calls as it loads, then load it.
 */
function brief_load_gutenberg(): void
{
    when('add_theme_support')->justReturn();
    when('add_editor_style')->justReturn();
    when('genesis_get_config')->justReturn(brief_gutenberg_appearance());
    when('get_stylesheet_directory')->justReturn(dirname(__DIR__, 2));

    require_once __DIR__.'/../../lib/gutenberg/init.php';
}

test('loading init.php hooks the functions and declares the theme support', function (): void {
    expectActionAdded('wp_enqueue_scripts')->once()->with('brief_enqueue_gutenberg_frontend_styles');
    expectActionAdded('wp_enqueue_scripts')->once()->with('brief_custom_gutenberg_css');
    expectActionAdded('enqueue_block_editor_assets')->once()->with('brief_block_editor_styles');
    expectActionAdded('enqueue_block_editor_assets')->once()->with('brief_custom_gutenberg_admin_css');
    expectFilterAdded('body_class')->once()->with('brief_blocks_body_classes');
    expectActionAdded('after_setup_theme')->once()->with('brief_content_width', 0);

    $appearance = brief_gutenberg_appearance();

    Functions\expect('add_theme_support')->once()->with('editor-styles');
    Functions\expect('add_theme_support')->once()->with('align-wide');
    Functions\expect('add_theme_support')->once()->with('responsive-embeds');
    Functions\expect('add_theme_support')->once()->with('editor-font-sizes', $appearance['editor-font-sizes']);
    Functions\expect('add_theme_support')->once()->with('editor-color-palette', $appearance['editor-color-palette']);
    Functions\expect('add_editor_style')->once()->with('/lib/gutenberg/style-editor.css');
    Functions\expect('genesis_get_config')->once()->with('appearance')->andReturn($appearance);
    Functions\expect('get_stylesheet_directory')->once()->andReturn(dirname(__DIR__, 2));

    require_once __DIR__.'/../../lib/gutenberg/init.php';
});

test('body classes are untouched when neither block function exists', function (): void {
    brief_load_gutenberg();
    when('is_singular')->justReturn(true);

    expect(brief_blocks_body_classes(['home']))->toBe(['home']);
});

test('body classes are untouched when parse_blocks does not exist', function (): void {
    brief_load_gutenberg();
    when('is_singular')->justReturn(true);
    when('has_blocks')->justReturn(false);

    expect(brief_blocks_body_classes(['home']))->toBe(['home']);
});

test('body classes are untouched away from a singular view', function (): void {
    brief_load_gutenberg();
    when('is_singular')->justReturn(false);
    when('has_blocks')->justReturn(false);
    when('parse_blocks')->justReturn([]);

    expect(brief_blocks_body_classes(['home']))->toBe(['home']);
});

test('a post with no blocks gets has-no-blocks', function (): void {
    brief_load_gutenberg();
    when('is_singular')->justReturn(true);
    when('has_blocks')->justReturn(false);
    when('parse_blocks')->justReturn([]);

    expect(brief_blocks_body_classes(['single']))->toBe(['single', 'has-no-blocks']);
});

test('the first block adds its name and alignment classes', function (
    mixed $blocks,
    array $expected
): void {
    brief_load_gutenberg();
    when('is_singular')->justReturn(true);
    when('has_blocks')->justReturn(true);
    Functions\expect('get_post')->once()->withNoArgs()->andReturn((object) ['post_content' => 'markup']);
    Functions\expect('parse_blocks')->once()->with('markup')->andReturn($blocks);

    expect(brief_blocks_body_classes(['single']))->toBe($expected);
})->with([
    'a named block with an alignment' => [
        [['blockName' => 'core/cover', 'attrs' => ['align' => 'full']]],
        ['single', 'first-block-core-cover', 'first-block-align-full'],
    ],
    'a named block without an alignment' => [
        [['blockName' => 'core/paragraph', 'attrs' => []]],
        ['single', 'first-block-core-paragraph'],
    ],
    'a freeform first block with no name' => [
        [['blockName' => null, 'attrs' => []]],
        ['single'],
    ],
    'an alignment on an unnamed block' => [
        [['blockName' => null, 'attrs' => ['align' => 'wide']]],
        ['single', 'first-block-align-wide'],
    ],
    'no parsed blocks' => [
        [],
        ['single'],
    ],
    'a return that is not an array' => [
        new stdClass,
        ['single'],
    ],
    'only the first block counts' => [
        [
            ['blockName' => 'core/image', 'attrs' => []],
            ['blockName' => 'core/cover', 'attrs' => ['align' => 'full']],
        ],
        ['single', 'first-block-core-image'],
    ],
]);

test('front-end styles are enqueued after the theme stylesheet', function (): void {
    brief_load_gutenberg();
    when('genesis_get_theme_handle')->justReturn('brief');
    when('get_stylesheet_directory_uri')->justReturn('https://example.test/theme');
    when('genesis_get_theme_version')->justReturn('1.2.3');
    Functions\expect('wp_enqueue_style')->once()->with(
        'brief-gutenberg',
        'https://example.test/theme/lib/gutenberg/front-end.css',
        ['brief'],
        '1.2.3'
    );

    brief_enqueue_gutenberg_frontend_styles();
});

test('editor fonts are enqueued from the appearance config', function (): void {
    brief_load_gutenberg();
    when('genesis_get_theme_handle')->justReturn('brief');
    when('genesis_get_theme_version')->justReturn('1.2.3');
    Functions\expect('wp_enqueue_style')->once()->with(
        'brief-gutenberg-fonts',
        'https://fonts.example/css',
        [],
        '1.2.3'
    );

    brief_block_editor_styles();
});

test('the content width is the filtered config width', function (): void {
    brief_load_gutenberg();
    Functions\expect('apply_filters')->once()->with('brief_content_width', 1062)->andReturn(900);

    brief_content_width();

    expect($GLOBALS['content_width'])->toBe(900);

    unset($GLOBALS['content_width']);
});

test('the front-end CSS carries the colors, font sizes and palette', function (): void {
    brief_load_gutenberg();
    when('genesis_get_theme_handle')->justReturn('brief');
    when('get_theme_support')->justReturn([brief_gutenberg_appearance()['editor-font-sizes']]);

    $calls = [];
    when('wp_add_inline_style')->alias(
        static function (string $handle, string $css) use (&$calls): void {
            $calls[] = [$handle, $css];
        }
    );

    brief_custom_gutenberg_css();

    expect($calls)->toHaveCount(1)
        ->and($calls[0][0])->toBe('brief-gutenberg')
        ->and($calls[0][1])->toContain('background-color: #111111;')
        ->and($calls[0][1])->toContain('box-shadow: inset 0 -6px 0 0 #222222;')
        ->and($calls[0][1])->toContain('.site-container .has-small-font-size {')
        ->and($calls[0][1])->toContain('.site-container .has-theme-primary-color,')
        ->and($calls[0][1])->toContain('.site-container .has-theme-secondary-background-color,');
});

test('the editor CSS carries the colors', function (): void {
    brief_load_gutenberg();
    when('genesis_get_theme_handle')->justReturn('brief');
    $calls = [];
    when('wp_add_inline_style')->alias(
        static function (string $handle, string $css) use (&$calls): void {
            $calls[] = [$handle, $css];
        }
    );

    brief_custom_gutenberg_admin_css();

    expect($calls)->toHaveCount(1)
        ->and($calls[0][0])->toBe('brief-gutenberg-fonts')
        ->and($calls[0][1])->toContain("color: #111111 !important;\n}")
        ->and($calls[0][1])->toContain('box-shadow: inset 0 -7px 0 0 #222222;')
        ->and($calls[0][1])->toContain('border: 2px solid #111111;');
});

test('font size CSS has one rule per size', function (): void {
    brief_load_gutenberg();
    when('get_theme_support')->justReturn([[
        ['name' => 'Small', 'size' => 14, 'slug' => 'small'],
        ['name' => 'Big', 'size' => 30.5, 'slug' => 'big'],
    ]]);

    $css = brief_inline_font_sizes();

    expect($css)->toContain('.site-container .has-small-font-size {')
        ->and($css)->toContain('font-size: 14px;')
        ->and($css)->toContain('.site-container .has-big-font-size {')
        ->and($css)->toContain('font-size: 30.5px;');
});

test('font size CSS is empty without theme support', function (mixed $support): void {
    brief_load_gutenberg();
    when('get_theme_support')->justReturn($support);

    expect(brief_inline_font_sizes())->toBe('');
})->with([
    'false' => [false],
    'an empty list' => [[]],
    'support declared with no sizes' => [[[]]],
]);

test('palette CSS has a color and background rule per color', function (): void {
    brief_load_gutenberg();
    when('genesis_get_config')->justReturn(brief_gutenberg_appearance());

    $css = brief_inline_color_palette();

    expect($css)->toContain('.site-container .has-theme-primary-color,')
        ->and($css)->toContain('.has-theme-primary-color {')
        ->and($css)->toContain('.has-theme-primary-background-color,')
        ->and($css)->toContain('.has-theme-secondary-color,')
        ->and($css)->toContain('.has-theme-secondary-background-color {')
        ->and($css)->toContain('color: #111111;')
        ->and($css)->toContain('background-color: #222222;');
});

test('palette CSS is empty for an empty palette', function (): void {
    brief_load_gutenberg();
    when('genesis_get_config')->justReturn(['editor-color-palette' => []] + brief_gutenberg_appearance());

    expect(brief_inline_color_palette())->toBe('');
});
