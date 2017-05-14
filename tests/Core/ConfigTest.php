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
            'ENV' => getenv('APP_ENV'),
        ]);
        $this->assertEquals(true, $config->APP_DEBUG);
    }

    public function testSetConfig()
    {
        $config = new Config();
        $config->initialize([
            'ENV' => getenv('APP_ENV'),
            'MY_ENV' => 'MY_ENV',
        ]);
        $this->assertEquals('MY_ENV', $config->MY_ENV);
    }

    public function testGetConfigArray()
    {
        $config = new Config();
        $config->initialize([
            'ENV' => getenv('APP_ENV'),
        ]);
        $this->assertTrue(is_array($config->getArray()));
    }
}
