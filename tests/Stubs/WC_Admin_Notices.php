<?php

/**
 * Minimal stand-in for WooCommerce's `WC_Admin_Notices`, which the Unit suite
 * cannot load. It records each removed notice for the tests to read.
 */

declare(strict_types=1);

// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class WC_Admin_Notices
{
    /**
     * @var list<string>
     */
    public static array $removed = [];

    public static function remove_notice(string $name): void
    {
        self::$removed[] = $name;
    }
}
