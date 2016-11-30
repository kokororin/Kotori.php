<?php
/**
 * Kotori.php
 *
 * A Tiny Model-View-Controller PHP Framework
 *
 * This content is released under the Apache 2 License
 *
 * Copyright (c) 2015-2016 Kotori Technology. All rights reserved.
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
 * Model Class
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Kotori\Debug\Hook;

class Model
{
    /**
     * Class constructor
     *
     * Initialize Model.
     *
     * @return void
     */
    public function __construct()
    {
        Hook::listen(__CLASS__);
    }

    /**
     * __get magic
     *
     * Allows models to access loaded classes using the same
     * syntax as controllers.
     *
     * @param string $key
     */
    public function __get($key)
    {
        return Controller::getSoul()->$key;
    }

    /**
     * __call magic
     *
     * Allows model to access controller methods
     * 
     * @param  $name
     * @param  $arguments
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array(Controller::getSoul(), $name), $arguments);
    }
}
