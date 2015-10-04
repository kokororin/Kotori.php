<?php
require './Kotori.class.php';

Kotori::run(array(
    'APP_PATH' => './App/',
    'DB_HOST' => '127.0.0.1',
    'DB_USER' => 'root',
    'DB_PWD' => '123456',
    'DB_NAME' => 'test',
));
