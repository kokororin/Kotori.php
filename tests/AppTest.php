<?php
namespace Kotori\Tests;

use Kotori\App;
use PHPUnit_Framework_TestCase;

class AppTest extends PHPUnit_Framework_TestCase
{
    public function testApp()
    {
        $app = new App();
        $this->assertTrue(!empty($app));
    }
}
