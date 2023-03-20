# ConvertKit SDK PHP

The ConvertKit PHP SDK provides convinient access to the ConvertKit API from applications written in the PHP language.

It includes a pre-defined set of methods for interacting with the API.

## Requirements

PHP 7.4 and later.

## Composer

You can install this PHP SDK via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require convertkit/convertkitapi
```

To use the PHP SDK, use Composer's [autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading):

```php
require_once 'vendor/autoload.php';
```

## Dependencies

The PHP SDK require the following extensions in order to work properly:

-   [`curl`](https://secure.php.net/manual/en/book.curl.php), although you can use your own non-cURL client if you prefer
-   [`json`](https://secure.php.net/manual/en/book.json.php)
-   [`mbstring`](https://secure.php.net/manual/en/book.mbstring.php) (Multibyte String)

If you use Composer, these dependencies should be handled automatically.

## Getting Started

Get your ConvertKit API Key and API Secret [here](https://app.convertkit.com/account/edit) and set it somewhere in your application.

```php
$api = new \ConvertKit_API\ConvertKit_API($api_key, $api_secret);
```

## Documentation

See the [PHP SDK docs]()