<?php

/**
 * Minimal stand-in for WordPress' `WP_Customize_Color_Control`, which the Unit
 * suite cannot load. It records its constructor arguments for the tests to read.
 */

declare(strict_types=1);

// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class WP_Customize_Color_Control
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __construct(public object $manager, public string $id, public array $args) {}
}
