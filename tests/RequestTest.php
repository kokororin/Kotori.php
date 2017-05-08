<?php
namespace Kotori\Tests;

use Curl\Curl;
use Exception;
use Kotori\Http\Request;
use PHPUnit_Framework_TestCase;

class RequestTest extends PHPUnit_Framework_TestCase
{

    protected static $ROUTE = null;

    public static function setUpBeforeClass()
    {
        self::$ROUTE = 'http://' . getenv('WEB_SERVER_HOST') . ':' . getenv('WEB_SERVER_PORT') . '/test';
    }

    public function testPost()
    {
        $curl = new Curl();
        $curl->post(self::$ROUTE, [
            'id' => 1,
            'name' => 'honoka',
        ]);
        if ($curl->error) {
            throw new Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
        }

        $this->assertEquals('honoka', $curl->response->name);
    }

    public function testPostJSON()
    {
        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->post(self::$ROUTE, [
            'id' => 1,
            'name' => 'honoka',
        ]);
        if ($curl->error) {
            throw new Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
        }

        $this->assertEquals('honoka', $curl->response->name);
    }

    public function testGet()
    {
        $curl = new Curl();
        $curl->get(self::$ROUTE, [
            'id' => 1,
            'name' => 'honoka',
        ]);
        if ($curl->error) {
            throw new Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
        }

        $this->assertEquals('honoka', $curl->response->name);
    }

    public function testCookieGet()
    {
        $request = new Request();
        $_COOKIE['name'] = 'honoka';
        $this->assertEquals('honoka', $request->cookie('name'));
    }
}
