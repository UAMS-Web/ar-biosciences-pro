<?php

/**
 * The color helpers in lib/helper-functions.php, which call no WordPress function.
 *
 * `brief_color_brightness()` pins current behavior (a characterization): each channel is
 * written with `dechex()` and no padding, so a channel below 0x10 becomes one digit and the
 * result is not a six-digit color. A fix belongs in its own change.
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
    'red alone is dark' => ['#ff0000', '#ffffff'],
    'green alone is light' => ['#00ff00', '#333333'],
    'blue alone is dark' => ['#0000ff', '#ffffff'],
    'mixed channels, light' => ['#abcdef', '#333333'],
    'mixed channels, dark' => ['#123456', '#ffffff'],
    'upper case hex' => ['#ABCDEF', '#333333'],
    'luminosity exactly 128 is dark' => ['#808080', '#ffffff'],
    'luminosity 129 is light' => ['#818181', '#333333'],
    'luminosity 127 is dark' => ['#7f7f7f', '#ffffff'],
    'an alpha pair after the color is ignored' => ['#80800fff', '#ffffff'],
]);

it('weights the red, green and blue channels separately', function (): void {
    // The channel coefficients 0.2126, 0.7152 and 0.0722 sum to 1.0, so one channel at
    // 255 never crosses 128 alone: red alone reads 54, green alone 182, blue alone 18.
    expect(brief_color_contrast('#ff0000'))->toBe('#ffffff')
        ->and(brief_color_contrast('#00ff00'))->toBe('#333333')
        ->and(brief_color_contrast('#0000ff'))->toBe('#ffffff')
        // Green carries the threshold on its own at 179 (128.02), red cannot at 255.
        ->and(brief_color_contrast('#00b300'))->toBe('#333333')
        ->and(brief_color_contrast('#ffff00'))->toBe('#333333')
        ->and(brief_color_contrast('#ff00ff'))->toBe('#ffffff');
});

it('lightens and darkens a color by the change', function (string $color, int $change, string $expected): void {
    expect(brief_color_brightness($color, $change))->toBe($expected);
})->with([
    'lighter' => ['#101010', 16, '#202020'],
    'darker' => ['#202020', -16, '#101010'],
    'clamped at white' => ['#fffff0', 32, '#ffffff'],
    'clamped at black' => ['#0a0a0a', -32, '#000'],
    'each channel moves on its own' => ['#123456', 10, '#1c3e60'],
    'each channel moves on its own, darker' => ['#abcdef', -5, '#a6c8ea'],
    'without the hash' => ['123456', 10, '#1c3e60'],
    'no change' => ['#aabbcc', 0, '#aabbcc'],
    'a channel below 0x10 loses its leading zero' => ['#0a0a0a', -5, '#555'],
    'an alpha pair after the color is ignored' => ['#aabbccdd', 0, '#aabbcc'],
    'a color within range is unchanged by a zero change' => ['#101010', 0, '#101010'],
    'red clamps alone' => ['#f01010', 32, '#ff3030'],
    'green clamps alone' => ['#10f010', 32, '#30ff30'],
    'blue clamps alone' => ['#1010f0', 32, '#3030ff'],
]);
