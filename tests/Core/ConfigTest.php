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

use Kotori\Core\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testGetConfig()
    {
        $config = new Config();
        $config->initialize();
        $this->assertEquals(true, $config->get('app_debug'));
    }

    public function testSetConfig()
    {
        $config = new Config();
        $config->initialize([
            'my_env' => 'my_env',
        ]);
        $this->assertEquals('my_env', $config->get('my_env'));
    }

    public function testGetConfigArray()
    {
        $config = new Config();
        $config->initialize();
        $this->assertTrue(is_array($config->getArray()));
    }
}
