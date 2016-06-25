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
 * Config Class
 *
 * This class contains functions that enable config files to be managed
 *
 * @package     Kotori
 * @subpackage  Config
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Kotori\Debug\Hook;

class Config
{
    /**
     * Config Array
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Default Config Array
     *
     * @var array
     */
    protected $_defaults = array(
        'APP_DEBUG' => true,
        'APP_PATH' => './app/',
        'DB_PORT' => 3306,
        'DB_CHARSET' => 'utf8',
        'URL_MODE' => 'QUERY_STRING',
        'TIME_ZONE' => 'Asia/Shanghai',
    );

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
     * Initialize Config.
     *
     * @return void
     */
    public function __construct()
    {
        Hook::listen('Config');
    }

    /**
     * Initialize Config
     *
     * @param $config Config Array
     * @return boolean
     */
    public function initialize($config = array())
    {
        $this->_config = $config;
        if (is_array($this->_config)) {
            if (array_keys($this->_config) !== range(0, count($this->_config) - 1)) {
                $this->_config = array_merge($this->_defaults, $this->_config);
                $this->_config = array_merge(array('APP_FULL_PATH' => realpath(realpath('.') . '/' . rtrim($this->APP_PATH, '/'))), $this->_config);
            }
        }
        return false;
    }

    /**
     * __set magic
     *
     * Set the specified config item
     *
     * @param string $key Config item name
     * @param mixed $value Config item value
     * @return void
     */
    public function __set($key, $value)
    {
        if (is_string($key)) {
            $this->_config[$key] = $value;
        } else {
            Handle::halt('Config Error.');
        }
    }

    /**
     * __get magic
     *
     * Returns the specified config item
     *
     * @param string $key Config item name
     * @return mixed
     */
    public function __get($key)
    {
        if (is_string($key)) {
            return isset($this->_config[$key]) ? $this->_config[$key] : null;
        }
        return null;
    }

    /**
     * Return the config array
     *
     * @return array
     */
    public function getArray()
    {
        return $this->_config;
    }
}
