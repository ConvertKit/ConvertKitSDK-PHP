# Deployment Guide

This document describes the workflow for deploying a PHP SDK update on GitHub.

## Merge Pull Requests

Merge the approved Pull Request(s) to the `main` branch.

An *approved* Pull Request is when a PR passes all tests **and** has been approved by **one or more** reviewers.

## Update the PHP SDK's Version Number

We follow [Semantic Versioning](https://semver.org/).

- In `src/ConvertKit_API.php`, change the `const VERSION` to the new version number.

## Commit Changes

Commit the updated files, which should comprise of:

- `src/ConvertKit_API.php`

## Create a New Release

[Create a New Release](https://github.com/ConvertKit/convertkitsdk-php/releases/new), completing the following:

- Choose a tag: Click this button and enter the new version number (e.g. `1.0`)
- Release title: The version number (e.g. `1.0`)
- Describe this release: Add a changelog detailing the applicable changes this version introduces, with a link to each PR, using the below template

```
# Deprecations / Notices

- PHP: Minimum supported version is now `7.4`
- `add_tag()` will trigger an `E_USER_NOTICE`, as the method name is misleading, and we prefer methods with named arguments; use `tag_subscribe()` (#44)
- `form_subscribe()` will trigger an `E_USER_NOTICE`, as we prefer methods with named arguments; use `add_subscriber_to_form()` (#54)
- `form_unsubscribe()` will trigger an `E_USER_NOTICE`, as the method name is misleading, and we prefer methods with named arguments; use `unsubscribe()` (#45)

# Features / Additions

- Added User-Agent on API requests (#34)
- Added `get()`, `post()`, `put()` and `delete()` methods (#36)
- Added `get_forms()` and `get_landing_pages()` methods (#41)
- Added `get_form_subscriptions()` method (#42)
- Added Tag methods `get_tags()`, `create_tag()`, `tag_subscriber()`, `remove_tag_from_subscriber()`, `remove_tag_from_subscriber_by_email()` (#44)
- Added Subscriber methods `update_subscriber()`, `unsubscribe()` (#45)
- Added `add_subscriber_to_sequence()` to support name, custom fields and tags (#43)
- Added Custom Field methods `get_custom_fields()`, `add_custom_field()`, `add_custom_fields()`, `update_custom_field()`, `delete_custom_field()` (#46)
- Added Purchase method `get_purchase()` (#47)
- Added Webhook methods `create_webhook()`, `destroy_webhook() (#48)
- Added Broadcast methods `create_broadcast()`, `get_broadcast()`, `get_broadcast_stats()`, `update_broadcast()`, `destroy_broadcast()`

# Fixes / Improvements

- Fixed: Guzzle version set to 6.5 or higher (#20, #26, #27)
- Fixed: `get_subscriber_id()` performance (#21, #22, #29, #39)
- Refactored: fetching legacy forms and landing pages (#32)
- Refactored: using `api_version` property, API calls and logging (#37, #38)
- Removed: Caching of resources and markup in class life cycle (#52)
- Removed: `InvalidArgumentException` where type hints for methods now exist (#43)

# Testing
- Added PHPStan static analysis (#40)
- Added PSR-12 coding standards with some modifications (#33)
- Updated PHPUnit test coverage (#30, #35)
```

Generic changelog items such as `Fix: Various bugfixes` or `Several edge-case bug fixes` should be avoided.  They don't tell users (or us, as developers)
what took place in this version.

Each line in the changelog should start with `Added` or `Fix`.

![New Release Screen](/.github/docs/new-release.png?raw=true)

## Publish the Release

When you're happy with the above, click `Publish Release`.

This will then make the release available to developers, who can include it manually or using composer.

The release will also be available to view on the [Releases](https://github.com/ConvertKit/convertkit-wordpress/releases) section of this GitHub repository.