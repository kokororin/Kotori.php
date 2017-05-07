<?php

/**
 * Require the Kotori.php using Composer's autoloader
 *
 * If you are not using Composer, you need to load Kotori.php with your own
 * PSR-4 autoloader.
 */

require __DIR__ . '/../../vendor/autoload.php';

$config = [
    'APP_NAME' => 'app',
    // 'APP_NAME' => [
    //     '1.kotori.php' => 'app',
    //     '2.kotori.php' => 'module2'
    // ],
    'DB' => [
        'test' => [
            'HOST' => '127.0.0.1',
            'USER' => 'root',
            'PWD' => '123456',
            'NAME' => 'test',
            'TYPE' => 'mysql',
        ],
        'dist' => [
            'HOST' => '127.0.0.1',
            'USER' => 'root',
            'PWD' => '123456',
            'NAME' => 'data',
            'TYPE' => 'mysql',
        ],
    ],
    // 'CACHE' => [
    //     'ADAPTER' => 'memcached',
    // ],
    'URL_MODE' => 'PATH_INFO',
    'URL_ROUTE' => [
        '/' => 'Hello/index',
        'news/([0-9])' => 'Hello/showNews/$1',
        'add' => [
            'get' => 'Hello/addNews',
            'post' => 'Hello/insertNews',
            'delete' => 'Hello/deleteNews',
        ],
        'captcha' => 'Hello/captcha',
        'memcache' => 'Hello/memcache',
        'cliTest/(.*)' => [
            'cli' => 'Hello/cli/$1',
        ],
        'test' => [
            'get' => 'Test/receiveGet',
            'post' => 'Test/receivePost',
        ],
    ],
];

$app = new \Kotori\App($config);

$app->run();
