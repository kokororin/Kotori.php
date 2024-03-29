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
 * ControllerMethods Trait
 *
 * @package     Kotori
 * @subpackage  Traits
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Traits;

use Kotori\Core\Container;
use Kotori\Exception\NotFoundException;

trait ControllerMethodsTrait
{
    /**
     * __get magic
     *
     * Allows models and views to access loaded classes using the same
     * syntax as controllers.
     *
     * @param string $key
     *
     * @throws \Kotori\Exception\NotFoundException
     */
    public function __get($key)
    {
        $controller = Container::get('controller');
        if (property_exists($controller, $key)) {
            return $controller->$key;
        }

        $backTrace = debug_backtrace();
        $className = get_class($backTrace[0]['object']);
        throw new NotFoundException($className . '::$' . $key . ' is not defined');
    }

    /**
     * __call magic
     *
     * Allows models and views to access controller methods
     *
     * @param  string $name
     * @param  array  $arguments
     *
     * @throws \Kotori\Exception\NotFoundException
     */
    public function __call($name, $arguments)
    {
        $controller = Container::get('controller');
        $callback = [$controller, $name];
        if (!is_callable($callback)) {
            $backTrace = debug_backtrace();
            $className = get_class($backTrace[0]['object']);
            throw new NotFoundException($className . '::' . $name . '() is not callable');
        }

        return call_user_func_array($callback, $arguments);
    }
}
