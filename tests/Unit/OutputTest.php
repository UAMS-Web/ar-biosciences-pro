<?php

/**
 * Covers `lib/output.php`: the inline CSS `brief_css()` adds for the Customizer
 * colors and the logo size.
 *
 * The file registers its hook as it loads, and the first test below is the one
 * that loads it, so it must stay first: any test run before it loads the file
 * without the expectation. Logo sizes are chosen so the ratio and effective
 * height are exact in floating point.
 *
 * Mutation notes: `$css .= ...` on the first CSS statement (`ConcatEqualToEqual`)
 * is equivalent, since `$css` is the empty string there and `=` and `.=` give the
 * same result. It is annotated in `lib/output.php`.
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\when;

/**
 * Run `brief_css()` against the given theme mods and logo and return what it
 * passed to `wp_add_inline_style()`.
 *
 * @param  array<string, mixed>  $mods  Theme mods that are set; every other mod returns its default.
 * @param  array<int, int>|false  $logo  What the attachment lookup returns.
 * @return list<array{0: string, 1: string}>
 */
function brief_run_css(array $mods, array|false $logo, bool $hasLogo): array
{
    require_once __DIR__.'/../../lib/output.php';

    $calls = [];

    Functions\expect('genesis_get_config')->once()->with('appearance')->andReturn([
        'default-colors' => ['text' => '#333', 'link' => '#fff385'],
    ]);
    when('get_theme_mod')->alias(
        static fn (string $name, mixed $default = false): mixed => $mods[$name] ?? $default
    );
    when('absint')->alias(static fn (mixed $value): int => abs((int) $value));
    when('has_custom_logo')->justReturn($hasLogo);
    when('genesis_get_theme_handle')->justReturn('brief-handle');
    Functions\expect('wp_get_attachment_image_src')->once()->with(42, 'full')->andReturn($logo);
    when('wp_add_inline_style')->alias(
        static function (string $handle, string $css) use (&$calls): void {
            $calls[] = [$handle, $css];
        }
    );

    brief_css();

    return $calls;
}

test('loading the file hooks brief_css to wp_enqueue_scripts', function (): void {
    expectAdded('wp_enqueue_scripts')->once()->with('brief_css');

    require_once __DIR__.'/../../lib/output.php';
});

test('default colors and no logo add no inline style', function (): void {
    expect(brief_run_css(['custom_logo' => 42], false, false))->toBe([]);
});

test('a changed text color adds the text rules only', function (): void {
    $calls = brief_run_css(['custom_logo' => 42, 'brief_text_color' => '#111111'], false, false);

    expect($calls)->toHaveCount(1)
        ->and($calls[0][0])->toBe('brief-handle')
        ->and($calls[0][1])->toContain('color: #111111;')
        ->and($calls[0][1])->toContain('background-color: #111111;')
        ->and($calls[0][1])->toContain('.site-title a,')
        ->and($calls[0][1])->not->toContain('box-shadow');
});

test('a changed link color adds the link rules only', function (): void {
    $calls = brief_run_css(['custom_logo' => 42, 'brief_link_color' => '#222222'], false, false);

    expect($calls)->toHaveCount(1)
        ->and($calls[0][1])->toContain('box-shadow: inset 0 -7px 0 0 #222222;')
        ->and($calls[0][1])->toContain('box-shadow: inset 0 -6px 0 0 #222222;')
        ->and($calls[0][1])->not->toContain('background-color');
});

test('both changed colors add both sets of rules in one style', function (): void {
    $calls = brief_run_css(
        ['custom_logo' => 42, 'brief_text_color' => '#111111', 'brief_link_color' => '#222222'],
        false,
        false
    );

    expect($calls)->toHaveCount(1)
        ->and($calls[0][1])->toContain('background-color: #111111;')
        ->and($calls[0][1])->toContain('box-shadow: inset 0 -7px 0 0 #222222;');
});

test('a logo adds only the rules its size calls for', function (
    int $width,
    int $height,
    int $maxWidth,
    array $present,
    array $absent
): void {
    $calls = brief_run_css(['custom_logo' => 42, 'brief_logo_width' => $maxWidth], [0, $width, $height], true);

    expect($calls)->toHaveCount(1);

    foreach ($present as $fragment) {
        expect($calls[0][1])->toContain($fragment);
    }

    foreach ($absent as $fragment) {
        expect($calls[0][1])->not->toContain($fragment);
    }
})->with([
    'effective height exactly 200 pins the header' => [200, 200, 350, ['.site-header {'], ['max-width', 'padding-top', 'float: none']],
    'a wide logo is padded from the 60px header' => [700, 100, 350, ['padding-top: 10px;'], ['.site-header {', 'max-width', 'float: none']],
    'a tiny logo is padded close to the header height' => [2, 2, 350, ['padding-top: 34px;'], ['.site-header {']],
    'a custom width sets the title area width' => [800, 100, 400, ['max-width: 400px;', 'padding-top: 10px;'], ['float: none']],
    'a width of 599 does not center the logo' => [1198, 599, 599, ['max-width: 599px;', '.site-header {'], ['float: none']],
    'a width of 600 centers the logo' => [1200, 600, 600, ['max-width: 600px;', 'float: none', 'text-align: center;', '.site-header {'], ['padding-top']],
]);

test('a logo adds nothing when its effective height gives no padding or rules', function (
    int $width,
    int $height
): void {
    expect(brief_run_css(['custom_logo' => 42], [0, $width, $height], true))->toBe([]);
})->with([
    'effective height 199 is under the header threshold' => [199, 199],
    'effective height 1 gets no padding' => [1, 1],
    'effective height 60 needs no padding' => [60, 60],
    'a tall logo is not stretched by its ratio' => [100, 200],
    'a zero height does not divide by zero' => [100, 0],
]);

test('the logo rules are skipped when the site has no custom logo', function (): void {
    expect(brief_run_css(['custom_logo' => 42, 'brief_logo_width' => 600], [0, 1200, 600], false))->toBe([]);
});
