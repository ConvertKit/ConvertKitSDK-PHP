# Setup Guide

This document describes how to setup your development environment, so that it is ready to run, develop and test the ConvertKit PHP SDK.

Suggestions are provided for the LAMP/LEMP stack and Git client are for those who prefer the UI over a command line and/or are less familiar with 
WordPress, PHP, MySQL and Git - but you're free to use your preferred software.

## Setup

### LAMP/LEMP stack

Any Apache/nginx, PHP 7.x+ and MySQL 5.8+ stack running.  For example, but not limited to:
- Local by Flywheel (recommended)
- Docker
- MAMP
- WAMP
- VVV

### Composer

If [Composer](https://getcomposer.org) is not installed on your local environment, enter the following commands at the command line to install it:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

Confirm that installation was successful by entering the `composer` command at the command line

### Clone Repository

Using your preferred Git client or command line to clone this repository.

If you're new to this, use [GitHub Desktop](https://desktop.github.com/) or [Tower](https://www.git-tower.com/mac)

### Configure Testing Environment

Copy the `.env.example` file to `.env` in the root of this repository, adding your ConvertKit API keys.

```
CONVERTKIT_API_KEY_NO_DATA=
CONVERTKIT_API_SECRET_NO_DATA=
CONVERTKIT_API_KEY=
CONVERTKIT_API_SECRET=
CONVERTKIT_API_BROADCAST_ID="8697158"
CONVERTKIT_API_FORM_ID="2765139"
CONVERTKIT_API_LEGACY_FORM_URL="https://app.convertkit.com/landing_pages/470099"
CONVERTKIT_API_LANDING_PAGE_URL="https://cheerful-architect-3237.ck.page/cc5eb21744"
CONVERTKIT_API_LEGACY_LANDING_PAGE_URL="https://app.convertkit.com/landing_pages/470103"
CONVERTKIT_API_SEQUENCE_ID="1030824"
CONVERTKIT_API_TAG_NAME="wordpress"
CONVERTKIT_API_TAG_ID="2744672"
CONVERTKIT_API_SUBSCRIBER_EMAIL="optin@n7studios.com"
CONVERTKIT_API_SUBSCRIBER_ID="1579118532"
```

#### PHPStan

Copy the `phpstan.neon.example` file to `phpstan.neon` in the root of this repository:
```yaml
# Parameters
parameters:
    # Paths to scan
    paths:
        - src/

    # Should not need to edit anything below here
    # Rule Level: https://phpstan.org/user-guide/rule-levels
    level: 8

    # Ignore the following errors, as PHPStan either does not have registered symbols for them yet,
    # or the symbols are inaccurate.
    ignoreErrors:
        - '#\$headers of class GuzzleHttp\\Psr7\\Request constructor expects#'
```

### Install Packages

In the root directory, at the command line, run `composer install`.

This will install two types of packages:
- Packages used by the SDK (e.g. Guzzle and Monolog)
- Packages used in the process of development (i.e. testing, coding standards):
-- PHPStan
-- PHPUnit
-- PHP_CodeSniffer

### Install phpDocumentor

Download the latest phpDocumentor release.

On Mac / Linux, mark it as executable and move it to your bin folder, so it can be run globally:
```bash
chmod +x phpDocumentor.phar
mv phpDocumentor.phar /usr/local/bin/phpDocumentor
```

### Running PHPUnit Tests

In a Terminal window, run the tests to make sure there are no errors and that you have 
correctly setup your environment:

```bash
vendor/bin/phpunit
```

Don't worry if you don't understand these commands; if your output looks similar to the above screenshot, and no test is prefixed with `E`, 
your environment is setup successfully.

### Running CodeSniffer

In the root directory, run the following commands to run PHP_CodeSniffer, which will check the code meets PHP Coding Standards:

```bash
vendor/bin/phpcs -s -v
vendor/bin/phpcs -s -v --standard=phpcs.tests.xml
```

![Coding Standards Test Results](/.github/docs/coding-standards.png?raw=true)

Again, don't worry if you don't understand these commands; if your output looks similar to the above screenshot, with no errors, your environment
is setup successfully.

### Running PHPStan

In the Plugin's directory, run the following command to run PHPStan, which will perform static analysis on the code, checking it meets required
standards, that PHP DocBlocks are valid, WordPress action/filter DocBlocks are valid etc:

```bash
vendor/bin/phpstan --memory-limit=1G
```

![PHPStan Test Results](/.github/docs/phpstan.png?raw=true)

Again, don't worry if you don't understand these commands; if your output looks similar to the above screenshot, with no errors, your environment
is setup successfully.

### Next Steps

With your development environment setup, you'll probably want to start development, which is covered in the [Development Guide](DEVELOPMENT.md)