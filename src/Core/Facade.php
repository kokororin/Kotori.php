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

/**
 * Facade Class
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

abstract class Facade
{
    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string $method
     * @param  array  $params
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        return call_user_func_array([Container::get(static::getFacadeAccessor()), $method], $params);
    }
}
