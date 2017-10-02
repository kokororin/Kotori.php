<?php
namespace Kotori\Tests\Core;

use Kotori\Facade\Cache;
use Kotori\Facade\Config;
use PHPUnit_Framework_TestCase as TestCase;

class CacheTest extends TestCase
{

    protected static $cache = null;

    public static function setUpBeforeClass()
    {
        Config::initialize([
            'app_debug' => false,
            'cache' => [
                'adapter' => 'memcached',
                'prefix' => '',
                'host' => '127.0.0.1',
                'port' => 11211,
                'weight' => 1,
            ],
        ]);
        // You must have php-memcached installed
    }

    public function testSetAndGet()
    {
        Cache::set('names', ['honoka', 'kotori']);
        $this->assertEquals(['honoka', 'kotori'], Cache::get('names'), "\$canonicalize = true", 0.0, 10, true);
    }

    public function testDelete()
    {
        Cache::set('name', 'honoka');
        Cache::delete('name');
        $this->assertFalse(Cache::get('name'));
    }

    public function testClean()
    {
        Cache::set('name', 'honoka');
        Cache::clean();
        $this->assertFalse(Cache::get('name'));
    }

}
