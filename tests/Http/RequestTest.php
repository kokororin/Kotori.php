<?php
namespace Kotori\Tests\Http;

use Kotori\Http\Request;
use Kotori\Tests\Util;
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
        $request = new Request();
        // @codingStandardsIgnoreStart
        @$request->cookie('name', 'honoka');
        // @codingStandardsIgnoreEnd
        $this->assertEquals('honoka', $request->cookie('name'));
    }

    public function testDeleteCookie()
    {
        $request = new Request();
        // @codingStandardsIgnoreStart
        @$request->cookie('name', 'honoka');
        @$request->cookie('name', null);
        // @codingStandardsIgnoreEnd
        $this->assertNull($request->cookie('name'));
    }

    public function testIsSecure()
    {
        $request = new Request();
        $this->assertFalse($request->isSecure());
    }

    public function testGetBaseUrl()
    {
        $request = new Request();
        $this->assertEquals('http://kotori.php.dev/', $request->getBaseUrl());
    }

    public function testGetClientIp()
    {
        $request = new Request();
        $this->assertEquals('127.0.0.1', $request->getClientIp());
    }

    public function testGetHostName()
    {
        $request = new Request();
        $this->assertEquals('kotori.php.dev', $request->getHostName());
    }

    public function testIsMobile()
    {
        $request = new Request();
        $this->assertFalse($request->isMobile());
    }
}
