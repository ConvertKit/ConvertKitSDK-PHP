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
// Require the autoloader (if you're using a PHP framework, this may already be done for you).
require_once 'vendor/autoload.php';

// Initialize the API class.
$api = new \ConvertKit_API\ConvertKit_API('<your_public_api_key>', '<your_secret_api_key>');
```

## Handling Errors

The ConvertKit PHP SDK uses Guzzle for all HTTP API requests.  Errors will be thrown as Guzzle's `ClientException` (for 4xx errors),
or `ServerException` (for 5xx errors).

```php
try {
    $forms = $api->add_subscriber_to_form('invalid-form-id');
} catch (GuzzleHttp\Exception\ClientException $e) {
    // Handle 4xx client errors.
    die($e->getMessage());
} catch (GuzzleHttp\Exception\ServerException $e) {
    // Handle 5xx server errors.
    die($e->getMessage());
}
```

For a more detailed error message, it's possible to fetch the API's response when a `ClientException` is thrown:

```php
// Errors will be thrown as Guzzle's ClientException or ServerException.
try {
    $forms = $api->form_subscribe('invalid-form-id');
} catch (GuzzleHttp\Exception\ClientException $e) {
    // Handle 4xx client errors.
    // For ClientException, it's possible to inspect the API's JSON response
    // to output an error or handle it accordingly.
    $error = json_decode($e->getResponse()->getBody()->getContents());
    die($error->message); // e.g. "Entity not found".
} catch (GuzzleHttp\Exception\ServerException $e) {
    // Handle 5xx server errors.
    die($e->getMessage());
}
```

## Documentation

See the [PHP SDK docs](./docs/classes/ConvertKit_API/ConvertKit_API.md)

## Contributing

See our [contributor guide](CONTRIBUTING.md) for setting up your development environment, testing and submitting a PR.

For ConvertKit, refer to the [deployment guide](DEPLOYMENT.md) on how to publish a new release.
