<?php
require __DIR__ . '/../vendor/autoload.php';

$app = new Kotori\App();

$config['APP_PATH'] = './app/';
$config['DB_HOST'] = '127.0.0.1';
$config['DB_USER'] = 'root';
$config['DB_PWD'] = 'test';
$config['DB_NAME'] = 'test';
//$config['DB_TYPE'] = 'mysql';
$config['URL_ROUTE']['news/([0-9])'] = 'Index/showNews/$1';
$config['URL_ROUTE']['add']['get'] = 'Index/addNews';
$config['URL_ROUTE']['add']['post'] = 'Index/insertNews';

$app->run();
