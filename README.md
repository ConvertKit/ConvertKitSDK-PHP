# ConvertKit SDK PHP
ConvertKit's official PHP SDK

### Installation

1. Download or clone this repository
2. Run `composer install`
3. Add `./vendor/auoload.php` to your project

### Usage

Get your ConvertKit API Key and API Secret [here](https://app.convertkit.com/account/edit) and set it somewhere in your application.

```php
$api = new \ConvertKit_API\ConvertKit_API($api_key, $api_secret);
```

