<?php
require './Kotori.php';

Kotori::run(array(
    'APP_PATH'  => './App/',
    'DB_HOST'   => '127.0.0.1',
    'DB_USER'   => 'root',
    'DB_PWD'    => 'root',
    'DB_NAME'   => 'test',
    'URL_ROUTE' => array(
        'news/([0-9])' => 'Index/showNews/$1',
    ),
));
