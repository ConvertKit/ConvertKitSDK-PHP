# ConvertKit SDK PHP

ConvertKit's official PHP SDK

### Installation

#### Standard Installation

1. Download or clone this repository
2. Run `composer install`
3. Add `./vendor/autoload.php` to your project

#### Installation with Package Manager

If your project uses [Composer](https://getcomposer.org/), you can install the ConvertKitSDK-PHP package as a composer package. This allows you to have this project as a dependency without the ConvertKitSDK-PHP files being checked into your source code.

```shell
composer require convertkit/convertkitapi:0.1
```

**If you previously use or rely on `dev-master`, please use the `0.1` release to maintain compatibility with no breaking changes.**

This package is now versioned, and `1.0` will introduce breaking changes to bring this package up to date with coding standards, PHP versions and third party library versions.

### Usage

Get your ConvertKit API Key and API Secret [here](https://app.convertkit.com/account/edit) and set it somewhere in your application.

```php
$api = new \ConvertKit_API\ConvertKit_API($api_key, $api_secret);
```

### Examples

**Subscribe to a form**

Add a subscriber to a form. The `$subscribed` response will be an object.

```php
$tag_id = '99999'; // This tag must be valid for your ConvertKit account.

$options = [
			'email'      => 'test@test.com',
			'name'       => 'Full Name',
			'first_name' => 'First Name',
			'tags'       => $tag_id,
			'fields'     => [
				'phone' => 134567891243,
				'shirt_size' => 'M',
				'website_url' => 'testurl.com'
			]
		];

$subscribed = $api->form_subscribe($this->test_form_id, $options);
```

**Get Subscriber ID**

Get the ConvertKit Subscriber ID for a given email address.

```php
$subscriber_id = $api->get_subscriber_id( $email );
```

**Get Subscriber**

Get subscriber data for a ConvertKit Subscriber.

```php
$subscriber = $api->get_subscriber( $subscriber_id );
```

**Get Subscriber Tags**

Get all tags applied to a Subscriber.

```php
$subscriber_tags = $api->get_subscriber_tags( $subscriber_id );
```

**Add Tag to a Subscriber**

Apply a tag to a Subscriber.

```php
$tag_id = '99999'; // This tag must be valid for your ConvertKit account.
$api->add_tag($tag_id, [
			'email' => 'test@test.com'
		]);
```


