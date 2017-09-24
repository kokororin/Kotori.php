<?php
namespace Kotori\Tests\Core;

use Kotori\Core\View;

class ViewTest extends ControllerTest
{
    public function testProperty()
    {
        $view = new View();

        $this->assertProperty($view);
    }
}
