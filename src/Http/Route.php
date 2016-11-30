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

use Kotori\Core\Common;
use Kotori\Core\Config;
use Kotori\Debug\Hook;

class Route
{
    /**
     * Controllers Array
     *
     * @var array
     */
    protected $_controllers = array();

    /**
     * Current controller
     *
     * @var string
     */
    protected $_controller;

    /**
     * Current action
     *
     * @var string
     */
    protected $_action;

    /**
     * Current URI string
     *
     * @var string
     */
    protected $_uri = '';

    /**
     * Parsed URI Array
     *
     * @var array
     */
    protected $_uris = array();

    /**
     * Parsed params
     *
     * @var array
     */
    protected $_params = array();

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
     * Initialize route class.
     *
     * @return void
     */
    public function __construct()
    {
        if (Request::getSoul()->isCli()) {
            $this->_uri = $this->parseArgv();
        } else {
            if (isset($_GET['_i'])) {
                $_SERVER['PATH_INFO'] = $_GET['_i'];
            }
            $_SERVER['PATH_INFO'] = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO']
            : (isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO']
                : (isset($_SERVER['REDIRECT_PATH_INFO']) ? $_SERVER['REDIRECT_PATH_INFO'] : ''));

            $this->_uri = $_SERVER['PATH_INFO'];
        }

        if (substr($this->_uri, 0, 1) == '/') {
            $this->_uri = ltrim($this->_uri, '/');
        }
        if (trim($this->_uri, '/') == '') {
            $this->_uri = '/';
        }
        Hook::listen(__CLASS__);
    }

    /**
     * Map URL to controller and action
     *
     * @return void
     */
    public function dispatch()
    {
        if (Config::getSoul()->URL_MODE == 'QUERY_STRING') {
            $this->_uri = explode('?', $this->_uri, 2);
            $_SERVER['QUERY_STRING'] = isset($this->_uri[1]) ? $this->_uri[1] : '';
            $this->_uri = $this->_uri[0];
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        }

        define('URI', $this->_uri);

        switch ($this->_uri) {
            case 'favicon.ico':
                Response::getSoul()->setHeader('Content-Type', 'image/x-icon');
                Response::getSoul()->setCacheHeader();
                echo base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', Common::logo()));
                exit;
            case 'kotori-php-system-route/highlight-github.css':
                Response::getSoul()->setHeader('Content-Type', 'text/css; charset=utf-8');
                Response::getSoul()->setCacheHeader();
                echo file_get_contents(Common::getComposerVendorPath() . '/components/highlightjs/styles/github.css');
                exit;
            case 'kotori-php-system-route/highlight.js':
                Response::getSoul()->setHeader('Content-Type', 'text/javascript; charset=utf-8');
                Response::getSoul()->setCacheHeader();
                echo file_get_contents(Common::getComposerVendorPath() . '/components/highlightjs/highlight.pack.min.js');
                exit;
        }

        $parsedRoute = $this->parseRoutes($this->_uri);

        if ($parsedRoute) {
            $this->_uri = $parsedRoute;
        } else {
            throw new \Exception('Request URI ' . $this->_uri . ' is not Matched by any route.');
        }

        $this->_uris = ($this->_uri != '') ? explode('/', trim($this->_uri, '/')) : array();

        //Clean uris
        foreach ($this->_uris as $key => $value) {
            if ($value == '') {
                unset($this->_uris[$key]);
            }
        }
        $this->_uris = array_merge($this->_uris);

        $this->_controller = $this->getController();
        $this->_action = $this->getAction();
        //Define some variables
        define('CONTROLLER_NAME', $this->_controller);
        define('ACTION_NAME', $this->_action);
        define('PUBLIC_DIR', Request::getSoul()->getBaseUrl() . 'public');

        //If is already initialized
        $prefix = Config::getSoul()->NAMESPACE_PREFIX;

        $controllerClassName = $prefix . 'controllers\\' . $this->_controller;
        if (isset($this->_controllers[$this->_controller])) {
            $class = $this->_controllers[$this->_controller];
        } else {
            $class = new $controllerClassName();
            $this->_controllers[$this->_controller] = $class;
        }

        if (!class_exists($controllerClassName)) {
            throw new \Exception('Request Controller ' . $this->_controller . ' is not Found.');
        }

        if (!method_exists($class, $this->_action)) {
            throw new \Exception('Request Action ' . $this->_action . ' is not Found.');
        }
        //Parse params from uri
        $this->_params = $this->getParams();

        //Do some final cleaning of the params
        $_GET = array_merge($this->_params, $_GET);
        $_REQUEST = array_merge($_POST, $_GET, $_COOKIE);

        Response::getSoul()->setHeader('X-Powered-By', 'Kotori');
        Response::getSoul()->setHeader('Cache-control', 'private');

        //Call the requested method
        call_user_func_array(array($class, $this->_action), $this->_params);
    }

    /**
     * Returns the controller name
     *
     * @deprecated since v20160718-1444
     * @return string
     */
    protected function getController()
    {
        if (isset($this->_uris[0]) && '' !== $this->_uris[0]) {
            $_controller = $this->_uris[0];
        } else {
            throw new \Exception('Cannot dispatch controller name.');
        }
        return strip_tags($_controller);
    }

    /**
     * Returns the action name
     *
     * @deprecated since v20160718-1444
     * @return string
     */
    protected function getAction()
    {
        if (isset($this->_uris[1])) {
            $_action = $this->_uris[1];
        } else {
            throw new \Exception('Cannot dispatch action name.');
        }
        return strip_tags($_action);
    }

    /**
     * Returns the request params
     *
     * @return array
     */
    protected function getParams()
    {
        $params = $this->_uris;
        unset($params[0], $params[1]);
        return array_merge($params);
    }

    /**
     * Parse Routes
     *
     * Matches any routes that may exist in URL_ROUTE array
     * against the URI to determine if the class/method need to be remapped.
     *
     * @param string $uri URI
     *
     * @return string
     */
    protected function parseRoutes($uri)
    {
        $routes = Config::getSoul()->URL_ROUTE;

        $hostName = Request::getSoul()->getHostName();

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
     * @param string $uri URI
     * @param string $module module name
     * @return string
     */
    public function url($uri = '', $module = null)
    {
        if ($module != null) {
            $appPaths = Config::getSoul()->APP_PATH;
            if (is_array($appPaths)) {
                foreach ($appPaths as &$appPath) {
                    $appPath = str_replace('./', '', $appPath);
                }
                $appPaths = array_flip($appPaths);
                $baseUrl = $appPaths[$module];
                $baseUrl = '//' . $baseUrl . '/';
            }
        } else {
            $baseUrl = Request::getSoul()->getBaseUrl();
        }

        $uri = is_array($uri) ? implode('/', $uri) : trim($uri, '/');
        $prefix = $baseUrl . 'index.php?_i=';

        switch (Config::getSoul()->URL_MODE) {
            case 'PATH_INFO':
                return $uri == '' ? rtrim($baseUrl, '/') : $baseUrl . $uri;
                break;
            case 'QUERY_STRING':
                return $uri == '' ? rtrim($baseUrl, '/') : $prefix . $uri;
                break;
            default:
                throw new \Exception('URL_MODE Config ERROR');
        }

    }

}
