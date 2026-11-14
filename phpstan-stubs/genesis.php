<?php

/**
 * Stubs for the Genesis Framework functions the theme calls.
 *
 * Genesis is the parent theme and is not a Composer package, so PHPStan cannot find its
 * functions. These declarations exist only for analysis: PHPStan reads them through
 * `scanFiles` in phpstan.neon.dist, and nothing loads this file at runtime. Add a stub
 * here when PHPStan reports a Genesis function as not found; keep each signature to what
 * Genesis documents.
 */

declare(strict_types=1);

/**
 * Genesis loads `config/<name>.php` and returns the array it returns. The `appearance` file is this
 * theme's own, so its shape is stated here; every other name stays loosely typed.
 *
 * @return ($config is 'appearance' ? array{
 *     fonts-url: string,
 *     content-width: int,
 *     text-color: string,
 *     link-color: string,
 *     button-color: string,
 *     default-colors: array{text: string, link: string},
 *     editor-color-palette: list<array{name: string, slug: string, color: string}>,
 *     editor-font-sizes: list<array{name: string, size: float|int, slug: string}>,
 * } : ($config is 'post-type-supports' ? array<string, list<string>> : array<mixed>))
 */
function genesis_get_config(string $config): array
{
    return [];
}

function genesis_get_theme_handle(): string
{
    return '';
}

function genesis_get_theme_version(): string
{
    return '';
}

function genesis_is_amp(): bool
{
    return false;
}

function genesis_site_layout(bool $use_cache = true): string
{
    return '';
}

function genesis_unregister_layout(string $id = ''): bool
{
    return true;
}

function genesis(): void {}

function genesis_do_nav(): void {}

function genesis_do_subnav(): void {}

function genesis_post_info(): void {}

function genesis_post_meta(): void {}

function genesis_entry_footer_markup_open(): void {}

function genesis_entry_footer_markup_close(): void {}

function __genesis_return_full_width_content(): string
{
    return '';
}
