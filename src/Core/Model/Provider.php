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
 * Model Provider CLass
 *
 * @package     Kotori
 * @subpackage  Model
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core\Model;

use Kotori\Core\Container;
use Kotori\Debug\Hook;
use Kotori\Exception\NotFoundException;

class Provider
{
    /**
     * Initialized Models
     *
     * @var array
     */
    protected $models = [];

    /**
     * Class constructor
     *
     * Initialize Model Provider.
     */
    public function __construct()
    {
        Hook::listen(__CLASS__);
    }

    /**
     * __get magic
     *
     * Allows controllers to access model
     *
     * @param   string $key
     * @return  \Kotori\Core\Model
     *
     * @throws \Kotori\Exception\NotFoundException
     */
    public function __get($key)
    {
        if (isset($this->models[$key])) {
            return $this->models[$key];
        }

        $modelClassName = Container::get('config')->get('namespace_prefix') . 'models\\' . $key;

        if (!class_exists($modelClassName)) {
            throw new NotFoundException('Request Model ' . $key . ' is not Found');
        } else {
            $model = new $modelClassName();
            $this->models[$key] = $model;
            return $model;
        }
    }
}
