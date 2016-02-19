<?php

class KotoriPHPTest extends \PHPUnit_Framework_TestCase
{
    public function __construct($config)
    {
        require '../Kotori.php';
        $app = new Kotori();
        $app->run();
    }
}
