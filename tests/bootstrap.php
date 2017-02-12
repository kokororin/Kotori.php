<?php
// Errors on full!
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';

$autoloader->addPsr4('Kotori\Tests\\', __DIR__);
