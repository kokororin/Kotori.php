<?php
namespace Kotori\Tests\Core;

use Kotori\Core\Config;
use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testGetConfig()
    {
        $config = new Config();
        $config->initialize([
            'in_test_env' => true,
        ]);
        $this->assertEquals(true, $config->get('app_debug'));
    }

    public function testSetConfig()
    {
        $config = new Config();
        $config->initialize([
            'in_test_env' => true,
            'my_env' => 'my_env',
        ]);
        $this->assertEquals('my_env', $config->get('my_env'));
    }

    public function testGetConfigArray()
    {
        $config = new Config();
        $config->initialize([
            'in_test_env' => true,
        ]);
        $this->assertTrue(is_array($config->getArray()));
    }
}
