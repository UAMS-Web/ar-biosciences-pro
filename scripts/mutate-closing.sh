#!/usr/bin/env bash
# Closing mutation run for #14: each first-party file under the test file that covers it,
# the named-test-file form (a bare `pest --mutate` reads the test directory, which
# Patchwork's stream wrapper cannot do). Each run's own log is checked by
# check-mutation-run.php, and the run fails if any run does.
#
# Scratch script for a dispatched run; it is never merged.
set -uo pipefail

cd "$(dirname "$0")/.."

echo "Platform: $(uname -sr) | PHP $(php -r 'echo PHP_VERSION;') | PCOV $(php -r 'echo phpversion("pcov");')"
vendor/bin/pest --version

runs=(
  "ConfigTest|config/accessibility.php config/appearance.php config/block-editor-settings.php config/child-theme-settings-genesis.php config/child-theme-settings.php config/custom-logo.php config/html5.php config/menus.php config/post-type-supports.php config/responsive-menus.php config/simple-social-icons-settings.php config/theme-supports.php"
  "HelperFunctionsTest|lib/helper-functions.php"
  "ThemeDefaultsTest|lib/theme-defaults.php"
  "OutputTest|lib/output.php"
  "CustomizeTest|lib/customize.php"
  "GutenbergTest|lib/gutenberg/init.php lib/gutenberg/inline-styles.php"
  "WooCommerceTest|lib/woocommerce/woocommerce-setup.php lib/woocommerce/woocommerce-output.php lib/woocommerce/woocommerce-notice.php"
  "FunctionsTest|functions.php"
  "PageTemplatesTest|page-templates/blocks.php page-templates/landing.php XXXsingle.php"
)

failed=0
files=0
logdir=$(mktemp -d)

for run in "${runs[@]}"; do
  test_name=${run%%|*}
  for file in ${run#*|}; do
    files=$((files + 1))
    log="$logdir/$(echo "$file" | tr '/' '_').log"
    echo "=== $file (tests/Unit/$test_name.php)"
    php -d pcov.directory="$PWD" vendor/bin/pest "tests/Unit/$test_name.php" --mutate --path="$file" > "$log" 2>&1
    pest_status=$?
    cat "$log"
    php .claude/skills/pest-testing/scripts/check-mutation-run.php "$log" > "$log.check" 2>&1
    check_status=$?
    cat "$log.check"
    echo "RESULT $file pest=$pest_status check=$check_status"
    if [ "$pest_status" -ne 0 ] || [ "$check_status" -ne 0 ]; then
      failed=$((failed + 1))
    fi
  done
done

echo "FILES $files FAILED $failed"
[ "$failed" -eq 0 ]
