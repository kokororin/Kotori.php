<?php
namespace Kotori\Tests;

use Kotori\App;
use Kotori\Core\Config;

class AppTest extends \PHPUnit_Framework_TestCase
{

    public function testApp()
    {
        $app = new App();
        $this->assertTrue(!empty($app));
    }

    public function testGetConfig()
    {
        $config = new Config();
        $config->initialize();
        $this->assertEquals(true, $config->APP_DEBUG);
    }

    public function testSetConfig()
    {
        $config = new Config();
        $config->initialize([
            'MY_ENV' => 'MY_ENV',
        ]);
        $this->assertEquals('MY_ENV', $config->MY_ENV);
    }

    public function testGetConfigArray()
    {
        $config = new Config();
        $config->initialize();
        $this->assertTrue(is_array($config->getArray()));
    }
}
