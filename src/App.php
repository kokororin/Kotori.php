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
 * Kotori Initialization Class
 *
 * Loads the base classes and executes the request.
 *
 * @package     Kotori
 * @subpackage  Kotori
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori;

use Kotori\Core\Container;
use Kotori\Core\Helper;
use Kotori\Core\Middleware;

class App
{
    /**
     * Config Array
     *
     * @var array
     */
    protected $config = [];

    /**
     * Class constructor
     *
     * Initialize Framework.
     *
     * @param  array $config
     */
    public function __construct($config = [])
    {
        if (version_compare(PHP_VERSION, '7.3.0', '<')) {
            exit('Kotori.php requires PHP >= 7.3.0 !');
        }

        ini_set('display_errors', 'off');
        define('KOTORI_START_TIME', microtime(true));
        define('KOTORI_START_MEMORY', memory_get_usage());
        if (!empty($config)) {
            $this->config = $config;
        }
    }

    /**
     * Start the App.
     *
     * @return void
     */
    public function run()
    {
        // Define a custom error handler so we can log PHP errors
        set_error_handler([\Kotori\Core\Handle::class, 'error']);
        set_exception_handler([\Kotori\Core\Handle::class, 'exception']);
        register_shutdown_function([\Kotori\Core\Handle::class, 'end']);

        Container::get('config')->initialize($this->config);
        Middleware::register('before_app');

        ini_set('date.timezone', Container::get('config')->get('time_zone'));

        // Load application's common functions
        Helper::import(Container::get('config')->get('app_full_path') . '/common.php');

        // @codingStandardsIgnoreStart
        if (function_exists('spl_autoload_register')) {
            spl_autoload_register(['\\Kotori\\Core\\Helper', 'autoload']);
        } else {
            function __autoload($className)
            {
                Helper::autoload($className);
            }
        }
        // @codingStandardsIgnoreEnd

        // Init session
        Container::get('request')->sessionInit();

        Middleware::register('after_app');
        // Load route class
        Container::get('route')->dispatch();
    }
}
