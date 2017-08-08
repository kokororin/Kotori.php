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
     * @return void
     */
    public function __construct($config = [])
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            exit('Kotori.php requires PHP >= 5.5.0 !');
        }

        ini_set('display_errors', 'off');
        define('KOTORI_START_TIME', microtime(true));
        define('KOTORI_START_MEMORY', memory_get_usage());
        if (!empty($config)) {
            $this->config = $config;
        }

        Container::getInstance()->bind([
            'cache' => \Kotori\Core\Cache::class,
            'config' => \Kotori\Core\Config::class,
            'controller' => \Kotori\Core\Controller::class,
            'request' => \Kotori\Http\Request::class,
            'response' => \Kotori\Http\Response::class,
            'route' => \Kotori\Http\Route::class,
            'trace' => \Kotori\Debug\Trace::class,
            'model/provider' => \Kotori\Core\Model\Provider::class,
        ]);
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

        ini_set('date.timezone', Container::get('config')->get('TIME_ZONE'));

        if (Container::get('config')->get('USE_SESSION')) {
            !session_id() && session_start();
        }

        // Load application's common functions
        Helper::import(Container::get('config')->get('APP_FULL_PATH') . '/common.php');

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

        // Load route class
        Container::get('route')->dispatch();
    }

}
