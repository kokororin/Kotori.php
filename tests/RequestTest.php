<?php
namespace Kotori\Tests;

use Curl\Curl;
use Kotori\Http\Request;
use PHPUnit_Framework_TestCase;

class RequestTest extends PHPUnit_Framework_TestCase
{

    protected $route = 'http://' . WEB_SERVER_HOST . ':' . WEB_SERVER_PORT . '/test';

    public function testPost()
    {
        $curl = new Curl();
        $curl->post($this->route, [
            'id' => 1,
            'name' => 'honoka',
        ]);
        $response = $curl->response;
        $this->assertEquals('honoka', $curl->response->name);
    }

    public function testPostJSON()
    {
        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->post($this->route, [
            'id' => 1,
            'name' => 'honoka',
        ]);
        $this->assertEquals('honoka', $curl->response->name);
    }

    public function testGet()
    {
        $curl = new Curl();
        $curl->get($this->route, [
            'id' => 1,
            'name' => 'honoka',
        ]);
        $response = $curl->response;
        $this->assertEquals('honoka', $curl->response->name);
    }

    public function testCookieGet()
    {
        $request = new Request();
        $_COOKIE['name'] = 'honoka';
        $this->assertEquals('honoka', $request->cookie('name'));
    }
}
