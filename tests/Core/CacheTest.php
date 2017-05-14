<?php
namespace Kotori\Tests\Core;

use Kotori\Core\Cache;
use Kotori\Core\Config;
use PHPUnit_Framework_TestCase;

class CacheTest extends PHPUnit_Framework_TestCase
{

    protected static $cache = null;

    public static function setUpBeforeClass()
    {
        $config = Config::getSoul();
        $config->initialize([
            'ENV' => 'test',
            'APP_DEBUG' => false,
            'CACHE' => [
                'ADAPTER' => 'memcached',
                'PREFIX' => '',
                'HOST' => '127.0.0.1',
                'PORT' => 11211,
                'WEIGHT' => 1,
            ],
        ]);
        // You must have php-memcached installed
        self::$cache = Cache::getSoul();
    }

    public function testSetAndGet()
    {
        self::$cache->set('names', ['honoka', 'kotori']);
        $this->assertEquals(['honoka', 'kotori'], self::$cache->get('names'), "\$canonicalize = true", 0.0, 10, true);
    }

    public function testDelete()
    {
        self::$cache->set('name', 'honoka');
        self::$cache->delete('name');
        $this->assertFalse(self::$cache->get('name'));
    }

    public function testClean()
    {
        self::$cache->set('name', 'honoka');
        self::$cache->clean();
        $this->assertFalse(self::$cache->get('name'));
    }

}
