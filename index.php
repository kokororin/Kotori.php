<?php
require './Kotori.class.php';
//载入配置
$config = array(
    'APP_PATH'    => './App/', //项目目录
    'DB_HOST'     => '127.0.0.1',
    'DB_PORT'     => '3306',
    'DB_USER'     => 'root',
    'DB_PWD'      => 'root',
    'DB_NAME'     => 'blog',
    'USE_SESSION' => true,
);
Kotori::getInstance($config)->run();