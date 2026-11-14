<?php

/**
 * Minimal stand-ins for WordPress' `WP_Customize_Color_Control` and the
 * `WP_Customize_Control` it extends, which the Unit suite cannot load. The
 * control records its constructor arguments for the tests to read.
 */

declare(strict_types=1);

if (! class_exists('WP_Customize_Control', false)) {
    // phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
    class WP_Customize_Control
    {
        /**
         * @param  array<string, mixed>  $args
         */
        public function __construct(public object $manager, public string $id, public array $args) {}
    }
}

// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class WP_Customize_Color_Control extends WP_Customize_Control {}
