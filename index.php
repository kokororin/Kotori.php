<?php
require './Kotori.php';

$app = new Kotori();

$app->set('APP_PATH', './App/');
$app->set('DB_HOST', '127.0.0.1');
$app->set('DB_USER', 'root');
$app->set('DB_PWD', 'root');
$app->set('DB_NAME', 'test');
$app->set('URL_ROUTE', array(
    'news/([0-9])' => 'Index/showNews/$1',
    'add' => array(
        'get' => 'Index/addNews',
        'post' => 'Index/insertNews',
    ),
));

$app->run();
