<?php
require './Kotori.class.php';
header("Content-type:text/html;charset=utf-8");
//载入配置
$config = array(
    'APP_PATH'    => './App/', //项目目录
    'DB_TYPE'	 =>  'mysql', 
    'DB_HOST'     => '127.0.0.1',
    'DB_PORT'     => '3306',
    'DB_USER'     => 'root',
    'DB_PWD'      => 'root',
    'DB_NAME'     => 'blog',
    'DB_CHARSET'  => 'utf8',
    'USE_SESSION' => true,
);
Kotori::getInstance($config)->run();