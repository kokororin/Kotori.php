<?php
namespace Kotori\Tests;

use Kotori\App;
use PHPUnit_Framework_TestCase as TestCase;

class AppTest extends TestCase
{
    public function testApp()
    {
        $app = new App();
        $this->assertTrue(!empty($app));
    }
}
