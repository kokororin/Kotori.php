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
        $this->assertNull(Cache::get('name'));
    }

    public function testClear()
    {
        Cache::set('name', 'honoka');
        Cache::clear();
        $this->assertNull(Cache::get('name'));
    }

    public function testSetMultipleAndGetMultiple()
    {
        $values = [
            'name' => 'honoka',
            'country' => 'japan',
        ];
        Cache::setMultiple($values);
        $this->assertEquals($values, Cache::getMultiple(array_keys($values)));
    }

    public function testDeleteMultiple()
    {
        $values = [
            'name' => 'honoka',
            'country' => 'japan',
        ];
        $assertValues = [
            'name' => null,
            'country' => null,
        ];
        Cache::setMultiple($values);
        Cache::deleteMultiple(array_keys($values));
        $this->assertEquals($assertValues, Cache::getMultiple(array_keys($values)));
    }

    public function testHas()
    {
        Cache::set('name', 'honoka');
        $this->assertTrue(Cache::has('name'));
        Cache::delete('name');
        $this->assertFalse(Cache::has('name'));
    }
}
