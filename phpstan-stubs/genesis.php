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
 * @return array<mixed>
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
