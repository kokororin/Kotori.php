<?php
namespace Kotori\Tests\Core;

use Kotori\Core\Model;

class ModelTest extends ControllerTest
{
    public function testProperty()
    {
        $model = new Model();

        $this->assertProperty($model);
    }
}
