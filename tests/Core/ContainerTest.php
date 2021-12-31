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

use Kotori\Core\Container;
use PHPUnit\Framework\TestCase;

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
        $this->assertNotEmpty(Container::get('logger'));
    }
}
