<?php
namespace Kotori\Tests\Core;

use Kotori\Core\Controller;
use PHPUnit_Framework_TestCase as TestCase;

class ControllerTest extends TestCase
{
    public function testProperty()
    {
        $controller = new Controller();

        $this->assertProperty($controller);
    }

    protected function assertProperty($instance)
    {
        $this->assertInstanceOf(\Kotori\Core\View::class, $instance->view);
        $this->assertInstanceOf(\Kotori\Http\Response::class, $instance->response);
        $this->assertInstanceOf(\Kotori\Http\Request::class, $instance->request);
        $this->assertInstanceOf(\Kotori\Http\Route::class, $instance->route);
        $this->assertInstanceOf(\Kotori\Core\Model\Provider::class, $instance->model);
        $this->assertInstanceOf(\Kotori\Core\Config::class, $instance->config);
        $this->assertInstanceOf(\Kotori\Core\Cache::class, $instance->cache);
    }
}
