<?php
require __DIR__ . '/../vendor/autoload.php';

$config = [
    'APP_PATH' => './app',
    'DB_HOST' => '127.0.0.1',
    'DB_USER' => 'root',
    'DB_PWD' => '123456',
    'DB_NAME' => 'test',
    'DB_TYPE' => 'mysql',
    'URL_ROUTE' => [
        '/' => 'Hello/index',
        'news/([0-9])' => 'Hello/showNews/$1',
        'add' => [
            'get' => 'Hello/addNews',
            'post' => 'Hello/insertNews',
            'delete' => 'Hello/deleteNews',
        ],
    ],
];

$app = new \Kotori\App($config);

$app->run();
