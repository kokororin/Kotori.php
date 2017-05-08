<?php
namespace Kotori\Tests;

use PHPUnit_Framework_TestCase;

class RequestTest extends PHPUnit_Framework_TestCase
{

    protected static $END_POINT = null;

    public static function setUpBeforeClass()
    {
        self::$END_POINT = 'http://' . getenv('WEB_SERVER_HOST') . ':' . getenv('WEB_SERVER_PORT') . '/test';
    }

    public function testPost()
    {
        $response = Util::post(self::$END_POINT . '/post', [
            'id' => 1,
            'name' => 'honoka',
        ]);
        $this->assertEquals('honoka', $response->name);
    }

    public function testPostJSON()
    {
        $response = Util::postJSON(self::$END_POINT . '/post', [
            'id' => 1,
            'name' => 'honoka',
        ]);
        $this->assertEquals('honoka', $response->name);
    }

    public function testGet()
    {
        $response = Util::get(self::$END_POINT . '/get', [
            'id' => 1,
            'name' => 'honoka',
        ]);
        $this->assertEquals('honoka', $response->name);
    }

    public function testSetAndGetCookie()
    {
        $response = Util::get(self::$END_POINT . '/setAndGetCookie');
        $this->assertEquals('honoka', $response);
    }

    public function testDeleteCookie()
    {
        $response = Util::get(self::$END_POINT . '/deleteCookie');
        $this->assertEquals('null', $response);
    }

    public function testIsSecure()
    {
        $response = Util::get(self::$END_POINT . '/isSecure');
        $this->assertEquals(false, $response);
    }

    public function testGetBaseUrl()
    {
        $response = Util::get(self::$END_POINT . '/getBaseUrl');
        $this->assertEquals('http://' . getenv('WEB_SERVER_HOST') . ':' . getenv('WEB_SERVER_PORT') . '/', $response);
    }
}
