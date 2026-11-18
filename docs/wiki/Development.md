# Development

How to work on the theme. The theme ships without Composer: everything below is development tooling, and none of it reaches a site, since `.gitattributes` keeps the download to the files the theme runs.

## Setting up

1. Clone the repository into a WordPress install's `wp-content/themes/`, beside Genesis.
2. Run `composer install`. It installs the development tools only.

The test suite needs no WordPress and no database: it runs each file under Brain Monkey with WordPress's functions stubbed.

## Checks

| Command | What it runs |
| --- | --- |
| `composer test` | the Pest suite in `tests/Unit/` |
| `composer stan` | PHPStan, with the WordPress stubs locked to the 6.8 line |
| `composer test:pint` | Pint, reporting files that need formatting |
| `composer test:refactor` | Rector as a dry run |
| `composer mutate` | Pest mutation testing, each first-party file under the test file that covers it (`scripts/mutate.sh`) |

The same checks run in CI through the shared workflow in `UAMS-Web/uams-ci-workflows`, with PHP 8.2 syntax checking and a check of what the theme's download contains.

## Making a change

Every change starts as an issue and reaches `main` through a pull request; [`CONTRIBUTING.md`](https://github.com/UAMS-Web/ar-biosciences-pro/blob/main/CONTRIBUTING.md) has the steps and the test plan each pull request carries. Before changing code that has no test, add one that captures what it does now.

## Editing this wiki

These pages live in `docs/wiki/` and are published by the *Publish wiki* workflow when a release is published, or when the workflow is run by hand. Each publish replaces the whole wiki with the folder, so an edit made in the browser is lost. Link between pages with relative links that keep the `.md` extension (`[Hooks](Hooks.md)`), and link anything else in the repository with a full URL.

<!-- Product, identifier and organization names, kept as spelled: cspell:ignore UAMS -->
