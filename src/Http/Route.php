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
 * Route class
 *
 * Parses URIs and determines routing
 *
 * @package     Kotori
 * @subpackage  Http
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Http;

use Kotori\Core\Container;
use Kotori\Core\Helper;
use Kotori\Debug\Hook;
use Kotori\Exception\ConfigException;
use Kotori\Exception\NotFoundException;
use Kotori\Exception\RouteNotFoundException;

class Route
{
    /**
     * Controllers Array
     *
     * @var array
     */
    protected $controllers = [];

    /**
     * Current controller
     *
     * @var string
     */
    protected $controller;

    /**
     * Current action
     *
     * @var string
     */
    protected $action;

    /**
     * Current URI string
     *
     * @var mixed
     */
    protected $uri = '';

    /**
     * Parsed URI Array
     *
     * @var array
     */
    protected $uris = [];

    /**
     * Parsed params
     *
     * @var array
     */
    protected $params = [];

    /**
     * Class constructor
     *
     * Initialize route class.
     *
     * @return void
     */
    public function __construct()
    {
        if (Container::get('request')->isCli()) {
            $this->uri = $this->parseArgv();
        } else {
            if (isset($_GET['_i'])) {
                $_SERVER['PATH_INFO'] = $_GET['_i'];
            }

            $_SERVER['PATH_INFO'] = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO']
            : (isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO']
                : (isset($_SERVER['REDIRECT_PATH_INFO']) ? $_SERVER['REDIRECT_PATH_INFO'] : ''));

            $this->uri = $_SERVER['PATH_INFO'];
        }

        if (substr($this->uri, 0, 1) == '/') {
            $this->uri = ltrim($this->uri, '/');
        }

        if (trim($this->uri, '/') == '') {
            $this->uri = '/';
        }

        Hook::listen(__CLASS__);
    }

    /**
     * Map URL to controller and action
     *
     * @return void
     *
     * @throws \Kotori\Exception\RouteNotFoundException
     * @throws \Kotori\Exception\NotFoundException
     */
    public function dispatch()
    {
        if (strtolower(Container::get('config')->get('url_mode')) == 'query_string') {
            $this->uri = explode('?', $this->uri, 2);
            $_SERVER['QUERY_STRING'] = isset($this->uri[1]) ? $this->uri[1] : '';
            $this->uri = $this->uri[0];
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        }

        if ($this->uri == 'favicon.ico') {
            return Container::get('response')->setStatus(404);
        }

        Container::get('middleware')->register('before_route');
        $parsedRoute = $this->parseRoutes($this->uri);
        Container::get('middleware')->register('after_route');

        if ($parsedRoute) {
            $this->uri = $parsedRoute;
        } else {
            if (Container::get('request')->isOptions()) {
                Container::get('response')->setStatus(204);
                exit;
            }

            throw new RouteNotFoundException('Request URI ' . $this->uri . ' is not Matched by any route.');
        }

        $this->uris = ($this->uri != '') ? explode('/', trim($this->uri, '/')) : [];

        // Clean uris
        foreach ($this->uris as $key => $value) {
            if ($value == '') {
                unset($this->uris[$key]);
            }
        }

        $this->uris = array_merge($this->uris);

        $this->controller = $this->getController();
        $this->action = $this->getAction();

        // If is already initialized
        $prefix = Container::get('config')->get('namespace_prefix');

        $controllerClassName = $prefix . 'controllers\\' . $this->controller;

        Container::get('middleware')->register('before_controller');

        if (isset($this->controllers[$this->controller])) {
            $class = $this->controllers[$this->controller];
        } else {
            $class = new $controllerClassName();
            $this->controllers[$this->controller] = $class;
        }

        Container::get('middleware')->register('after_controller');

        if (!class_exists($controllerClassName)) {
            throw new NotFoundException('Request Controller ' . $this->controller . ' is not Found.');
        }

        if (!method_exists($class, $this->action)) {
            throw new NotFoundException('Request Action ' . $this->action . ' is not Found.');
        }

        $callback = [$class, $this->action];
        if (!is_callable($callback)) {
            throw new NotFoundException($controllerClassName . '::' . $this->action . '() is not callable');
        }

        // Parse params from uri
        $this->params = $this->getParams();

        // Do some final cleaning of the params
        $_GET = array_merge($this->params, $_GET);
        $_REQUEST = array_merge($_POST, $_GET, $_COOKIE);

        if (Container::get('config')->get('app_debug')) {
            Container::get('response')->setHeader('X-Kotori-Hash', call_user_func(function () {
                $lockFile = Helper::getComposerVendorPath() . '/../composer.lock';
                if (!Helper::isFile($lockFile)) {
                    return 'unknown';
                } else {
                    $lockData = file_get_contents($lockFile);
                    $lockData = json_decode($lockData, true);
                    foreach ($lockData['packages'] as $package) {
                        if ($package['name'] == 'kokororin/kotori-php') {
                            return substr($package['source']['reference'], 0, 6);
                        }
                    }
                }

                return 'unknown';
            }));
        }

        Container::get('middleware')->register('before_action');
        // Call the requested method
        call_user_func_array($callback, $this->params);
        Container::get('middleware')->register('after_action');
    }

