<?php
namespace Kotori\Tests\Core;

use Kotori\Core\Container;
use PHPUnit_Framework_TestCase as TestCase;

class ContainerTest extends TestCase
{
    public function testGet()
    {
        $this->assertNotEmpty(Container::get('cache'));
        $this->assertNotEmpty(Container::get('config'));
        $this->assertNotEmpty(Container::get('controller'));
        $this->assertNotEmpty(Container::get('request'));
        $this->assertNotEmpty(Container::get('response'));
        $this->assertNotEmpty(Container::get('route'));
        $this->assertNotEmpty(Container::get('trace'));
        $this->assertNotEmpty(Container::get('model/provider'));
    }

}
