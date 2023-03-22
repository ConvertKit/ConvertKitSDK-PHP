# Development Guide

This document describes the high level workflow used when working on the ConvertKit PHP SDK.

You're free to use your preferred IDE and Git client.

## Prerequisites

If you haven't yet set up your local development environment with the ConvertKit PHP SDK repository installed, refer to the [Setup Guide](SETUP.md).

## Create a Branch

In your Git client / command line, create a new branch:
- If this is for a new feature that does not have a GitHub Issue number, enter a short descriptive name for the branch, relative to what you're working on
- If this is for a feature/bug that has a GitHub Issue number, enter issue-XXX, replacing XXX with the GitHub issue number

Once done, make sure you've switched to your new branch, and begin making the necessary code additions/changes/deletions.

## Coding Standards

Code must follow [PSR-12 Coding standards](https://www.php-fig.org/psr/psr-12/), which is checked when running tests (more on this below).

## Composer Packages

We use Composer for package management.  A package can be added to one of two sections of the `composer.json` file: `require` or `require-dev`.

### "require"

Packages listed in the "require" directive are packages that the PHP SDK needs in order to function for end users.

These packages are included when the PHP SDK release is published. 

Typically, packages listed in this section would be libraries that the PHP SDK uses, such as:
- [Guzzle](https://docs.guzzlephp.org/en/stable/): PHP HTTP Client
- [Monolog](https://github.com/Seldaek/monolog): PSR-3 compatible logging client

### "require-dev"

Packages listed in the "require-dev" directive are packages that the PHP SDK **does not** need in order to function for end users.

Typically, packages listed in this section would be internal development tools for testing, such as:
- Coding Standards
- PHPStan
- PHPUnit

## Committing Work

Remember to commit your changes to your branch relatively frequently, with a meaningful, short summary that explains what the change(s) do.
This helps anyone looking at the commit history in the future to find what they might be looking for.

If it's a particularly large commit, be sure to include more information in the commit's description.

## Next Steps

Once you've finished your feature or issue, you must write/amend tests for it.  Refer to the [Testing Guide](TESTING.md) for a detailed walkthrough
on how to write a test.