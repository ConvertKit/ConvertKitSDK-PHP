# Migrating from v1.x SDK (v3 API) to v2.x SDK (v4 API)

Whilst every best effort is made to minimise the number of breaking changes, some breaking changes exist to ensure improved method naming conventions and compatibility with OAuth authentication and the v4 API.

This guide is designed to cover changes that developers may need to make to their existing implementation when upgrading to the v2 SDK.

## PHP Version

The minimum supported PHP version is `8.0`.  Users on older PHP versions should continue to use the v1 SDK.

## Authentication

Authentication is now via OAuth.  It's recommended to refer to the README file's [`Getting Started`](README.md#2x-v4-api-oauth-php-80) section for implementation.

Initializing the `ConvertKit_API` class now accepts a `clientID`, `clientSecret` and `accessToken` in place of the existing `api_key` and `api_secret`:

```php
$api = new \ConvertKit_API\ConvertKit_API(
    clientID: '<your_oauth_client_id>',
    clientSecret: '<your_oauth_client_secret>',
    accessToken: '<your_oauth_access_token>'
);
```

## Pagination

For list based endpoints which fetch data from the API (such as broadcasts, custom fields, subscribers, tags, email templates, forms, purchases etc.), cursor based pagination is used.  The following parameters can be specified in the API methods:

- `per_page`: Defines the number of results to return, with a maximum value of 100
- `after_cursor`: When specified, returns the next page of results based on the current result's `pagination->end_cursor` value
- `before_cursor`: When specified, returns the previous page of results based on the current result's `pagination->start_cursor` value

## Accounts

- Added: `get_account_colors()`
- Added: `update_account_colors()`
- Added: `get_creator_profile()`
- Added: `get_email_stats()`
- Added: `get_growth_stats()`

## Broadcasts

- Updated: `get_broadcasts()` supports pagination
- Updated: `create_broadcast()`:
  - `email_layout_template` is now `email_template_id`. To fetch the ID of the account's email templates, refer to `get_email_templates()`
  - `preview_text` option added
  - `subscriber_filter` option added
- Updated: `update_broadcast()`
  - `email_layout_template` is now `email_template_id`. To fetch the ID of the account's email templates, refer to `get_email_templates()`
  - `preview_text` option added
  - `subscriber_filter` option added
- Changed: `destroy_broadcast()` is renamed to `delete_broadcast()`

## Custom Fields

- Added: `create_custom_fields()` to create multiple custom fields in a single request
- Updated: `get_custom_fields()` supports pagination

## Subscribers

- Added: `create_subscriber()`. The concept of creating a subscriber via a form, tag or sequence is replaced with this new method. The subscriber can then be subscribed to resources (forms, tag, sequences) as necessary.
- Added: `create_subscribers()` to create multiple subscribers in a single request
- Added: `get_subscribers()`
- Changed: `unsubscribe()` is now `unsubscribe_by_email()`. Use `unsubscribe()` for unsubscribing by a subscriber ID
- Updated: `get_subscriber_tags()` supports pagination

## Tags

- Added: `create_tags()` to create multiple tags in a single request
- Updated: `get_tags()` supports pagination
- Updated: `get_tag_subscriptions()`:
  - supports pagination
  - supports filtering by subscribers by dates, covering `created_after`, `created_before`, `tagged_after` and `tagged_before`
  - `sort_order` is no longer supported
- Changed: `tag_subscriber()` is now `tag_subscriber_by_email()`. Use `tag_subscriber()` for tagging by subscriber ID

## Email Templates

- Added: `get_email_templates()`

## Forms

- Updated: `get_forms()`:
  - supports pagination
  - only returns active forms by default. Use the `status` parameter to filter by `active`, `archived`, `trashed` or `all`
- Updated: `get_landing_pages()`:
  - supports pagination
  - only returns active landing pages by default. Use the `status` parameter to filter by `active`, `archived`, `trashed` or `all`
- Updated: `get_form_subscriptions()`:
  - supports pagination
  - supports filtering by subscribers by dates, covering `created_after`, `created_before`, `added_after` and `added_before`
  - `sort_order` is no longer supported
- Changed: `add_subscriber_to_form()` is now `add_subscriber_to_form_by_email()`. Use `add_subscriber_to_form()` for adding subscriber to form by subscriber ID

## Purchases

- Updated: `create_purchase()` now supports named parameters for purchase data, instead of an `$options` array
- Changed: `list_purchases()` is now `get_purchases()`, with pagination support

## Segments

- Added: `get_segments()`

## Sequences

- Changed: `add_subscriber_to_sequence()` is now `add_subscriber_to_sequence_by_email()`. Use `add_subscriber_to_sequence()` for adding a subscriber to a sequence by subscriber ID
- Updated: `get_sequences()` supports pagination
- Updated: `get_sequence_subscriptions()`:
  - supports pagination
  - supports filtering by subscribers by dates, covering `created_after`, `created_before`, `added_after` and `added_before`
  - `sort_order` is no longer supported

## Webhooks

- Added: `get_webhooks()`
- Changed: `destroy_webhook()` is now `delete_webhook()`

## Other

- Removed: `form_subscribe()` was previously deprecated. Use `add_subscriber_to_form()` or `add_subscriber_to_form_by_email()`
- Removed: `add_tag()` was previously deprecated. Use `tag_subscriber()` or `tag_subscriber_by_email()`