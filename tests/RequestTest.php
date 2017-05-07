<?php
namespace Kotori\Tests;

use Kotori\Http\Request;
use PHPUnit_Framework_TestCase;

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testPost()
    {
        $_POST = [
            'id' => 1,
            'name' => 'honoka',
        ];
        $request = new Request();
        $this->assertEquals('honoka', $request->post('name'));
    }

    public function testPostJSON()
    {
        $GLOBALS['HTTP_RAW_POST_DATA'] = json_encode([
            'id' => 1,
            'name' => 'honoka',
        ]);
        $request = new Request();
        $this->assertEquals('honoka', $request->post('name'));
    }

    public function testGet()
    {
        $_GET = [
            'id' => 1,
            'name' => 'honoka',
        ];
        $request = new Request();
        $this->assertEquals('honoka', $request->get('name'));
    }

    public function testCookieGet()
    {
        $request = new Request();
        $_COOKIE['name'] = 'honoka';
        $this->assertEquals('honoka', $request->cookie('name'));
    }
}
