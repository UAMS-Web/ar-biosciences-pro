#!/usr/bin/env bash
# The `composer mutate` script the shared CI chain runs.
#
# Each first-party file is mutated under the one test file that covers it, in the named-test-file
# form: a bare `pest --mutate` reads the whole test directory, which Patchwork's stream wrapper
# cannot do. A run's own log is then read by check-mutation-run.php, because Pest's status alone
# reports a run that created no mutants as a pass. The script fails when any file's run fails.
#
# Arguments (the chain's own):
#   --min=N                                   passed to Pest (the chain passes 100)
#   --ignore-min-score-on-zero-mutations      a file that holds no code a mutator changes passes
#   --path=a,b                                mutate only these files; without it, every file below
#
# A first-party PHP file with no row in `test_for` fails the run: add the file and its test here.
set -uo pipefail

cd "$(dirname "$0")/.."

all_files=(
  config/accessibility.php config/appearance.php config/child-theme-settings-genesis.php
  config/child-theme-settings.php config/custom-logo.php config/html5.php config/menus.php
  config/post-type-supports.php config/responsive-menus.php config/simple-social-icons-settings.php
  config/theme-supports.php
  lib/helper-functions.php lib/theme-defaults.php lib/output.php lib/customize.php
  lib/gutenberg/init.php lib/gutenberg/inline-styles.php
  lib/woocommerce/woocommerce-setup.php lib/woocommerce/woocommerce-output.php lib/woocommerce/woocommerce-notice.php
  functions.php page-templates/blocks.php page-templates/landing.php XXXsingle.php
)

# The test file that covers a source file.
test_for() {
  case "$1" in
    config/*) echo ConfigTest ;;
    lib/helper-functions.php) echo HelperFunctionsTest ;;
    lib/theme-defaults.php) echo ThemeDefaultsTest ;;
    lib/output.php) echo OutputTest ;;
    lib/customize.php) echo CustomizeTest ;;
    lib/gutenberg/*) echo GutenbergTest ;;
    lib/woocommerce/*) echo WooCommerceTest ;;
    functions.php) echo FunctionsTest ;;
    page-templates/* | XXXsingle.php) echo PageTemplatesTest ;;
    *) return 1 ;;
  esac
}

pest_args=()
ignore_zero=0
files=("${all_files[@]}")
for arg in "$@"; do
  case "$arg" in
    --path=*) IFS=',' read -r -a files <<< "${arg#--path=}" ;;
    --ignore-min-score-on-zero-mutations) ignore_zero=1 ;;
    *) pest_args+=("$arg") ;;
  esac
done

echo "Platform: $(uname -sr) | PHP $(php -r 'echo PHP_VERSION;') | PCOV $(php -r 'echo phpversion("pcov") ?: "none";')"

logdir=$(mktemp -d)
failed=0
for file in "${files[@]}"; do
  if ! test_name=$(test_for "$file"); then
    echo "FAIL $file has no test file in scripts/mutate.sh: add a row to test_for and to all_files"
    failed=$((failed + 1))
    continue
  fi
  log="$logdir/$(echo "$file" | tr '/' '_').log"
  echo "=== $file (tests/Unit/$test_name.php)"
  php -d pcov.directory="$PWD" vendor/bin/pest "tests/Unit/$test_name.php" --mutate --path="$file" ${pest_args[@]+"${pest_args[@]}"} > "$log" 2>&1
  pest_status=$?
  cat "$log"
  if [ "$ignore_zero" -eq 1 ] && sed "s/$(printf '\033')\[[0-9;]*m//g" "$log" | grep -Eq '(^|[^0-9])0 Mutations for 0 Files created'; then
    echo "OK $file holds no code a mutator changes"
    continue
  fi
  php .claude/skills/pest-testing/scripts/check-mutation-run.php "$log" > "$log.check" 2>&1
  check_status=$?
  cat "$log.check"
  if [ "$pest_status" -ne 0 ] || [ "$check_status" -ne 0 ]; then
    echo "FAIL $file (pest=$pest_status check=$check_status)"
    failed=$((failed + 1))
  fi
done

echo "FILES ${#files[@]} FAILED $failed"
[ "$failed" -eq 0 ]
