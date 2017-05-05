# 🐦Kotori.php

[![Build Status](https://api.travis-ci.org/kokororin/Kotori.php.svg)](https://travis-ci.org/kokororin/Kotori.php)
[![Packagist](https://img.shields.io/packagist/dt/kokororin/kotori-php.svg?maxAge=2592000)](https://packagist.org/packages/kokororin/kotori-php)
[![License](https://img.shields.io/badge/license-Apache%202-blue.svg)](https://github.com/kokororin/Kotori.php/blob/master/LICENSE)

Kotori.php is a Tiny Model-View-Controller(MVC) PHP Framework.

![](https://raw.githubusercontent.com/kokororin/Kotori.php/master/src/Kotori.gif)

## Installation

You need [Composer](https://getcomposer.org/) to install Kotori.php.

```bash
$ composer require kokororin/kotori-php:dev-master
```

## Usage

```php
<?php
require 'vendor/autoload.php';

$app = new \Kotori\App();

$app->run();
```

## Tests

To execute the test suite, you'll need phpunit.

```bash
$ composer test
```

## LICENSE

Licensed under the Apache License, Version 2.0 (the "License").

## Learn More

Learn more at these links:

- [Website](https://kotori.love/archives/kotori-php-framework.html)
- [Documentation](https://github.com/kokororin/Kotori.php/wiki)
- [CLI tool](https://github.com/kasumi9863/kotori-php-cli)

## Thanks

- [ThinkPHP](https://github.com/top-think/thinkphp)
- [CodeIgniter](https://github.com/bcit-ci/CodeIgniter)
- [Typecho](https://github.com/typecho/typecho)