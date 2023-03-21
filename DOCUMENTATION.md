# ConvertKit SDK PHP Documentation

This documentation details the available methods provided by the PHP SDK.

For each method, a link to the ConvertKit API documentation is included.

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

## Account

### Show the current account

[API Docs](https://developers.convertkit.com/#account)

```php
$account = $api->get_account();
```

## Forms

### List forms

[API Docs](https://developers.convertkit.com/#forms)

```php
$forms = $api->get_forms();
```

### List landing pages

[API Docs](https://developers.convertkit.com/#forms)

```php
$landingPages = $api->get_landing_pages();
```

### Add subscriber to a form

[API Docs](https://developers.convertkit.com/#add-subscriber-to-a-form)

```php
$subscriber = $api->add_subscriber_to_form( $form_id, ... );
```

### List subscriptions to a form

```php
$subscriptions = $api->get_form_subscriptions(
    int $form_id,
    string $sort_order = 'asc',
    string $subscriber_state = 'active',
    int $page = 1
);
```

## Sequences

### List sequences

```php
$sequences = $api->get_sequences();
```

### Add subscriber to a sequence

