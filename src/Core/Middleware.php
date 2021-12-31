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
 * Middleware Class
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Kotori\Exception\ConfigException;
use Kotori\Exception\NotFoundException;

class Middleware
{
    /**
     * Registered middlewares
     *
     * @var array
     */
    protected static $middlewares = [];

    /**
     * Register a middleware
     *
     * @param  string $middleware
     * @return void
     */
    public static function register($middleware)
    {
        $config = Container::get('config')->get('middleware');

        if (isset($config[$middleware])) {
            if (in_array($middleware, array_values(self::$middlewares))) {
                throw new ConfigException('middleware has already registered');
            }

            array_push(self::$middlewares, $middleware);
            $middlewares = $config[$middleware];

            if (!is_array($middlewares)) {
                throw new ConfigException('middleware config must be an array');
            }

            foreach ($middlewares as $className) {
                if (!class_exists($className)) {
                    throw new NotFoundException('middleware class ' . $className . ' is not found');
                }

                $class = new $className();
                if (!method_exists($class, 'handle')) {
                    throw new NotFoundException('middleware class should implement a handle() method');
                }

                $class->handle(Container::get('request'), Container::get('response'));
            }
        }
    }
}
