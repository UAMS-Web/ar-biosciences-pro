<?php

/**
 * The color helpers in lib/helper-functions.php, which call no WordPress function.
 *
 * @see lib/helper-functions.php
 */

declare(strict_types=1);

it('picks white text on a dark color and dark grey text on a light one', function (string $color, string $expected): void {
    expect(brief_color_contrast($color))->toBe($expected);
})->with([
    'black' => ['#000000', '#ffffff'],
    'white' => ['#ffffff', '#333333'],
    'without the hash' => ['ffffff', '#333333'],
    'dark blue' => ['#003366', '#ffffff'],
    'light yellow' => ['#ffff99', '#333333'],
]);

it('lightens and darkens a color by the change', function (string $color, int $change, string $expected): void {
    expect(brief_color_brightness($color, $change))->toBe($expected);
})->with([
    'lighter' => ['#101010', 16, '#202020'],
    'darker' => ['#202020', -16, '#101010'],
    'clamped at white' => ['#fffff0', 32, '#ffffff'],
    'clamped at black' => ['#0a0a0a', -32, '#000'],
]);
