<?php
namespace Kotori\Tests;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public function testApp()
    {
        $app = new \Kotori\App();
        $this->assertTrue(!empty($app));
    }
}
