<?php
namespace Kotori\Tests\Http;

use Kotori\Facade\Config;
use Kotori\Facade\Request;
use Kotori\Tests\Util;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{

    protected static $END_POINT = null;

    public static function setUpBeforeClass(): void
    {
        self::$END_POINT = 'http://' . getenv('WEB_SERVER_HOST') . ':' . getenv('WEB_SERVER_PORT') . '/test';
        Config::initialize([
            'session' => [
                'adapter' => 'memcached',
            ],
        ]);
    }

    public function testPost()
    {
        $response = Util::post(self::$END_POINT . '/post', [
            'id' => '1',
            'name' => 'honoka',
        ]);
        $response = json_decode($response, true);
        $this->assertEquals('honoka', $response['name']);
    }

    public function testPostJSON()
    {
        $response = Util::postJSON(self::$END_POINT . '/post', [
            'id' => '1',
            'name' => 'honoka',
        ]);
        $response = json_decode($response, true);
        $this->assertEquals('honoka', $response['name']);
    }

    public function testGet()
    {
        $response = Util::get(self::$END_POINT . '/get', [
            'id' => 1,
            'name' => 'honoka',
        ]);
        $response = json_decode($response, true);
        $this->assertEquals('honoka', $response['name']);
    }

    public function testSetAndGetCookie()
    {
        // @codingStandardsIgnoreStart
        @Request::cookie('name', 'honoka');
        // @codingStandardsIgnoreEnd
        $this->assertEquals('honoka', Request::cookie('name'));
    }

    public function testDeleteCookie()
    {
        // @codingStandardsIgnoreStart
        @Request::cookie('name', 'honoka');
        @Request::cookie('name', null);
        // @codingStandardsIgnoreEnd
        $this->assertNull(Request::cookie('name'));
    }

    public function testSetAndGetSession()
    {
        // @codingStandardsIgnoreStart
        @Request::session('name', 'honoka');
        // @codingStandardsIgnoreEnd
        $this->assertEquals('honoka', Request::session('name'));
    }

    public function testDeleteSession()
    {
        // @codingStandardsIgnoreStart
        @Request::session('name', 'honoka');
        @Request::session('name', null);
        // @codingStandardsIgnoreEnd
        $this->assertNull(Request::session('name'));
    }

    public function testIsSecure()
    {
        $this->assertFalse(Request::isSecure());
    }

    public function testGetBaseUrl()
    {
        $this->assertEquals('http://kotori.php.dev/', Request::getBaseUrl());
    }

    public function testGetClientIp()
    {
        $this->assertEquals('127.0.0.1', Request::getClientIp());
    }

    public function testGetHostName()
    {
        $this->assertEquals('kotori.php.dev', Request::getHostName());
    }

    public function testGetHeader()
    {
        $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36';
        $this->assertEquals($userAgent, Request::getHeader('user_agent'));
        $this->assertEquals($userAgent, Request::getHeader('user-agent'));
        $this->assertEquals($userAgent, Request::getHeader('User_Agent'));
        $this->assertEquals($userAgent, Request::getHeader('User-Agent'));
    }

    public function testIsMobile()
    {
        $this->assertFalse(Request::isMobile());
    }
}
