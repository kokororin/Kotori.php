# 🐦Kotori.php

[![Build Status](https://api.travis-ci.org/kokororin/Kotori.php.svg)](https://travis-ci.org/kokororin/Kotori.php)
[![Version](https://badge.fury.io/ph/kokororin%2Fkotori-php.svg)](https://packagist.org/packages/kokororin/kotori-php)
[![Packagist](https://img.shields.io/packagist/dt/kokororin/kotori-php.svg?maxAge=2592000)](https://packagist.org/packages/kokororin/kotori-php)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-Apache%202-blue.svg)](https://github.com/kokororin/Kotori.php/blob/master/LICENSE)
[![Code Coverage](https://scrutinizer-ci.com/g/kokororin/Kotori.php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kokororin/Kotori.php/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kokororin/Kotori.php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kokororin/Kotori.php/?branch=master)

Kotori.php is a Tiny Model-View-Controller(MVC) PHP Framework.

![](https://kokororin.github.io/Kotori.php/src/Kotori.jpg)

## Installation

You need [Composer](https://getcomposer.org/) to install Kotori.php.

```bash
$ composer require kokororin/kotori-php
```

## Quick Start
```bash
$ composer global require kasumi/kotori-php-cli:dev-master
$ kotori init
$ cd awesome-project
$ kotori serve --port 2333
```

## Usage

```php
require __DIR__ . '/../vendor/autoload.php';

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

## Contributors

Thanks goes to these wonderful people ([emoji key](https://github.com/kentcdodds/all-contributors#emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
| [<img src="https://avatars0.githubusercontent.com/u/10093992?v=4" width="100px;"/><br /><sub>そら</sub>](http://kokororin.github.io)<br />[💻](https://github.com/kokororin/Kotori.php/commits?author=kokororin "Code") [📖](https://github.com/kokororin/Kotori.php/commits?author=kokororin "Documentation") [🤔](#ideas-kokororin "Ideas, Planning, & Feedback") [⚠️](https://github.com/kokororin/Kotori.php/commits?author=kokororin "Tests") | [<img src="https://avatars0.githubusercontent.com/u/12712012?v=4" width="100px;"/><br /><sub>吟夢ちゃん</sub>](https://kirainmoe.com/)<br />[💻](https://github.com/kokororin/Kotori.php/commits?author=kirainmoe "Code") [📖](https://github.com/kokororin/Kotori.php/commits?author=kirainmoe "Documentation") | [<img src="https://avatars2.githubusercontent.com/u/10379210?v=4" width="100px;"/><br /><sub>かすみ</sub>](https://github.com/kasumi9863)<br />[🔧](#tool-kasumi9863 "Tools") |
| :---: | :---: | :---: |
<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/kentcdodds/all-contributors) specification. Contributions of any kind welcome!
