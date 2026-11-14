<?php

/**
 * Unit bootstrap.
 *
 * Loads the Composer autoloader and the theme files whose functions the Unit suite
 * tests. It does not load functions.php: that file requires Genesis from the parent
 * theme (lib/init.php) and calls WordPress at file scope, neither of which exists
 * without WordPress. Each theme file under test is required here once it can load
 * without them, or with its load-time calls stubbed inside one Brain Monkey session.
 */

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

require_once __DIR__.'/../vendor/antecedent/patchwork/Patchwork.php';

require_once __DIR__.'/../lib/helper-functions.php';
