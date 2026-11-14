<?php

/**
 * The Simple Social Icons defaults in lib/theme-defaults.php.
 *
 * The file registers its filter at load, which needs Brain Monkey's `add_filter()`, so it is
 * loaded here inside the test rather than in the bootstrap. A file can be required once per
 * process: this is the only test that loads it, so the whole behavior is checked in one test.
 *
 * @see lib/theme-defaults.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Filters\expectAdded;

it('registers the defaults filter and merges the theme config over the plugin defaults', function (): void {
    expect(function_exists('brief_social_default_styles'))->toBeFalse('lib/theme-defaults.php was loaded before this test');

    expectAdded('simple_social_default_styles')->once()->with('brief_social_default_styles');

    require __DIR__.'/../../lib/theme-defaults.php';

    Functions\expect('genesis_get_config')->once()->with('simple-social-icons-settings')->andReturn(['size' => '40', 'new_window' => 1]);
    Functions\expect('wp_parse_args')->once()->with(['size' => '40', 'new_window' => 1], ['size' => '24', 'alignment' => 'left'])->andReturn(['size' => '40', 'alignment' => 'left', 'new_window' => 1]);

    expect(brief_social_default_styles(['size' => '24', 'alignment' => 'left']))->toBe(['size' => '40', 'alignment' => 'left', 'new_window' => 1]);
});
