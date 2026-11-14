<?php

declare(strict_types=1);
/**
 * Brief.
 *
 * This file adds the required helper functions used in the Brief Theme.
 *
 * @author  Minimal Genesis
 * @license GPL-2.0-or-later
 *
 * @link    https://minimalgenesis.com/
 */

/**
 * Calculates if white or gray would contrast more with the provided color.
 *
 * @since 2.2.3
 *
 * @param  string  $color  A color in hex format.
 * @return string The hex code for the most contrasting color: dark grey or white.
 */
function brief_color_contrast($color): string
{

    $hexcolor = str_replace('#', '', $color);
    $red = hexdec(substr($hexcolor, 0, 2));
    $green = hexdec(substr($hexcolor, 2, 2));
    $blue = hexdec(substr($hexcolor, 4, 2));

    $luminosity = (($red * 0.2126) + ($green * 0.7152) + ($blue * 0.0722));

    return ($luminosity > 128) ? '#333333' : '#ffffff';

}

/**
 * Generates a lighter or darker color from a starting color.
 * Used to generate complementary hover tints from user-chosen colors.
 *
 * @since 2.2.3
 *
 * @param  string  $color  A color in hex format.
 * @param  int  $change  The amount to reduce or increase brightness by.
 * @return string Hex code for the adjusted color brightness.
 */
function brief_color_brightness($color, $change): string
{

    $hexcolor = str_replace('#', '', $color);

    // `hexdec()` of two hex digits is at most 255 and so is already an int; the cast only gives PHPStan that type.
    // @pest-mutate-ignore: RemoveIntegerCast
    $red = (int) hexdec(substr($hexcolor, 0, 2));
    // @pest-mutate-ignore: RemoveIntegerCast
    $green = (int) hexdec(substr($hexcolor, 2, 2));
    // @pest-mutate-ignore: RemoveIntegerCast
    $blue = (int) hexdec(substr($hexcolor, 4, 2));

    $red = max(0, min(255, $red + $change));
    $green = max(0, min(255, $green + $change));
    $blue = max(0, min(255, $blue + $change));

    return '#'.dechex($red).dechex($green).dechex($blue);

}

/**
 * Reads a theme modification that holds a string, such as a color.
 *
 * @since 1.0.2
 *
 * @param  string  $name  The theme modification name.
 * @param  string  $default  The value to use when the modification is unset or is not a string.
 * @return string The modification, or the default.
 */
function brief_theme_mod_string(string $name, string $default): string
{

    $value = get_theme_mod($name, $default);

    return is_string($value) ? $value : $default;

}

/**
 * Reads a theme modification that holds a whole number, such as an attachment ID or a width.
 *
 * @since 1.0.2
 *
 * @param  string  $name  The theme modification name.
 * @param  int  $default  The value to use when the modification is unset or is not numeric.
 * @return int The modification as an integer, or the default.
 */
function brief_theme_mod_int(string $name, int $default): int
{

    $value = get_theme_mod($name, $default);

    return is_numeric($value) ? (int) $value : $default;

}
