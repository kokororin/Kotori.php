<?php
require __DIR__ . '/../vendor/autoload.php';

$config = [
    'APP_PATH' => './app',
    // 'APP_PATH' => [
    //     '1.kotori.php' => './app',
    //     '2.kotori.php' => './module2'
    // ],
    'DB_HOST' => '127.0.0.1',
    'DB_USER' => 'root',
    'DB_PWD' => '123456',
    'DB_NAME' => 'test',
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
