<?php

/**
 * Covers `lib/customize.php`: the Customizer settings and controls, and the
 * logo-width validator.
 *
 * The file registers its hook as it loads, and the first test below is the one
 * that loads it, so it must stay first: any test run before it loads the file
 * without the expectation.
 */

declare(strict_types=1);

use Brain\Monkey\Functions;
use Mockery\Matcher\Closure;

use function Brain\Monkey\Actions\expectAdded;
use function Brain\Monkey\Functions\when;

require_once __DIR__.'/../Stubs/WP_Customize_Color_Control.php';

/**
 * Match an argument that is identical (`===`) to the expected value.
 */
function brief_identical(mixed $expected): Closure
{
    return Mockery::on(static fn (mixed $actual): bool => $actual === $expected);
}

/**
 * Match a color control built for the given manager, id and arguments.
 *
 * @param  array<string, string>  $args
 */
function brief_color_control(object $manager, string $id, array $args): Closure
{
    return Mockery::on(
        static fn (mixed $control): bool => $control instanceof WP_Customize_Color_Control
            && $control->manager === $manager
            && $control->id === $id
            && $control->args === $args
    );
}

test('loading the file hooks the registration to customize_register', function (): void {
    expectAdded('customize_register')->once()->with('brief_customizer_register');

    require_once __DIR__.'/../../lib/customize.php';
});

test('the Customizer registers the color settings, the logo width setting and their controls', function (): void {
    require_once __DIR__.'/../../lib/customize.php';

    when('__')->returnArg(1);
    Functions\expect('genesis_get_config')->once()->with('appearance')->andReturn([
        'default-colors' => ['text' => '#aaa111', 'link' => '#bbb222'],
    ]);

    // WordPress declares `WP_Customize_Manager` final, so PHPStan types a mock of it as `never`.
    // The double is a plain mock, handed to the function that expects the real class.
    $manager = Mockery::mock();

    $manager->shouldReceive('add_setting')->once()->with('brief_text_color', brief_identical([
        'default' => '#aaa111',
        'sanitize_callback' => 'sanitize_hex_color',
    ]));
    $manager->shouldReceive('add_control')->once()->with(brief_color_control($manager, 'brief_text_color', [
        'description' => 'Change the default color for body, heading, button background, and other text.',
        'label' => 'Text Color',
        'section' => 'colors',
        'settings' => 'brief_text_color',
    ]));

    $manager->shouldReceive('add_setting')->once()->with('brief_link_color', brief_identical([
        'default' => '#bbb222',
        'sanitize_callback' => 'sanitize_hex_color',
    ]));
    $manager->shouldReceive('add_control')->once()->with(brief_color_control($manager, 'brief_link_color', [
        'description' => 'Change the default link highlight color.',
        'label' => 'Link Color',
        'section' => 'colors',
        'settings' => 'brief_link_color',
    ]));

    $manager->shouldReceive('add_setting')->once()->with('brief_logo_width', brief_identical([
        'default' => 350,
        'sanitize_callback' => 'absint',
        'validate_callback' => 'brief_validate_logo_width',
    ]));
    $manager->shouldReceive('add_control')->once()->with('brief_logo_width', brief_identical([
        'label' => 'Logo Width',
        'description' => 'The maximum width of the logo in pixels.',
        'priority' => 9,
        'section' => 'title_tagline',
        'settings' => ['default' => 'brief_logo_width'],
        'type' => 'number',
        'input_attrs' => [
            'min' => 100,
        ],
    ]));

    // @phpstan-ignore argument.type (the double stands in for the final `WP_Customize_Manager`)
    brief_customizer_register($manager);
});

test('a valid logo width adds no error and returns the validity object', function (mixed $width): void {
    require_once __DIR__.'/../../lib/customize.php';

    $validity = Mockery::mock(WP_Error::class);
    $validity->shouldNotReceive('add');

    expect(brief_validate_logo_width($validity, $width))->toBe($validity);
})->with([
    'the minimum' => [100],
    'above the minimum' => [101],
    'a numeric string' => ['250'],
]);

test('a missing or non-numeric logo width is required', function (mixed $width): void {
    require_once __DIR__.'/../../lib/customize.php';

    when('__')->returnArg(1);

    $validity = Mockery::mock(WP_Error::class);
    $validity->shouldReceive('add')->once()->with('required', 'You must supply a valid number.');

    expect(brief_validate_logo_width($validity, $width))->toBe($validity);
})->with([
    'zero' => [0],
    'an empty string' => [''],
    'null' => [null],
    'text' => ['wide'],
]);

test('a logo width under 100 is too small', function (mixed $width): void {
    require_once __DIR__.'/../../lib/customize.php';

    when('__')->returnArg(1);

    $validity = Mockery::mock(WP_Error::class);
    $validity->shouldReceive('add')->once()->with('logo_too_small', 'The logo width cannot be less than 100.');

    expect(brief_validate_logo_width($validity, $width))->toBe($validity);
})->with([
    'one under the minimum' => [99],
    'a numeric string' => ['50'],
]);
