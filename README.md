# Kotori.php

[![Build Status](https://api.travis-ci.org/kokororin/Kotori.php.svg)](https://travis-ci.org/kokororin/Kotori.php)
[![GitHub release](https://img.shields.io/github/release/kokororin/Kotori.php.svg)](https://github.com/kokororin/Kotori.php/releases)
[![License](https://img.shields.io/badge/license-Apache%202-blue.svg)](https://packagist.org/packages/kokororin/kotori-php)

Kotori.php is a Tiny Model-View-Controller(MVC) PHP Framework.

## Installation

You need [Composer](https://getcomposer.org/) to install Kotori.php.

```bash
$ composer require kokororin/kotori-php
```

## Usage

```php
<?php
require 'vendor/autoload.php';

$app = new \Kotori\App();

$config['APP_PATH'] = './app/';

$app->run();
```

## Tests

To execute the test suite, you'll need phpunit.

```bash
$ phpunit
```

## LICENSE

Licensed under the Apache License, Version 2.0 (the "License").

## Learn More

Learn more at these links:

- [Website](https://kotori.love/archives/kotori-php-framework.html)
- [Documentation](https://github.com/kokororin/Kotori.php/wiki)

## Thanks

- [ThinkPHP](https://github.com/top-think/thinkphp)
- [CodeIgniter](https://github.com/bcit-ci/CodeIgniter)
- [Typecho](https://github.com/typecho/typecho)