    /**
     * Returns the controller name
     *
     * @return      string
     *
     * @throws \Kotori\Exception\NotFoundException
     */
    public function getController()
    {
        if (isset($this->uris[0]) && '' !== $this->uris[0]) {
            $_controller = $this->uris[0];
        } else {
            throw new NotFoundException('Cannot dispatch controller name.');
        }

        return strip_tags($_controller);
    }

    /**
     * Returns the action name
     *
     * @return      string
     *
     * @throws \Kotori\Exception\NotFoundException
     */
    public function getAction()
    {
        if (isset($this->uris[1])) {
            $_action = $this->uris[1];
        } else {
            throw new NotFoundException('Cannot dispatch action name.');
        }

        return strip_tags($_action);
    }

    /**
     * Returns the request params
     *
     * @return array
     */
    public function getParams()
    {
        $params = $this->uris;
        unset($params[0], $params[1]);
        return array_merge($params);
    }

    /**
     * Returns the URI
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Parse Routes
     *
     * Matches any routes that may exist in URL_ROUTE array
     * against the URI to determine if the class/method need to be remapped.
     *
     * @param  string $uri
     * @return string
     */
    protected function parseRoutes($uri)
    {
        $routes = Container::get('config')->get('url_route');

        $hostName = Container::get('request')->getHostName();

        if (isset($routes[$hostName])) {
            $routes = $routes[$hostName];
        }

        // Get HTTP verb
        $http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

        if (null != $routes) {
            foreach ($routes as $key => $val) {
                // Check if route format is using HTTP verbs
                if (is_array($val)) {
                    $val = array_change_key_case($val, CASE_LOWER);
                    if (isset($val[$http_verb])) {
                        $val = $val[$http_verb];
                    } else {
                        continue;
                    }
                }

                // Does the RegEx match?
                if (preg_match('#^' . $key . '$#', $uri, $matches)) {
                    // Are we using callbacks to process back-references?
                    if (!is_string($val) && is_callable($val)) {
                        // Remove the original string from the matches array.
                        array_shift($matches);

                        // Execute the callback using the values in matches as its parameters.
                        $val = call_user_func_array($val, $matches);
                    }
                    // Are we using the default routing method for back-references?
                    elseif (strpos($val, '$') !== false && strpos($key, '(') !== false) {
                        $val = preg_replace('#^' . $key . '$#', $val, $uri);
                    }

                    return $val;
                }
            }
        }

    }

    /**
     * Parse CLI arguments
     *
     * Take each command line argument and assume it is a URI segment.
     *
     * @return  string
     */
    protected function parseArgv()
    {
        $args = array_slice($_SERVER['argv'], 1);
        return $args ? implode('/', $args) : '';
    }

    /**
     * Build Full URL
     *
     * @param  string $uri
     * @param  string $module
     * @return string
     *
     * @throws \Kotori\Exception\ConfigException
     */
    public function url($uri = '', $module = null)
    {
        if ($module != null) {
            $appNames = Container::get('config')->get('app_name');
            if (is_array($appNames)) {
                foreach ($appNames as &$appName) {
                    $appName = str_replace('./', '', $appName);
                }

                $appNames = array_flip($appNames);
                $baseUrl = $appNames[$module];
                $baseUrl = '//' . $baseUrl . '/';
            }
        } else {
            $baseUrl = Container::get('request')->getBaseUrl();
        }

        $uri = is_array($uri) ? implode('/', $uri) : trim($uri, '/');
        $prefix = $baseUrl . 'index.php?_i=';

        switch (strtolower(Container::get('config')->get('url_mode'))) {
            case 'path_info':
                return $uri == '' ? rtrim($baseUrl, '/') : $baseUrl . $uri;
            case 'query_string':
                return $uri == '' ? rtrim($baseUrl, '/') : $prefix . $uri;
            default:
                throw new ConfigException('`url_mode` Config ERROR');
        }

    }

}
