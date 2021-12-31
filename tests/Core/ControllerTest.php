<?php

/**
 * Kotori.php
 *
 * A Tiny Model-View-Controller PHP Framework
 *
 * This content is released under the Apache 2 License
 *
 * Copyright (c) 2015-2022 kokororin. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Kotori\Tests\Core;

use Kotori\Core\Controller;
use PHPUnit\Framework\TestCase;

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
        $this->assertInstanceOf(\Kotori\Debug\Logger::class, $instance->logger);
    }
}
