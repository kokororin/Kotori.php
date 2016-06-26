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
 * Model Provider CLass
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Kotori\Debug\Hook;

class ModelProvider
{
    /**
     * Initialized Models
     *
     * @var array
     */
    protected $_models = array();

    /**
     * Disable Clone
     *
     * @return boolean
     */
    public function __clone()
    {
        return false;
    }

    /**
     * Instance Handle
     *
     * @var array
     */
    protected static $_soul;

    /**
     * get singleton
     *
     * @return object
     */
    public static function getSoul()
    {
        if (self::$_soul === null) {
            self::$_soul = new self();
        }
        return self::$_soul;
    }

    /**
     * Class constructor
     *
     * Initialize Model Provider.
     *
     * @return void
     */
    public function __construct()
    {
        Hook::listen('Model_Provider');
    }

    /**
     * __get magic
     *
     * Allows controllers to access model
     *
     * @param string $key
     */
    public function __get($key)
    {
        $key .= '_model';
        if (isset($this->_models[$key])) {
            return $this->_models[$key];
        }

        Common::import(Config::getSoul()->APP_FULL_PATH . '/models/' . $key . '.php');

        if (!class_exists($key)) {
            throw new \Exception('Request Model ' . $key . ' is not Found');
        } else {
            $model = new $key();
            $this->_models[$key] = $model;
            return $model;
        }
    }
}
