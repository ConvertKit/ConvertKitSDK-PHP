# ConvertKit SDK PHP

The ConvertKit PHP SDK provides convinient access to the ConvertKit API from applications written in the PHP language.

It includes a pre-defined set of methods for interacting with the API.

## Version Guidance

| SDK Version | API Version | API Authentication | PHP Version  |
|-------------|-------------|--------------------|--------------|
| 1.x         | v3          | API Key and Secret | 7.4+         |
| 2.x         | v4          | OAuth              | 8.0+         |

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

### 2.x (v4 API, OAuth, PHP 8.0+)

Please reach out to ConvertKit to set up an OAuth application for you. We'll provide you with your Client ID and Secret.

```php
// Require the autoloader (if you're using a PHP framework, this may already be done for you).
require_once 'vendor/autoload.php';

// Initialize the API class.
$api = new \ConvertKit_API\ConvertKit_API(
    clientID: '<your_oauth_client_id>',
    clientSecret: '<your_oauth_client_secret>'
);
```

To begin the OAuth process, redirect the user to ConvertKit to grant your application access to their ConvertKit account.

```php
header('Location: '.$api->get_oauth_url('<your_redirect_uri>'));
```

Once the user grants your application access to their ConvertKit account, they'll be redirected to your Redirect URI with an authorization code. For example:

`your-redirect-uri?code=<auth_code>`

At this point, your application needs to exchange the authorization code for an access token and refresh token.

```php
$result = $api->get_access_token(
    authCode: '<auth_code>',
    redirectURI: '<your_redirect_uri>'
);
```

`$result` is an array comprising of:
- `access_token`: The access token, used to make authenticated requests to the API
- `refresh_token`: The refresh token, used to fetch a new access token once the current access token has expired
- `created_at`: When the access token was created
- `expires_in`: The number of seconds from `created_at` that the access token will expire

Once you have an access token, re-initialize the API class with it:

```php
// Initialize the API class.
$api = new \ConvertKit_API\ConvertKit_API(
    clientID: '<your_oauth_client_id>',
    clientSecret: '<your_oauth_client_secret>',
    accessToken: '<your_access_token>'
);
```

To refresh an access token:

```php
$result = $api->refresh_token(
    refreshToken: '<your_refresh_token>',
    redirectURI: '<your_redirect_uri>'
);
```

`$result` is an array comprising of:
- `access_token`: The access token, used to make authenticated requests to the API
- `refresh_token`: The refresh token, used to fetch a new access token once the current access token has expired
- `created_at`: When the access token was created
- `expires_in`: The number of seconds from `created_at` that the access token will expire

Once you have refreshed the access token i.e. obtained a new access token, re-initialize the API class with it:

```php
// Initialize the API class.
$api = new \ConvertKit_API\ConvertKit_API(
    clientID: '<your_oauth_client_id>',
    clientSecret: '<your_oauth_client_secret>',
    accessToken: '<your_new_access_token>'
);
```

API requests may then be performed:

```php
$result = $api->add_subscriber_to_form(12345, 'joe.bloggs@convertkit.com');
```

To determine whether a new entity / relationship was created, or an existing entity / relationship updated, inspect the HTTP code of the last request:

```php
$result = $api->add_subscriber_to_form(12345, 'joe.bloggs@convertkit.com');
$code = $api->getResponseInterface()->getStatusCode(); // 200 OK if e.g. a subscriber already added to the specified form, 201 Created if the subscriber added to the specified form for the first time.
```

The PSR-7 response can be fetched and further inspected, if required - for example, to check if a header exists:

```php
$result = $api->add_subscriber_to_form(12345, 'joe.bloggs@convertkit.com');
$api->getResponseInterface()->hasHeader('Content-Length'); // Check if the last API request included a `Content-Length` header
```

### 1.x (v3 API, API Key and Secret, PHP 7.4+)

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
