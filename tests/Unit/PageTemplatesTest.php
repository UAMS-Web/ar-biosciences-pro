<?php

/**
 * Covers `page-templates/blocks.php`, `page-templates/landing.php` and
 * `XXXsingle.php`.
 *
 * Each file runs its hook changes and ends in `genesis()` as it loads, so a test
 * loads it with `require` under expectations. `landing.php` also declares two
 * functions, so it loads once: the first landing test is the one that loads it
 * and must stay first among them; the function tests that follow load it only when
 * it has not been loaded.
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Actions\expectAdded as expectActionAdded;
use function Brain\Monkey\Actions\expectRemoved as expectActionRemoved;
use function Brain\Monkey\Filters\expectAdded as expectFilterAdded;
use function Brain\Monkey\Functions\when;

test('the Blocks template strips the entry header, forces full width and removes breadcrumbs', function (): void {
    expectActionRemoved('genesis_entry_header')->once()->with('genesis_entry_header_markup_open', 5);
    expectActionRemoved('genesis_entry_header')->once()->with('genesis_entry_header_markup_close', 15);
    expectActionRemoved('genesis_entry_header')->once()->with('genesis_do_post_title');
    expectFilterAdded('genesis_site_layout')->once()->with('__genesis_return_full_width_content');
    expectActionRemoved('genesis_before_loop')->once()->with('genesis_do_breadcrumbs');
    Functions\expect('genesis')->once();

    require __DIR__.'/../../page-templates/blocks.php';
});

test('the Landing template strips the header, footer, navigation and skip links', function (): void {
    expectFilterAdded('body_class')->once()->with('brief_landing_body_class');
    expectActionAdded('wp_enqueue_scripts')->once()->with('brief_dequeue_skip_links');
    expectActionRemoved('genesis_before_header')->once()->with('genesis_skip_links', 5);
    expectActionRemoved('genesis_header')->once()->with('genesis_header_markup_open', 5);
    expectActionRemoved('genesis_header')->once()->with('genesis_do_header');
    expectActionRemoved('genesis_header')->once()->with('genesis_header_markup_close', 15);
    Functions\expect('remove_theme_support')->once()->with('genesis-menus');
    expectActionRemoved('genesis_footer')->once()->with('genesis_footer_markup_open', 5);
    expectActionRemoved('genesis_footer')->once()->with('genesis_do_footer');
    expectActionRemoved('genesis_footer')->once()->with('genesis_footer_markup_close', 15);
    Functions\expect('genesis')->once();

    require_once __DIR__.'/../../page-templates/landing.php';
});

/**
 * Load `landing.php` with every call it makes at load stubbed, unless it is loaded.
 */
function brief_load_landing(): void
{
    if (in_array(realpath(__DIR__.'/../../page-templates/landing.php'), get_included_files(), true)) {
        return;
    }

    when('remove_theme_support')->justReturn();
    when('genesis')->justReturn();

    require_once __DIR__.'/../../page-templates/landing.php';
}

test('the Landing template adds a body class', function (): void {
    brief_load_landing();

    expect(brief_landing_body_class(['page']))->toBe(['page', 'landing-page']);
});

test('the Landing template dequeues the skip links script', function (): void {
    brief_load_landing();
    Functions\expect('wp_dequeue_script')->once()->with('skip-links');

    brief_dequeue_skip_links();
});

test('the single post template adds the entry footer markup and post meta', function (): void {
    expectActionAdded('genesis_entry_footer')->once()->with('genesis_entry_footer_markup_open', 5);
    expectActionAdded('genesis_entry_footer')->once()->with('genesis_post_meta');
    expectActionAdded('genesis_entry_footer')->once()->with('genesis_entry_footer_markup_close', 15);
    Functions\expect('genesis')->once();

    require __DIR__.'/../../XXXsingle.php';
});
