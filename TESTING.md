# Testing Guide

This document describes how to:
- create and run tests for your development work,
- ensure code meets coding standards, for best practices and security,

If you're new to creating and running tests, this guide will walk you through how to do this.

For those more experienced with creating and running tests, our tests are written in PHP using [PHPUnit](https://phpunit.de/).

## Prerequisites

If you haven't yet set up your local development environment, refer to the [Setup Guide](SETUP.md).

If you haven't yet created a branch and made any code changes, refer to the [Development Guide](DEVELOPMENT.md)

## Write (or modify) a test

If your work creates new functionality, write a test.

If your work fixes existing functionality, check if a test exists. Either update that test, or create a new test if one doesn't exist.

Tests are written in PHP using [PHPUnit](https://phpunit.de/), and the existing `tests/ConvertKitAPITest.php` is a good place to start as a guide.

## Run PHPStan

[PHPStan](https://phpstan.org) performs static analysis on the Plugin's PHP code.  This ensures:

- DocBlocks declarations are valid and uniform
- Typehinting variables and return types declared in DocBlocks are correctly cast
- Any unused functions are detected
- Unnecessary checks / code is highlighted for possible removal
- Conditions that do not evaluate can be fixed/removed as necessary

In the Plugin's directory, run the following command to run PHPStan:

```bash
vendor/bin/phpstan --memory-limit=1G
```

Any errors should be corrected by making applicable code changes.

False positives [can be excluded by configuring](https://phpstan.org/user-guide/ignoring-errors) the `phpstan.neon` file.

## Run PHPUnit

Once you have written your code and tests, run the tests to make sure there are no errors.

To run the tests, enter the following commands in a separate Terminal window:

```bash
vendor/bin/phpunit --verbose --stop-on-failure
```

If a test fails, you can inspect the output.

Any errors should be corrected by making applicable code or test changes.

## Run PHP CodeSniffer

[PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) checks that all code meets the [PSR-12 Coding Standards](https://www.php-fig.org/psr/psr-12/).

To run CodeSniffer on tests, enter the following command:

```bash
vendor/bin/phpcs
```

Any errors should be corrected by either:
- making applicable code changes
- (Experimental) running `vendor/bin/phpcbf` to automatically fix coding standards

Need to change the coding standard rules applied?  Either:
- ignore a rule in the affected code, by adding `phpcs:ignore {rule}`, where {rule} is the given rule that failed in the above output.
- edit the [phpcs.tests.xml](phpcs.xml) file.

**Rules can be ignored with caution**, but it's essential that rules relating to coding style and inline code commenting / docblocks remain.

## Run PHP CodeSniffer for Tests

[PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) checks that all test code meets the [PSR-12 Coding Standards](https://www.php-fig.org/psr/psr-12/).

To run CodeSniffer on tests, enter the following command:

```bash
vendor/bin/phpcs --standard=phpcs.tests.xml
```

`--standard=phpcs.tests.xml` tells PHP CodeSniffer to use the use the [phpcs.tests.xml](phpcs.tests.xml) configuration file

Any errors should be corrected by either:
- making applicable code changes
- (Experimental) running `vendor/bin/phpcbf --standard=phpcs.tests.xml` to automatically fix coding standards

Need to change the coding standard rules applied?  Either:
- ignore a rule in the affected code, by adding `phpcs:ignore {rule}`, where {rule} is the given rule that failed in the above output.
- edit the [phpcs.tests.xml](phpcs.tests.xml) file.

**Rules can be ignored with caution**, but it's essential that rules relating to coding style and inline code commenting / docblocks remain.

## Next Steps

Once your tests are written and successfully run locally, submit your branch via a new [Pull Request](https://github.com/ConvertKit/ConvertKitSDK-PHP/compare).

It's best to create a Pull Request in draft mode, as this will trigger all tests to run as a GitHub Action, allowing you to double check all tests pass.

If the PR tests fail, you can make code changes as necessary, pushing to the same branch.  This will trigger the tests to run again.

If the PR tests pass, you can publish the PR, assigning some reviewers.