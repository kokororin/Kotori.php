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
        'new/([0-9])' => 'Index/showNews/$1',
        'add' => [
            'get' => 'Index/addNews',
            'post' => 'Index/insertNews',
        ],
    ],
];

$app = new \Kotori\App($config);

$app->run();
