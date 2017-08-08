<?php
/**
 * Kotori.php
 *
 * A Tiny Model-View-Controller PHP Framework
 *
 * This content is released under the Apache 2 License
 *
 * Copyright (c) 2015-2017 Kotori Technology. All rights reserved.
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
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Kotori\Debug\Hook;
use Kotori\Exception\ConfigException;

class Config
{
    /**
     * Config Array
     *
     * @var array
     */
    protected $config = [];

    /**
     * Default Config Array
     *
     * @var array
     */
    protected $defaults = [
        'APP_DEBUG' => true,
        'APP_NAME' => 'app',
        'URL_MODE' => 'QUERY_STRING',
        'TIME_ZONE' => 'Asia/Shanghai',
        'USE_SESSION' => true,
        'ENV' => 'normal',
    ];

    /**
     * Class constructor
     *
     * Initialize Config.
     *
     * @return void
     */
    public function __construct()
    {
        Hook::listen(__CLASS__);
    }

    /**
     * Initialize Config
     *
     * @param  array   $config
     * @return boolean
     *
     * @throws \Kotori\Exception\ConfigException
     */
    public function initialize($config = [])
    {
        $this->config = $config;
        if (is_array($this->config)) {
            if (array_keys($this->config) !== range(0, count($this->config) - 1)) {
                if (isset($this->config['DB']) && is_array($this->config['DB'])) {
                    foreach ($this->config['DB'] as $key => &$value) {
                        if (!isset($value['PORT'])) {
                            $value['PORT'] = 3306;
                        }

                        if (!isset($value['CHARSET'])) {
                            $value['CHARSET'] = 'utf8';
                        }
                    }
                }

                $this->config = array_merge($this->defaults, $this->config);
                if (is_array($this->get('APP_NAME'))) {
                    $hostName = Container::get('request')->getHostName();
                    if (array_key_exists($hostName, $this->get('APP_NAME'))) {
                        $appName = $this->get('APP_NAME')[$hostName];
                    } else {
                        throw new ConfigException('Cannot find any app paths.');
                    }
                } else {
                    $appName = $this->get('APP_NAME');
                }

                if (Container::get('request')->isCli()) {
                    $stack = debug_backtrace();
                    $firstFrame = $stack[count($stack) - 1];
                    $initialFile = $firstFrame['file'];
                    $appFullPath = realpath(dirname($initialFile) . '/../' . rtrim($appName, '/'));
                } else {
                    $appFullPath = realpath(realpath('.') . '/../' . rtrim($appName, '/'));
                }

                if (!$appFullPath && $this->get('ENV') == 'normal') {
                    throw new ConfigException('Cannot find your app directory (' . $appName . ').');
                }

                $this->config = array_merge([
                    'APP_FULL_PATH' => $appFullPath,
                ], $this->config);
                $this->set('NAMESPACE_PREFIX', basename($this->get('APP_FULL_PATH')) . '\\');
            }
        }

        return false;
    }

    /**
     * Set the specified config item
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     *
     * @throws \Kotori\Exception\ConfigException
     */
    public function set($key, $value)
    {
        if (is_string($key)) {
            $this->config[$key] = $value;
        } else {
            throw new ConfigException('Config Error.');
        }
    }

    /**
     * Returns the specified config item
     *
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        if (is_string($key)) {
            return isset($this->config[$key]) ? $this->config[$key] : null;
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
        return $this->config;
    }
}
