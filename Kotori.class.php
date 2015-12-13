<?php
/**
 * Kotori Framework
 *
 * A Tiny Model-View-Controller PHP Framework
 *
 * This content is released under the Apache 2 License
 *
 * Copyright (c) 2015 Kotori Technology. All rights reserved.
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
class Kotori
{
	/**
	 * biu~ biu~ biu~ Run!!
	 * @param mixed $conf Config array
	 * @return void
	 */
	public static function run($conf)
	{
		ini_set('display_errors', 'off');
		ini_set('date.timezone', 'Asia/Shanghai');
		Kotori_Config::getInstance()->initialize($conf);
		Kotori::initialize();
	}

	/**
	 * Instantiate the Framework
	 * @return void
	 */
	private static function initialize()
	{
		define('START_TIME', microtime(true));
		//Define a custom error handler so we can log PHP errors
		set_error_handler(array('Kotori_Handle', 'error'));
		set_exception_handler(array('Kotori_Handle', 'exception'));
		register_shutdown_function(array('Kotori_Handle', 'end'));

		if (Kotori_Config::getInstance()->get('USE_SESSION') == true)
		{
			session_start();
		}

		//Load application's common functions
		Kotori_Common::import(Kotori_Config::getInstance()->get('APP_FULL_PATH') . '/common.php');

		if (function_exists('spl_autoload_register'))
		{
			spl_autoload_register(array(__CLASS__, 'autoload'));
		}
		else
		{
			function __autoload($className)
			{
				Kotori::autoload($className);
			}
		}

		//Load route class
		Kotori_Route::getInstance()->dispatch();

		//Global security filter
		array_walk_recursive($_GET, array('Kotori_Request', 'filter'));
		array_walk_recursive($_POST, array('Kotori_Request', 'filter'));
		array_walk_recursive($_REQUEST, array('Kotori_Request', 'filter'));

	}

	/**
	 * Global autoload function
	 * @param string $class Class name
	 * @return void
	 */
	public static function autoload($class)
	{
		$baseRoot = Kotori_Config::getInstance()->get('APP_FULL_PATH');
		if (substr($class, -10) == 'Controller')
		{
			Kotori_Common::import($baseRoot . '/Controller/' . $class . '.class.php');
		}
		elseif (substr($class, -5) == 'Model')
		{
			Kotori_Common::import($baseRoot . '/Model/' . $class . '.class.php');
		}
		else
		{
			Kotori_Common::import($baseRoot . '/Lib/' . $class . '.class.php');
		}
	}
}

/**
 * Common Class
 *
 * Common APIs.
 *
 * @package     Kotori
 * @subpackage  Common
 * @author      Kokororin
 * @link        https://kotori.love
 */
class Kotori_Common
{
	/**
	 * Require Array
	 * @var array
	 */
	private static $_require = array();

	/**
	 * Include One File
	 *
	 * @param string $path File Path
	 * @return boolean
	 */
	public static function import($path)
	{
		if (!isset(self::$_require[$path]))
		{
			if (self::isFile($path))
			{
				require $path;
				self::$_require[$path] = true;
			}
			else
			{
				self::$_require[$path] = false;
			}
		}
		return self::$_require[$path];

	}

	/**
	 * Detect whether file is existed
	 *
	 * @param string $path File Path
	 * @return boolean
	 */
	public static function isFile($path)
	{
		if (is_file($path))
		{
			if (strstr(PHP_OS, 'WIN'))
			{
				if (basename(realpath($path)) != basename($path))
				{
					return false;
				}
			}
			return true;
		}
		return false;
	}

}

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
class Kotori_Config
{

	/**
	 * Instance Handle
	 *
	 * @var object
	 */
	private static $_instance = null;

	/**
	 * Config Array
	 *
	 * @var array
	 */
	private $_config = array();

	/**
	 * Default Config Array
	 *
	 * @var array
	 */
	private $_defaults = array(
		'APP_DEBUG'   => 'false',
		'APP_PATH'    => './App',
		'DB_PORT'     => 3306,
		'DB_CHARSET'  => 'utf8',
		'USE_SESSION' => true,
		'URL_MODE'    => 'QUERY_STRING',
	);

	/**
	 * get singleton
	 * @return object
	 */
	public static function getInstance()
	{
		if (!(self::$_instance instanceof self))
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	/**
	 * Initialize Config
	 * @param mixed $conf Config
	 * @return void
	 */
	public function initialize($conf)
	{
		if (is_array($conf))
		{
			if (array_keys($conf) !== range(0, count($conf) - 1))
			{
				$this->_config = array_merge($this->_config, $conf);
				$this->_config = array_merge($this->_defaults, $this->_config);
				$this->_config = array_merge(array('APP_FULL_PATH' => dirname(__FILE__) . '/' . $this->get('APP_PATH')), $this->_config);
			}
		}
		return false;
	}

	/**
	 * Set the specified config item
	 *
	 * @param string $key Config item name
	 * @param mixed $value Config item value
	 * @return void
	 */
	public function set($key, $value)
	{
		if (is_string($key))
		{
			$this->_config[$key] = $value;
		}
		else
		{
			Kotori_Handle::halt('Config Error.');
		}
	}

	/**
	 * Returns the specified config item
	 *
	 * @param string $key Config item name
	 * @return mixed
	 */
	public function get($key)
	{
		if (is_string($key))
		{
			return isset($this->_config[$key]) ? $this->_config[$key] : null;
		}
		return null;
	}
}

/**
 * Handle Class
 *
 * @package     Kotori
 * @subpackage  Handle
 * @author      Kokororin
 * @link        https://kotori.love
 */
class Kotori_Handle
{
	/**
	 * Error Array
	 *
	 * @var array
	 */
	public static $errors = array();

	/**
	 * General Error Page
	 *
	 * Takes an error message as input
	 * and displays it using the specified template.
	 *
	 * @param string $message Error Message
	 * @param int $code HTTP Header code
	 *
	 * @return void
	 */
	public static function halt($message, $code = 500)
	{
		Kotori_Response::getInstance()->setStatus($code);
		if (Kotori_Config::getInstance()->get('APP_DEBUG') == false)
		{
			$message = '404 Not Found.';
		}
		echo
			<<<EOF
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN" prefix="og: http://ogp.me/ns#">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
<title>Error Occured.</title>
<style type="text/css">
html {
    background: #f1f1f1;
}
body {
    background: #fff;
    color: #444;
    font-family: "Open Sans", sans-serif;
    margin: 2em auto;
    padding: 1em 2em;
    max-width: 700px;
    -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
    box-shadow: 0 1px 3px rgba(0,0,0,0.13);
}
h1 {
    border-bottom: 1px solid #dadada;
    clear: both;
    color: #666;
    font: 24px "Open Sans", sans-serif;
    margin: 30px 0 0 0;
    padding: 0;
    padding-bottom: 7px;
}
#error-page {
    margin-top: 50px;
}
#error-page p {
    font-size: 14px;
    line-height: 1.5;
    margin: 25px 0 20px;
}
#error-page code {
    font-family: Consolas, Monaco, monospace;
}
ul li {
    margin-bottom: 10px;
    font-size: 14px ;
}
a {
    color: #21759B;
    text-decoration: none;
}
a:hover {
    color: #D54E21;
}
.button {
    background: #f7f7f7;
    border: 1px solid #cccccc;
    color: #555;
    display: inline-block;
    text-decoration: none;
    font-size: 13px;
    line-height: 26px;
    height: 28px;
    margin: 0;
    padding: 0 10px 1px;
    cursor: pointer;
    -webkit-border-radius: 3px;
    -webkit-appearance: none;
    border-radius: 3px;
    white-space: nowrap;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0,0,0,.08);
    box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0,0,0,.08);
    vertical-align: top;
}
.button.button-large {
    height: 29px;
    line-height: 28px;
    padding: 0 12px;
}
.button:hover, .button:focus {
    background: #fafafa;
    border-color: #999;
    color: #222;
}
.button:focus {
    -webkit-box-shadow: 1px 1px 1px rgba(0,0,0,.2);
    box-shadow: 1px 1px 1px rgba(0,0,0,.2);
}
.button:active {
    background: #eee;
    border-color: #999;
    color: #333;
    -webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
    box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
}
</style>
<script>
function open_link(url){
 var el = document.createElement('a');
 document.body.appendChild(el);
 el.href = url;
 el.target = '_blank';
 el.click();
 document.body.removeChild(el);
}
</script>
</head>

<body id="error-page">
    <h1>Error Occured.</h1>
    <p>{$message}</p>
    <button class="button" onclick="open_link('https://github.com/kokororin/KotoriFramework')">Go to GitHub Page</button>
    <button class="button" onclick="open_link('https://github.com/kokororin/KotoriFramework/issues')">Report a Bug</button>
</body>
</html>
EOF;
		exit;
	}

	/**
	 * Error Handler
	 *
	 * This function lets us invoke the exception class and
	 * display errors using the standard error template located
	 * in App/View/Public/error.html
	 * This function will send the error page directly to the
	 * browser and exit.
	 *
	 * @param string $errno Error number
	 * @param int $errstr Error string
	 * @param string $errfile Error filepath
	 * @param int $errline Error line
	 * @return void
	 */
	public static function error($errno, $errstr, $errfile, $errline)
	{
		switch ($errno)
		{
		case E_ERROR:
			$errtype = 'Error';
			break;
		case E_WARNING:
			$errtype = 'Warning';
			break;
		case E_PARSE:
			$errtype = 'Parsing Error';
			break;
		case E_NOTICE:
			$errtype = 'Notice';
			break;
		case E_CORE_ERROR:
			$errtype = 'Core Error';
			break;
		case E_CORE_WARNING:
			$errtype = 'Core Warning';
			break;
		case E_COMPILE_ERROR:
			$errtype = 'Compile Error';
			break;
		case E_COMPILE_WARNING:
			$errtype = 'Compile Warning';
			break;
		case E_USER_ERROR:
			$errtype = 'User Error';
			break;
		case E_USER_WARNING:
			$errtype = 'User Warning';
			break;
		case E_USER_NOTICE:
			$errtype = 'User Notice';
			break;
		case E_STRICT:
			$errtype = 'Runtime Notice';
			break;
		default:
			$errtype = 'Unknown';
			break;
		}

		$text = '<b>Error Type:</b>' . $errtype . '<br>' . '<b>Info:</b>' . $errstr . '<br>' . '<b>Line:</b>' . $errline . '<br>' . '<b>File:</b>' . $errfile;
		$txt  = 'Type:' . $errtype . ' Info:' . $errstr . ' Line:' . $errline . ' File:' . $errfile;
		array_push(self::$errors, $txt);
		Kotori_Log::normal($txt);
		//self::halt($text, 500);

	}

	/**
	 * Exception Handler
	 *
	 * Sends uncaught exceptions to the logger and displays them
	 * only if display_errors is On so that they don't show up in
	 * production environments.
	 *
	 * @param Exception $exception The exception
	 * @return void
	 */
	public static function exception($exception)
	{
		$text = '<b>Exception:</b>' . $exception->getMessage();
		$txt  = 'Type:Exception' . ' Info:' . $exception->getMessage();
		//array_push(self::$errors, $txt);
		Kotori_Log::normal($txt);
		self::halt($text, 500);
	}

	/**
	 * Shutdown Handler
	 *
	 * This is the shutdown handler that is declared in framework.
	 * The main reason we use this is to simulate
	 * a complete custom exception handler.
	 *
	 * E_STRICT is purposively neglected because such events may have
	 * been caught. Duplication or none? None is preferred for now.
	 *
	 * @return  void
	 */
	public static function end()
	{
		$last_error = error_get_last();
		if (isset($last_error) &&
			($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING)))
		{
			$text = '<b>Error Type:</b>' . $last_error['type'] . '<br>' . '<b>Info:</b>' . $last_error['message'] . '<br>' . '<b>Line:</b>' . $last_error['line'] . '<br>' . '<b>File:</b>' . $last_error['file'];
			$txt  = 'Type:' . $last_error['type'] . ' Info:' . $last_error['message'] . ' Line:' . $last_error['line'] . ' File:' . $last_error['file'];
			Kotori_Log::normal($txt);
			self::halt($text, 500);
		}

	}

}

/**
 * Exception Class
 *
 * @package     Kotori
 * @subpackage  Exception
 * @author      Kokororin
 * @link        https://kotori.love
 */
class Kotori_Exception extends Exception
{

}

/**
 * Route class
 *
 * Parses URIs and determines routing
 *
 * @package     Kotori
 * @subpackage  Route
 * @author      Kokororin
 * @link        https://kotori.love
 */
class Kotori_Route
{
	/**
	 * Instance Handle
	 *
	 * @var object
	 */
	private static $_instance = null;

	/**
	 * Controllers Array
	 *
	 * @var array
	 */
	private $_controller = array();

	/**
	 * get singleton
	 * @return object
	 */
	public static function getInstance()
	{
		if (!(self::$_instance instanceof self))
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Map URL to controller and action
	 *
	 * @return void
	 */
	public function dispatch()
	{
		if (isset($_GET['route']))
		{
			$_SERVER['PATH_INFO'] = $_GET['route'];
		}
		$_SERVER['PATH_INFO'] = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO']
		: (isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO']
			: (isset($_SERVER['REDIRECT_PATH_INFO']) ? $_SERVER['REDIRECT_PATH_INFO'] : ''));

		$uri = $_SERVER['PATH_INFO'];

		if (trim($uri, '/') == '')
		{
			$uri = '';
		}
		elseif (strncmp($uri, '/', 1) == 0)
		{
			$uri                     = explode('?', $uri, 2);
			$_SERVER['QUERY_STRING'] = isset($uri[1]) ? $uri[1] : '';
			$uri                     = $uri[0];
		}

		$parsedRoute = $this->parseRoutes($uri);
		if ($parsedRoute)
		{
			$uri = $parsedRoute;
		}

		$uriArray = ('' != $uri) ? explode('/', trim($uri, '/')) : array();

		$_controller = $this->getController($uriArray);
		$_action     = $this->getAction($uriArray);
		//Define some variables
		define('CONTROLLER_NAME', $_controller);
		define('ACTION_NAME', $_action);
		define('PUBLIC_DIR', Kotori_Request::getInstance()->getBaseUrl() . 'Public');
		unset($uriArray[0], $uriArray[1]);

		$controller = Kotori_Route::getInstance()->controller($_controller);

		if (!method_exists($controller, $_action))
		{
			throw new Kotori_Exception('Request Action ' . $_action . ' is not Found.');
		}
		//Parse params from uri
		$params = $this->getParams($uriArray);
		//Do some final cleaning of the params
		$_GET     = array_merge($params, $_GET);
		$_REQUEST = array_merge($_POST, $_GET, $_COOKIE);
		//Endtime
		define('END_TIME', microTime(true));
		define('RUN_TIME', END_TIME - START_TIME);
		header('X-Powered-By:Kotori');
		//Bind
		call_user_func_array(array($controller, $_action), $params);

	}

	/**
	 * Returns the controller name
	 *
	 * @param array $uriArray parsed uri array
	 * @return string
	 */
	private function getController($uriArray)
	{
		if (isset($uriArray[0]) && '' !== $uriArray[0])
		{
			$_controller = $uriArray[0];
		}
		else
		{
			$_controller = 'Index';
		}
		return strip_tags($_controller);
	}

	/**
	 * Returns the action name
	 *
	 * @param array $uriArray parsed uri array
	 * @return string
	 */
	private function getAction($uriArray)
	{
		if (isset($uriArray[1]))
		{
			$_action = $uriArray[1];
		}
		else
		{
			$_action = 'index';
		}
		return strip_tags($_action);
	}

	/**
	 * Returns the request params
	 *
	 * @param array $uriArray parsed uri array
	 * @return array
	 */
	private function getParams($uriArray)
	{
		$params = array();
		$params = $uriArray;
		return $params;
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
	private function parseRoutes($uri)
	{
		$routes = Kotori_Config::getInstance()->get('URL_ROUTE');

		if (null != $routes)
		{
			foreach ($routes as $key => $val)
			{
				if (is_array($val))
				{
					Kotori_Handle::halt('Route Rules Error.');
				}
				// Does the RegEx match?
				if (preg_match('#^' . $key . '$#', $uri, $matches))
				{

					// Are we using callbacks to process back-references?
					if (!is_string($val) && is_callable($val))
					{
						// Remove the original string from the matches array.
						array_shift($matches);

						// Execute the callback using the values in matches as its parameters.
						$val = call_user_func_array($val, $matches);
					}
					// Are we using the default routing method for back-references?
					elseif (strpos($val, '$') !== false && strpos($key, '(') !== false)
					{
						$val = preg_replace('#^' . $key . '$#', $val, $uri);
					}

					return $val;
				}

			}
		}

	}

	/**
	 * Build Full URL
	 *
	 * @param string $uri URI
	 * @param array $params Params Array
	 * @return string
	 */
	public function url($uri = '')
	{
		$base_url = Kotori_Request::getInstance()->getBaseUrl();
		$uri      = is_array($uri) ? implode('/', $uri) : trim($uri, '/');
		$prefix   = $base_url . 'index.php?route=' . $uri;

		switch (Kotori_Config::getInstance()->get('URL_MODE'))
		{
		case 'PATH_INFO':
			return $base_url . $uri;
			break;
		case 'QUERY_STRING':
			return $uri == '' ? $base_url . $uri : $base_url . $prefix . $uri;
			break;
		default:
			return;
			break;
		}

	}

	/**
	 * Call Controller
	 *
	 * @param string $controller Controller Name
	 * @return class
	 */
	public function controller($controllerName)
	{
		//If is already initialized
		$controllerClass = $controllerName . 'Controller';
		if (isset($this->_controller[$controllerClass]))
		{
			return $this->_controller[$controllerClass];
		}

		if (!class_exists($controllerClass))
		{
			throw new Kotori_Exception('Request Controller ' . $controllerClass . ' is not Found');
		}
		else
		{
			$controller                          = new $controllerClass();
			$this->_controller[$controllerClass] = $controller;
			return $controller;
		}

	}

}

/**
 * Application Controller Class
 *
 * This class object is the super class .
 *
 * @package     Kotori
 * @subpackage  Controller
 * @author      Kokororin
 * @link        https://kotori.love
 */
class Kotori_Controller
{
	/**
	 * Instance Handle
	 *
	 * @var object
	 */
	private static $_instance;

	/**
	 * Initialized Models
	 *
	 * @var array
	 */
	private $_model = array();

	/**
	 * get singleton
	 * @return object
	 */
	public static function &getInstance()
	{
		return self::$_instance;
	}

	/**
	 * Class constructor
	 *
	 * Initialize view and database classes.
	 *
	 * @return void
	 */
	public function __construct()
	{
		self::$_instance = &$this;
		$this->view      = new Kotori_View();
		$this->response  = Kotori_Response::getInstance();
		$this->request   = Kotori_Request::getInstance();
		$this->route     = Kotori_Route::getInstance();
		$this->db        = Kotori_Database::getInstance();
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
		if (substr($key, -5) == 'Model')
		{
			if (isset($this->_model[$key]))
			{
				return $this->_model[$key];
			}

			if (!class_exists($key))
			{
				throw new Kotori_Exception('Request Model ' . $key . ' is not Found');
			}
			else
			{
				$model              = new $key();
				$this->_model[$key] = $model;
				return $model;
			}
		}
		return null;
	}

	/**
	 * Class destructor
	 *
	 * In order to show Trace.
	 *
	 * @return void
	 */
	public function __destruct()
	{
		if (Kotori_Config::getInstance()->get('APP_DEBUG') == true && !Kotori_Request::getInstance()->isAjax())
		{
			echo Kotori_Trace::getInstance()->showTrace();
		}
	}

}

/**
 * Model Class
 *
 * @package     Kotori
 * @subpackage  Model
 * @author      Kokororin
 * @link        https://kotori.love
 */
class Kotori_Model
{
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
		return Kotori_Controller::getInstance()->$key;
	}
}

/**
 * View Class
 *
 * @package     Kotori
 * @subpackage  View
 * @author      Kokororin
 * @link        https://kotori.love
 */
class Kotori_View
{
	/**
	 * Template Direcory
	 *
	 * @var string
	 */
	private $_tplDir;

	/**
	 *
	 * Template Path
	 *
	 * @var string
	 */
	private $_viewPath;

	/**
	 * Variable List
	 *
	 * @var array
	 */
	private $_data = array();

	/**
	 * Variable List for TplInclude
	 *
	 * @var array
	 */
	private $_needData;

	/**
	 * __get magic
	 *
	 * Allows view to access loaded classes using the same
	 * syntax as controllers.
	 *
	 * @param string $key
	 */
	public function __get($key)
	{
		return Kotori_Controller::getInstance()->$key;
	}

	/**
	 * @param string $tplDir Template Directory
	 */
	public function __construct($tplDir = '')
	{
		if ('' == $tplDir)
		{
			$this->_tplDir = Kotori_Config::getInstance()->get('APP_FULL_PATH') . '/View/';
		}
		else
		{
			$this->_tplDir = $tplDir;
		}

	}

	/**
	 * Set variables for Template
	 *
	 * @param string $name key
	 * @param mixed $value value
	 * @return void
	 */
	public function assign($key, $value)
	{
		$this->_data[$key] = $value;
	}

	/**
	 * Display Output
	 *
	 * Processes and sends finalized output data to the browser along
	 *
	 * @param string $tpl Template Path
	 * @return void
	 */
	public function display($tpl = '')
	{
		if ('' === $tpl)
		{
			$tpl = CONTROLLER_NAME . '/' . ACTION_NAME;
		}
		$this->_viewPath = $this->_tplDir . $tpl . '.html';
		if (!Kotori_Common::isFile($this->_viewPath))
		{
			Kotori_Handle::halt('Template is not existed.');
		}
		unset($tpl);
		//Cache Control
		ob_start();
		ob_implicit_flush(0);
		extract($this->_data, EXTR_OVERWRITE);
		include $this->_viewPath;
		$content = ob_get_clean();
		echo $content;
	}

	/**
	 * Include Template
	 *
	 * @param string $path Template Path
	 * @param array $data Data Array
	 * @return void
	 */
	public function need($path, $data = array())
	{
		$this->_needData = array(
			'path' => Kotori_Config::getInstance()->get('APP_FULL_PATH') . '/View/' . $path . '.html',
			'data' => $data,
		);
		unset($path);
		unset($data);
		extract($this->_needData['data']);
		include $this->_needData['path'];
	}

}

/**
 * Request Class
 *
 * @package     Kotori
 * @subpackage  Request
 * @author      Kokororin
 * @link        https://kotori.love
 */
class Kotori_Request
{
	/**
	 * Instance Handle
	 *
	 * @var object
	 */
	private static $_instance = null;

	/**
	 * Params
	 *
	 * @var string
	 */
	private $_put = null;

	/**
	 * get singleton
	 * @return object
	 */
	public static function getInstance()
	{
		if (!(self::$_instance instanceof self))
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Safe Inputs
	 *
	 * @param string $name Param Name
	 * @param mixed $default Default Value
	 * @param mixed $filter Filter
	 * @param mixed $datas Extend Data Source
	 * @return mixed
	 */
	public function input($name, $default = '', $filter = null, $datas = null)
	{
		if (strpos($name, '/'))
		{
			list($name, $type) = explode('/', $name, 2);
		}
		else
		{
			$type = 's';
		}
		if (strpos($name, '.'))
		{
			list($method, $name) = explode('.', $name, 2);
		}
		else
		{
			$method = 'param';
		}
		switch (strtolower($method))
		{
		case 'get':
			$input = &$_GET;
			break;
		case 'post':
			$input = &$_POST;
			break;
		case 'put':
			if (is_null($this->_put))
				{
				parse_str(file_get_contents('php://input'), $this->_put);
			}
			$input = $this->_put;
			break;
		case 'param':
			switch ($_SERVER['REQUEST_METHOD'])
				{
			case 'POST':
				$input = $_POST;
				break;
			case 'PUT':
				if (is_null($this->_put))
					{
					parse_str(file_get_contents('php://input'), $this->_put);
				}
				$input = $this->_put;
				break;
			default:
				$input = $_GET;
			}
			break;
		case 'path':
			$input = array();
			if (!empty($_SERVER['PATH_INFO']))
				{
				$depr  = '/';
				$input = explode($depr, trim($_SERVER['PATH_INFO'], $depr));
			}
			break;
		case 'request':
			$input = &$_REQUEST;
			break;
		case 'session':
			$input = &$_SESSION;
			break;
		case 'cookie':
			$input = &$_COOKIE;
			break;
		case 'server':
			$input = &$_SERVER;
			break;
		case 'globals':
			$input = &$GLOBALS;
			break;
		case 'data':
			$input = &$datas;
			break;
		default:
			return null;
		}
		if ('' == $name)
		{
			$data    = $input;
			$filters = isset($filter) ? $filter : 'htmlspecialchars';
			if ($filters)
			{
				if (is_string($filters))
				{
					$filters = explode(',', $filters);
				}
				foreach ($filters as $filter)
				{
					$data = $this->array_map_recursive($filter, $data); // 参数过滤
				}
			}
		}
		elseif (isset($input[$name]))
		{
			$data    = $input[$name];
			$filters = isset($filter) ? $filter : 'htmlspecialchars';
			if ($filters)
			{
				if (is_string($filters))
				{
					if (0 === strpos($filters, '/') && 1 !== preg_match($filters, (string) $data))
					{
						return isset($default) ? $default : null;
					}
					else
					{
						$filters = explode(',', $filters);
					}
				}
				elseif (is_int($filters))
				{
					$filters = array($filters);
				}

				if (is_array($filters))
				{
					foreach ($filters as $filter)
					{
						if (function_exists($filter))
						{
							$data = is_array($data) ? $this->array_map_recursive($filter, $data) : $filter($data);
						}
						else
						{
							$data = filter_var($data, is_int($filter) ? $filter : filter_id($filter));
							if (false === $data)
							{
								return isset($default) ? $default : null;
							}
						}
					}
				}
			}
			if (!empty($type))
			{
				switch (strtolower($type))
				{
				case 'a':
					$data = (array) $data;
					break;
				case 'd':
					$data = (int) $data;
					break;
				case 'f':
					$data = (float) $data;
					break;
				case 'b':
					$data = (boolean) $data;
					break;
				case 's':
				default:
					$data = (string) $data;
				}
			}
		}
		else
		{
			// default
			$data = isset($default) ? $default : null;
		}
		is_array($data) && array_walk_recursive($data, array('Kotori_Request', 'filter'));
		return $data;

	}

	/**
	 * Callback Function
	 *
	 * @param string $filter Filter
	 * @param $data mixed Orginal data
	 * @return mixed
	 */
	private function array_map_recursive($filter, $data)
	{
		$result = array();
		foreach ($data as $key => $val)
		{
			$result[$key] = is_array($val)
			? $this->array_map_recursive($filter, $val)
			: call_user_func($filter, $val);
		}
		return $result;
	}

	/**
	 * Security Filter
	 *
	 * @param  $value Value
	 * @return void
	 */
	public static function filter(&$value)
	{
		if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value))
		{
			$value .= ' ';
		}
	}

	/**
	 * Is HTTPS?
	 *
	 * Determines if the application is accessed via an encrypted
	 * (HTTPS) connection.
	 *
	 * @return  boolean
	 */
	public function isSecure()
	{
		if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
		{
			return true;
		}
		elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'])
		{
			return true;
		}
		elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
		{
			return true;
		}
		return false;
	}

	/**
	 * Base URL
	 *
	 * Returns base url
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		if (isset($_SERVER['HTTP_HOST']) && preg_match('/^((\[[0-9a-f:]+\])|(\d{1,3}(\.\d{1,3}){3})|[a-z0-9\-\.]+)(:\d+)?$/i', $_SERVER['HTTP_HOST']))
		{
			$base_url = (Kotori_Request::getInstance()->isSecure() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
			. substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
		}
		else
		{
			$base_url = 'http://localhost/';
		}
		return rtrim($base_url, '/') . '/';
	}

	/**
	 * Detect whether request method is GET
	 *
	 * @return boolean
	 */
	public function isGet()
	{
		return 'GET' == $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Detect whether request method is POST
	 *
	 * @return boolean
	 */
	public function isPost()
	{
		return 'POST' == $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Detect whether request method is PUT
	 *
	 * @return boolean
	 */
	public function isPut()
	{
		return 'PUT' == $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Detect whether request method is AJAX
	 *
	 * @return boolean
	 */
	public function isAjax()
	{
		return ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) ? true : false;
	}
}
/**
 * Response Class
 *
 * @package     Kotori
 * @subpackage  Response
 * @author      Kokororin
 * @link        https://kotori.love
 */
class Kotori_Response
{
	/**
	 * Instance Handle
	 *
	 * @var object
	 */
	private static $_instance = null;

	/**
	 * Status array
	 *
	 * @var array
	 */
	private $_httpCode = array(
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',

		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		422 => 'Unprocessable Entity',

		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
	);

	/**
	 * get singleton
	 * @return object
	 */
	public static function getInstance()
	{
		if (!(self::$_instance instanceof self))
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Set HTTP Status Header
	 *
	 * @param int $code Status code
	 * @param string $text Custom text
	 * @return void
	 */
	public function setStatus($code = 200, $text = '')
	{
		if (empty($code) or !is_numeric($code))
		{
			Kotori_Handle::halt('Status codes must be numeric.', 500);
		}

		if (empty($text))
		{
			is_int($code) or $code = (int) $code;

			if (isset($this->_httpCode[$code]))
			{
				$text = $this->_httpCode[$code];
			}
			else
			{
				Kotori_Handle::halt('No status text available. Please check your status code number or supply your own message text.', 500);
			}
		}

		if (strpos(PHP_SAPI, 'cgi') === 0)
		{
			header('Status: ' . $code . ' ' . $text, true);
		}
		else
		{
			$server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
			header($server_protocol . ' ' . $code . ' ' . $text, true, $code);
		}
	}

	/**
	 * Set Header
	 *
	 * Lets you set a server header which will be sent with the final output.
	 *
	 * @param string $name Header
	 * @param string $value Value
	 * @return void
	 */
	public function setHeader($name, $value)
	{
		header($name . ': ' . $value, true);
	}

	/**
	 * Thown JSON to output
	 *
	 * @access public
	 * @param mixed $data Original Data
	 * @return void
	 */
	public function throwJson($data)
	{
		header('Content-Type:application/json; charset=utf-8');
		exit(json_encode($data));
	}

	/**
	 * Header Redirect
	 *
	 * @param string $location Redirect url
	 * @param boolean $isPermanently 301 or 302
	 * @return void
	 */
	public function redirect($location, $isPermanently = false)
	{
		if ($isPermanently)
		{
			header('Location: ' . $location, false, 301);
			exit;
		}
		else
		{
			header('Location: ' . $location, false, 302);
			exit;
		}
	}

}

/**
 * Trace Class
 *
 * @package     Kotori
 * @subpackage  Trace
 * @author      Kokororin
 * @link        https://kotori.love
 */
class Kotori_Trace
{
	/**
	 * traceTab
	 *
	 * @var array
	 */
	private $traceTabs = array(
		'BASE'  => 'Basic',
		'FILE'  => 'File',
		'ERROR' => 'Error',
		'SQL'   => 'SQL',
	);

	/**
	 * Instance Handle
	 *
	 * @var object
	 */
	private static $_instance = null;

	/**
	 * get singleton
	 * @return object
	 */
	public static function getInstance()
	{
		if (!(self::$_instance instanceof self))
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Get Page Trace
	 *
	 * @return array
	 */
	private function getTrace()
	{
		$files = get_included_files();
		$info  = array();
		foreach ($files as $key => $file)
		{
			$info[] = $file . ' ( ' . number_format(filesize($file) / 1024, 2) . ' KB )';
		}
		$error = Kotori_Handle::$errors;
		$sql   = Kotori_Database::getInstance()->queries;
		$trace = array();
		$base  = array(
			'Request Info' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ' . $_SERVER['SERVER_PROTOCOL'] . ' ' . $_SERVER['REQUEST_METHOD'] . ' : ' . $_SERVER['PHP_SELF'],
			'Run Time'     => RUN_TIME . 's',
			'TPR'          => number_format(1 / RUN_TIME, 2) . 'req/s',
			'Memory Uses'  => number_format(memory_get_usage() / 1024, 2) . ' kb',
			'SQL Queries'  => count($sql) . ' queries ',
			'File Loaded'  => count(get_included_files()),
			'Session Info' => 'SESSION_ID=' . session_id(),
		);

		$tabs = $this->traceTabs;
		foreach ($tabs as $name => $title)
		{
			switch (strtoupper($name))
			{
			case 'BASE':
				$trace[$title] = $base;
				break;
			case 'FILE':
				$trace[$title] = $info;
				break;
			case 'ERROR':
				$trace[$title] = $error;
				break;
			case 'SQL':
				$trace[$title] = $sql;
				break;
			}
		}
		return $trace;
	}

	/**
	 * Show Page Trace in Output
	 *
	 * @return string
	 */
	public function showTrace()
	{
		$trace = $this->getTrace();
		$tpl   = '<!-- Kotori Page Trace -->
<div id="kotori_page_trace" style="position: fixed;bottom:0;right:0;font-size:14px;width:100%;z-index: 999999;color: #000;text-align:left;font-family:\'Hiragino Sans GB\',\'Microsoft YaHei\',\'WenQuanYi Micro Hei\';">
<div id="kotori_page_trace_tab" style="display: none;background:white;margin:0;height: 250px;">
<div id="kotori_page_trace_tab_tit" style="height:30px;padding: 6px 12px 0;border-bottom:1px solid #ececec;border-top:1px solid #ececec;font-size:16px">';
		foreach ($trace as $key => $value)
		{
			$tpl .= '<span style="color:#000;padding-right:12px;height:30px;line-height: 30px;display:inline-block;margin-right:3px;cursor: pointer;font-weight:700">' . $key . '</span>';
		}
		$tpl .= '</div>
<div id="kotori_page_trace_tab_cont" style="overflow:auto;height:212px;padding: 0; line-height: 24px">';
		foreach ($trace as $info)
		{
			$tpl .= '<div style="display:none;">
    <ol style="padding: 0; margin:0">';
			if (is_array($info))
			{
				foreach ($info as $k => $val)
				{
					$tpl .= '<li style="border-bottom:1px solid #EEE;font-size:14px;padding:0 12px">' . (is_numeric($k) ? '' : $k . ' : ') . htmlentities($val, ENT_COMPAT, 'utf-8') . '</li>';
				}
			}

			$tpl .= '</ol>
    </div>';
		}
		$tpl .= '</div>
</div>
<div id="kotori_page_trace_close" style="display:none;text-align:right;height:15px;position:absolute;top:10px;right:12px;cursor: pointer;"><img style="vertical-align:top;" src="data:image/gif;base64,R0lGODlhDwAPAJEAAAAAAAMDA////wAAACH/C1hNUCBEYXRhWE1QPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS4wLWMwNjAgNjEuMTM0Nzc3LCAyMDEwLzAyLzEyLTE3OjMyOjAwICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUQxMjc1MUJCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUQxMjc1MUNCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoxRDEyNzUxOUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDoxRDEyNzUxQUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn5uXk4+Lh4N/e3dzb2tnY19bV1NPS0dDPzs3My8rJyMfGxcTDwsHAv769vLu6ubi3trW0s7KxsK+urayrqqmop6alpKOioaCfnp2cm5qZmJeWlZSTkpGQj46NjIuKiYiHhoWEg4KBgH9+fXx7enl4d3Z1dHNycXBvbm1sa2ppaGdmZWRjYmFgX15dXFtaWVhXVlVUU1JRUE9OTUxLSklIR0ZFRENCQUA/Pj08Ozo5ODc2NTQzMjEwLy4tLCsqKSgnJiUkIyIhIB8eHRwbGhkYFxYVFBMSERAPDg0MCwoJCAcGBQQDAgEAACH5BAAAAAAALAAAAAAPAA8AAAIdjI6JZqotoJPR1fnsgRR3C2jZl3Ai9aWZZooV+RQAOw==" /></div>
</div>
<div id="kotori_page_trace_open" style="height:30px;float:right;text-align: right;overflow:hidden;position:fixed;bottom:0;right:0;color:#000;line-height:30px;cursor:pointer;"><div style="background:#232323;color:#FFF;padding:0 6px;float:right;line-height:30px;font-size:14px">' . round(RUN_TIME * 1000) . 'ms</div><img width="30" style="border-left:2px solid black;border-top:2px solid black;border-bottom:2px solid black;" title="ShowPageTrace" src="data:image/png;base64,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"></div>
<script type="text/javascript">
(function(){
var tab_tit  = document.getElementById(\'kotori_page_trace_tab_tit\').getElementsByTagName(\'span\');
var tab_cont = document.getElementById(\'kotori_page_trace_tab_cont\').getElementsByTagName(\'div\');
var open     = document.getElementById(\'kotori_page_trace_open\');
var close    = document.getElementById(\'kotori_page_trace_close\').childNodes[0];
var trace    = document.getElementById(\'kotori_page_trace_tab\');
var cookie   = document.cookie.match(/kotori_show_page_trace=(\d\|\d)/);
var history  = (cookie && typeof cookie[1] != \'undefined\' && cookie[1].split(\'|\')) || [0,0];
open.onclick = function(){
    trace.style.display = \'block\';
    this.style.display = \'none\';
    close.parentNode.style.display = \'block\';
    history[0] = 1;
    document.cookie = \'kotori_show_page_trace=\'+history.join(\'|\')
}
close.onclick = function(){
    trace.style.display = \'none\';
this.parentNode.style.display = \'none\';
    open.style.display = \'block\';
    history[0] = 0;
    document.cookie = \'kotori_show_page_trace=\'+history.join(\'|\')
}
for(var i = 0; i < tab_tit.length; i++){
    tab_tit[i].onclick = (function(i){
        return function(){
            for(var j = 0; j < tab_cont.length; j++){
                tab_cont[j].style.display = \'none\';
                tab_tit[j].style.color = \'#999\';
            }
            tab_cont[i].style.display = \'block\';
            tab_tit[i].style.color = \'#000\';
            history[1] = i;
            document.cookie = \'kotori_show_page_trace=\'+history.join(\'|\')
        }
    })(i)
}
parseInt(history[0]) && open.click();
(tab_tit[history[1]] || tab_tit[0]).click();
})();
</script>';
		return $tpl;
	}
}

/*!
 * Medoo database framework
 * http://medoo.in
 * Version 1.0
 *
 * Copyright 2015, Angel Lai
 * Released under the MIT license
 */
class Kotori_Database
{
	// General
	protected $database_type;
	protected $charset;
	protected $database_name;
	// For MySQL, MariaDB, MSSQL, Sybase, PostgreSQL, Oracle
	protected $server;
	protected $username;
	protected $password;
	// For SQLite
	protected $database_file;
	// For MySQL or MariaDB with unix_socket
	protected $socket;
	// Optional
	protected $port;
	protected $prefix;
	protected $option = array();
	// Variable
	protected $logs       = array();
	protected $debug_mode = false;
	// Kotori
	public static $_instance = array();
	public $queries          = array();

	public static function getInstance()
	{
		if (Kotori_Config::getInstance()->get('DB_TYPE') == null)
		{
			$config = array();
			return null;
		}
		else
		{
			$config = array(
				'database_type' => Kotori_Config::getInstance()->get('DB_TYPE'),
				'database_name' => Kotori_Config::getInstance()->get('DB_NAME'),
				'server'        => Kotori_Config::getInstance()->get('DB_HOST'),
				'username'      => Kotori_Config::getInstance()->get('DB_USER'),
				'password'      => Kotori_Config::getInstance()->get('DB_PWD'),
				'charset'       => Kotori_Config::getInstance()->get('DB_CHARSET'),
				'port'          => Kotori_Config::getInstance()->get('DB_PORT'),
			);
		}
		$key = $config['server'] . ':' . $config['port'];
		if (!isset(self::$_instance[$key]) || !(self::$_instance[$key] instanceof self))
		{
			self::$_instance[$key] = new self($config);
		}
		return self::$_instance[$key];
	}

	public function __construct($options = null)
	{
		try {
			$commands = array();
			$dsn      = '';

			if (is_array($options))
			{
				foreach ($options as $option => $value)
				{
					$this->$option = $value;
				}
			}
			else
			{
				return false;
			}

			if (
				isset($this->port) &&
				is_int($this->port * 1)
			)
			{
				$port = $this->port;
			}

			$type    = strtolower($this->database_type);
			$is_port = isset($port);

			if (isset($options['prefix']))
			{
				$this->prefix = $options['prefix'];
			}

			switch ($type)
			{
			case 'mariadb':
				$type = 'mysql';

			case 'mysql':
				if ($this->socket)
					{
					$dsn = $type . ':unix_socket=' . $this->socket . ';dbname=' . $this->database_name;
				}
					else
					{
					$dsn = $type . ':host=' . $this->server . ($is_port ? ';port=' . $port : '') . ';dbname=' . $this->database_name;
				}

				// Make MySQL using standard quoted identifier
				$commands[] = 'SET SQL_MODE=ANSI_QUOTES';
				break;

			case 'pgsql':
				$dsn = $type . ':host=' . $this->server . ($is_port ? ';port=' . $port : '') . ';dbname=' . $this->database_name;
				break;

			case 'sybase':
				$dsn = 'dblib:host=' . $this->server . ($is_port ? ':' . $port : '') . ';dbname=' . $this->database_name;
				break;

			case 'oracle':
				$dbname = $this->server ?
				'//' . $this->server . ($is_port ? ':' . $port : ':1521') . '/' . $this->database_name :
				$this->database_name;

				$dsn = 'oci:dbname=' . $dbname . ($this->charset ? ';charset=' . $this->charset : '');
				break;

			case 'mssql':
				$dsn = strstr(PHP_OS, 'WIN') ?
				'sqlsrv:server=' . $this->server . ($is_port ? ',' . $port : '') . ';database=' . $this->database_name :
				'dblib:host=' . $this->server . ($is_port ? ':' . $port : '') . ';dbname=' . $this->database_name;

				// Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
				$commands[] = 'SET QUOTED_IDENTIFIER ON';
				break;

			case 'sqlite':
				$dsn            = $type . ':' . $this->database_file;
				$this->username = null;
				$this->password = null;
				break;
			}

			if (
				in_array($type, explode(' ', 'mariadb mysql pgsql sybase mssql')) &&
				$this->charset
			)
			{
				$commands[] = "SET NAMES '" . $this->charset . "'";
			}

			$this->pdo = new PDO(
				$dsn,
				$this->username,
				$this->password,
				$this->option
			);

			foreach ($commands as $value)
			{
				$this->pdo->exec($value);
			}
		}
		catch (PDOException $e)
		{
			throw new Kotori_Exception($e->getMessage());
		}
	}

	public function query($query)
	{
		if ($this->debug_mode)
		{
			echo $query;

			$this->debug_mode = false;

			return false;
		}

		array_push($this->logs, $query);
		Kotori_Log::sql($this->last_query());
		array_push($this->queries, $this->last_query());

		return $this->pdo->query($query);
	}

	public function exec($query)
	{
		if ($this->debug_mode)
		{
			echo $query;

			$this->debug_mode = false;

			return false;
		}

		array_push($this->logs, $query);
		Kotori_Log::sql($this->last_query());
		array_push($this->queries, $this->last_query());

		return $this->pdo->exec($query);
	}

	public function quote($string)
	{
		return $this->pdo->quote($string);
	}

	protected function column_quote($string)
	{
		return '"' . str_replace('.', '"."', preg_replace('/(^#|\(JSON\)\s*)/', '', $string)) . '"';
	}

	protected function column_push($columns)
	{
		if ($columns == '*')
		{
			return $columns;
		}

		if (is_string($columns))
		{
			$columns = array($columns);
		}

		$stack = array();

		foreach ($columns as $key => $value)
		{
			preg_match('/([a-zA-Z0-9_\-\.]*)\s*\(([a-zA-Z0-9_\-]*)\)/i', $value, $match);

			if (isset($match[1], $match[2]))
			{
				array_push($stack, $this->column_quote($match[1]) . ' AS ' . $this->column_quote($match[2]));
			}
			else
			{
				array_push($stack, $this->column_quote($value));
			}
		}

		return implode($stack, ',');
	}

	protected function array_quote($array)
	{
		$temp = array();

		foreach ($array as $value)
		{
			$temp[] = is_int($value) ? $value : $this->pdo->quote($value);
		}

		return implode($temp, ',');
	}

	protected function inner_conjunct($data, $conjunctor, $outer_conjunctor)
	{
		$haystack = array();

		foreach ($data as $value)
		{
			$haystack[] = '(' . $this->data_implode($value, $conjunctor) . ')';
		}

		return implode($outer_conjunctor . ' ', $haystack);
	}

	protected function fn_quote($column, $string)
	{
		return (strpos($column, '#') === 0 && preg_match('/^[A-Z0-9\_]*\([^)]*\)$/', $string)) ?

		$string :

		$this->quote($string);
	}

	protected function data_implode($data, $conjunctor, $outer_conjunctor = null)
	{
		$wheres = array();

		foreach ($data as $key => $value)
		{
			$type = gettype($value);

			if (
				preg_match("/^(AND|OR)(\s+#.*)?$/i", $key, $relation_match) &&
				$type == 'array'
			)
			{
				$wheres[] = 0 !== count(array_diff_key($value, array_keys(array_keys($value)))) ?
				'(' . $this->data_implode($value, ' ' . $relation_match[1]) . ')' :
				'(' . $this->inner_conjunct($value, ' ' . $relation_match[1], $conjunctor) . ')';
			}
			else
			{
				preg_match('/(#?)([\w\.\-]+)(\[(\>|\>\=|\<|\<\=|\!|\<\>|\>\<|\!?~)\])?/i', $key, $match);
				$column = $this->column_quote($match[2]);

				if (isset($match[4]))
				{
					$operator = $match[4];

					if ($operator == '!')
					{
						switch ($type)
						{
						case 'NULL':
							$wheres[] = $column . ' IS NOT NULL';
							break;

						case 'array':
							$wheres[] = $column . ' NOT IN (' . $this->array_quote($value) . ')';
							break;

						case 'integer':
						case 'double':
							$wheres[] = $column . ' != ' . $value;
							break;

						case 'boolean':
							$wheres[] = $column . ' != ' . ($value ? '1' : '0');
							break;

						case 'string':
							$wheres[] = $column . ' != ' . $this->fn_quote($key, $value);
							break;
						}
					}

					if ($operator == '<>' || $operator == '><')
					{
						if ($type == 'array')
						{
							if ($operator == '><')
							{
								$column .= ' NOT';
							}

							if (is_numeric($value[0]) && is_numeric($value[1]))
							{
								$wheres[] = '(' . $column . ' BETWEEN ' . $value[0] . ' AND ' . $value[1] . ')';
							}
							else
							{
								$wheres[] = '(' . $column . ' BETWEEN ' . $this->quote($value[0]) . ' AND ' . $this->quote($value[1]) . ')';
							}
						}
					}

					if ($operator == '~' || $operator == '!~')
					{
						if ($type == 'string')
						{
							$value = array($value);
						}

						if (!empty($value))
						{
							$like_clauses = array();

							foreach ($value as $item)
							{
								if (preg_match('/^(?!%).+(?<!%)$/', $item))
								{
									$item = '%' . $item . '%';
								}

								$like_clauses[] = $column . ($operator === '!~' ? ' NOT' : '') . ' LIKE ' . $this->fn_quote($key, $item);
							}

							$wheres[] = implode(' OR ', $like_clauses);
						}
					}

					if (in_array($operator, array('>', '>=', '<', '<=')))
					{
						if (is_numeric($value))
						{
							$wheres[] = $column . ' ' . $operator . ' ' . $value;
						}
						elseif (strpos($key, '#') === 0)
						{
							$wheres[] = $column . ' ' . $operator . ' ' . $this->fn_quote($key, $value);
						}
						else
						{
							$wheres[] = $column . ' ' . $operator . ' ' . $this->quote($value);
						}
					}
				}
				else
				{
					switch ($type)
					{
					case 'NULL':
						$wheres[] = $column . ' IS NULL';
						break;

					case 'array':
						$wheres[] = $column . ' IN (' . $this->array_quote($value) . ')';
						break;

					case 'integer':
					case 'double':
						$wheres[] = $column . ' = ' . $value;
						break;

					case 'boolean':
						$wheres[] = $column . ' = ' . ($value ? '1' : '0');
						break;

					case 'string':
						$wheres[] = $column . ' = ' . $this->fn_quote($key, $value);
						break;
					}
				}
			}
		}

		return implode($conjunctor . ' ', $wheres);
	}

	protected function where_clause($where)
	{
		$where_clause = '';

		if (is_array($where))
		{
			$where_keys = array_keys($where);
			$where_AND  = preg_grep("/^AND\s*#?$/i", $where_keys);
			$where_OR   = preg_grep("/^OR\s*#?$/i", $where_keys);

			$single_condition = array_diff_key($where, array_flip(
				explode(' ', 'AND OR GROUP ORDER HAVING LIMIT LIKE MATCH')
			));

			if ($single_condition != array())
			{
				$where_clause = ' WHERE ' . $this->data_implode($single_condition, '');
			}

			if (!empty($where_AND))
			{
				$value        = array_values($where_AND);
				$where_clause = ' WHERE ' . $this->data_implode($where[$value[0]], ' AND');
			}

			if (!empty($where_OR))
			{
				$value        = array_values($where_OR);
				$where_clause = ' WHERE ' . $this->data_implode($where[$value[0]], ' OR');
			}

			if (isset($where['MATCH']))
			{
				$MATCH = $where['MATCH'];

				if (is_array($MATCH) && isset($MATCH['columns'], $MATCH['keyword']))
				{
					$where_clause .= ($where_clause != '' ? ' AND ' : ' WHERE ') . ' MATCH ("' . str_replace('.', '"."', implode($MATCH['columns'], '", "')) . '") AGAINST (' . $this->quote($MATCH['keyword']) . ')';
				}
			}

			if (isset($where['GROUP']))
			{
				$where_clause .= ' GROUP BY ' . $this->column_quote($where['GROUP']);

				if (isset($where['HAVING']))
				{
					$where_clause .= ' HAVING ' . $this->data_implode($where['HAVING'], ' AND');
				}
			}

			if (isset($where['ORDER']))
			{
				$rsort = '/(^[a-zA-Z0-9_\-\.]*)(\s*(DESC|ASC))?/';
				$ORDER = $where['ORDER'];

				if (is_array($ORDER))
				{
					if (
						isset($ORDER[1]) &&
						is_array($ORDER[1])
					)
					{
						$where_clause .= ' ORDER BY FIELD(' . $this->column_quote($ORDER[0]) . ', ' . $this->array_quote($ORDER[1]) . ')';
					}
					else
					{
						$stack = array();

						foreach ($ORDER as $column)
						{
							preg_match($rsort, $column, $order_match);

							array_push($stack, '"' . str_replace('.', '"."', $order_match[1]) . '"' . (isset($order_match[3]) ? ' ' . $order_match[3] : ''));
						}

						$where_clause .= ' ORDER BY ' . implode($stack, ',');
					}
				}
				else
				{
					preg_match($rsort, $ORDER, $order_match);

					$where_clause .= ' ORDER BY "' . str_replace('.', '"."', $order_match[1]) . '"' . (isset($order_match[3]) ? ' ' . $order_match[3] : '');
				}
			}

			if (isset($where['LIMIT']))
			{
				$LIMIT = $where['LIMIT'];

				if (is_numeric($LIMIT))
				{
					$where_clause .= ' LIMIT ' . $LIMIT;
				}

				if (
					is_array($LIMIT) &&
					is_numeric($LIMIT[0]) &&
					is_numeric($LIMIT[1])
				)
				{
					if ($this->database_type === 'pgsql')
					{
						$where_clause .= ' OFFSET ' . $LIMIT[0] . ' LIMIT ' . $LIMIT[1];
					}
					else
					{
						$where_clause .= ' LIMIT ' . $LIMIT[0] . ',' . $LIMIT[1];
					}
				}
			}
		}
		else
		{
			if ($where != null)
			{
				$where_clause .= ' ' . $where;
			}
		}

		return $where_clause;
	}

	protected function select_context($table, $join, &$columns = null, $where = null, $column_fn = null)
	{
		$table    = '"' . $this->prefix . $table . '"';
		$join_key = is_array($join) ? array_keys($join) : null;

		if (
			isset($join_key[0]) &&
			strpos($join_key[0], '[') === 0
		)
		{
			$table_join = array();

			$join_array = array(
				'>'  => 'LEFT',
				'<'  => 'RIGHT',
				'<>' => 'FULL',
				'><' => 'INNER',
			);

			foreach ($join as $sub_table => $relation)
			{
				preg_match('/(\[(\<|\>|\>\<|\<\>)\])?([a-zA-Z0-9_\-]*)\s?(\(([a-zA-Z0-9_\-]*)\))?/', $sub_table, $match);

				if ($match[2] != '' && $match[3] != '')
				{
					if (is_string($relation))
					{
						$relation = 'USING ("' . $relation . '")';
					}

					if (is_array($relation))
					{
						// For ['column1', 'column2']
						if (isset($relation[0]))
						{
							$relation = 'USING ("' . implode($relation, '", "') . '")';
						}
						else
						{
							$joins = array();

							foreach ($relation as $key => $value)
							{
								$joins[] = (
									strpos($key, '.') > 0 ?
									// For ['tableB.column' => 'column']
									'"' . str_replace('.', '"."', $key) . '"' :

									// For ['column1' => 'column2']
									$table . '."' . $key . '"'
								) .
									' = ' .
									'"' . (isset($match[5]) ? $match[5] : $match[3]) . '"."' . $value . '"';
							}

							$relation = 'ON ' . implode($joins, ' AND ');
						}
					}

					$table_join[] = $join_array[$match[2]] . ' JOIN "' . $match[3] . '" ' . (isset($match[5]) ? 'AS "' . $match[5] . '" ' : '') . $relation;
				}
			}

			$table .= ' ' . implode($table_join, ' ');
		}
		else
		{
			if (is_null($columns))
			{
				if (is_null($where))
				{
					if (
						is_array($join) &&
						isset($column_fn)
					)
					{
						$where   = $join;
						$columns = null;
					}
					else
					{
						$where   = null;
						$columns = $join;
					}
				}
				else
				{
					$where   = $join;
					$columns = null;
				}
			}
			else
			{
				$where   = $columns;
				$columns = $join;
			}
		}

		if (isset($column_fn))
		{
			if ($column_fn == 1)
			{
				$column = '1';

				if (is_null($where))
				{
					$where = $columns;
				}
			}
			else
			{
				if (empty($columns))
				{
					$columns = '*';
					$where   = $join;
				}

				$column = $column_fn . '(' . $this->column_push($columns) . ')';
			}
		}
		else
		{
			$column = $this->column_push($columns);
		}

		return 'SELECT ' . $column . ' FROM ' . $table . $this->where_clause($where);
	}

	public function select($table, $join, $columns = null, $where = null)
	{
		$query = $this->query($this->select_context($table, $join, $columns, $where));

		return $query ? $query->fetchAll(
			(is_string($columns) && $columns != '*') ? PDO::FETCH_COLUMN : PDO::FETCH_ASSOC
		) : false;
	}

	public function insert($table, $datas)
	{
		$lastId = array();

		// Check indexed or associative array
		if (!isset($datas[0]))
		{
			$datas = array($datas);
		}

		foreach ($datas as $data)
		{
			$values  = array();
			$columns = array();

			foreach ($data as $key => $value)
			{
				array_push($columns, $this->column_quote($key));

				switch (gettype($value))
				{
				case 'NULL':
					$values[] = 'NULL';
					break;

				case 'array':
					preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

					$values[] = isset($column_match[0]) ?
					$this->quote(json_encode($value)) :
					$this->quote(serialize($value));
					break;

				case 'boolean':
					$values[] = ($value ? '1' : '0');
					break;

				case 'integer':
				case 'double':
				case 'string':
					$values[] = $this->fn_quote($key, $value);
					break;
				}
			}

			$this->exec('INSERT INTO "' . $this->prefix . $table . '" (' . implode(', ', $columns) . ') VALUES (' . implode($values, ', ') . ')');

			$lastId[] = $this->pdo->lastInsertId();
		}

		return count($lastId) > 1 ? $lastId : $lastId[0];
	}

	public function update($table, $data, $where = null)
	{
		$fields = array();

		foreach ($data as $key => $value)
		{
			preg_match('/([\w]+)(\[(\+|\-|\*|\/)\])?/i', $key, $match);

			if (isset($match[3]))
			{
				if (is_numeric($value))
				{
					$fields[] = $this->column_quote($match[1]) . ' = ' . $this->column_quote($match[1]) . ' ' . $match[3] . ' ' . $value;
				}
			}
			else
			{
				$column = $this->column_quote($key);

				switch (gettype($value))
				{
				case 'NULL':
					$fields[] = $column . ' = NULL';
					break;

				case 'array':
					preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

					$fields[] = $column . ' = ' . $this->quote(
						isset($column_match[0]) ? json_encode($value) : serialize($value)
					);
					break;

				case 'boolean':
					$fields[] = $column . ' = ' . ($value ? '1' : '0');
					break;

				case 'integer':
				case 'double':
				case 'string':
					$fields[] = $column . ' = ' . $this->fn_quote($key, $value);
					break;
				}
			}
		}

		return $this->exec('UPDATE "' . $this->prefix . $table . '" SET ' . implode(', ', $fields) . $this->where_clause($where));
	}

	public function delete($table, $where)
	{
		return $this->exec('DELETE FROM "' . $this->prefix . $table . '"' . $this->where_clause($where));
	}

	public function replace($table, $columns, $search = null, $replace = null, $where = null)
	{
		if (is_array($columns))
		{
			$replace_query = array();

			foreach ($columns as $column => $replacements)
			{
				foreach ($replacements as $replace_search => $replace_replacement)
				{
					$replace_query[] = $column . ' = REPLACE(' . $this->column_quote($column) . ', ' . $this->quote($replace_search) . ', ' . $this->quote($replace_replacement) . ')';
				}
			}

			$replace_query = implode(', ', $replace_query);
			$where         = $search;
		}
		else
		{
			if (is_array($search))
			{
				$replace_query = array();

				foreach ($search as $replace_search => $replace_replacement)
				{
					$replace_query[] = $columns . ' = REPLACE(' . $this->column_quote($columns) . ', ' . $this->quote($replace_search) . ', ' . $this->quote($replace_replacement) . ')';
				}

				$replace_query = implode(', ', $replace_query);
				$where         = $replace;
			}
			else
			{
				$replace_query = $columns . ' = REPLACE(' . $this->column_quote($columns) . ', ' . $this->quote($search) . ', ' . $this->quote($replace) . ')';
			}
		}

		return $this->exec('UPDATE "' . $this->prefix . $table . '" SET ' . $replace_query . $this->where_clause($where));
	}

	public function get($table, $join = null, $column = null, $where = null)
	{
		$query = $this->query($this->select_context($table, $join, $column, $where) . ' LIMIT 1');

		if ($query)
		{
			$data = $query->fetchAll(PDO::FETCH_ASSOC);

			if (isset($data[0]))
			{
				$column = $where == null ? $join : $column;

				if (is_string($column) && $column != '*')
				{
					return $data[0][$column];
				}

				return $data[0];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	public function has($table, $join, $where = null)
	{
		$column = null;

		$query = $this->query('SELECT EXISTS(' . $this->select_context($table, $join, $column, $where, 1) . ')');

		return $query ? $query->fetchColumn() === '1' : false;
	}

	public function count($table, $join = null, $column = null, $where = null)
	{
		$query = $this->query($this->select_context($table, $join, $column, $where, 'COUNT'));

		return $query ? 0 + $query->fetchColumn() : false;
	}

	public function max($table, $join, $column = null, $where = null)
	{
		$query = $this->query($this->select_context($table, $join, $column, $where, 'MAX'));

		if ($query)
		{
			$max = $query->fetchColumn();

			return is_numeric($max) ? $max + 0 : $max;
		}
		else
		{
			return false;
		}
	}

	public function min($table, $join, $column = null, $where = null)
	{
		$query = $this->query($this->select_context($table, $join, $column, $where, 'MIN'));

		if ($query)
		{
			$min = $query->fetchColumn();

			return is_numeric($min) ? $min + 0 : $min;
		}
		else
		{
			return false;
		}
	}

	public function avg($table, $join, $column = null, $where = null)
	{
		$query = $this->query($this->select_context($table, $join, $column, $where, 'AVG'));

		return $query ? 0 + $query->fetchColumn() : false;
	}

	public function sum($table, $join, $column = null, $where = null)
	{
		$query = $this->query($this->select_context($table, $join, $column, $where, 'SUM'));

		return $query ? 0 + $query->fetchColumn() : false;
	}

	public function action($actions)
	{
		if (is_callable($actions))
		{
			$this->pdo->beginTransaction();

			$result = $actions($this);

			if ($result === false)
			{
				$this->pdo->rollBack();
			}
			else
			{
				$this->pdo->commit();
			}
		}
		else
		{
			return false;
		}
	}

	public function debug()
	{
		$this->debug_mode = true;

		return $this;
	}

	public function error()
	{
		return $this->pdo->errorInfo();
	}

	public function last_query()
	{
		return end($this->logs);
	}

	public function log()
	{
		return $this->logs;
	}

	public function info()
	{
		$output = array(
			'server'     => 'SERVER_INFO',
			'driver'     => 'DRIVER_NAME',
			'client'     => 'CLIENT_VERSION',
			'version'    => 'SERVER_VERSION',
			'connection' => 'CONNECTION_STATUS',
		);

		foreach ($output as $key => $value)
		{
			$output[$key] = $this->pdo->getAttribute(constant('PDO::ATTR_' . $value));
		}

		return $output;
	}
}

/**
 * Logging Class
 *
 */
class Kotori_Log
{
	/**
	 * Write Log File
	 *
	 * Support Sina App Engine
	 *
	 * @param string $msg Message
	 * @param string $level Log level
	 * @return void
	 */
	private static function write($msg, $level = '')
	{
		if (Kotori_Config::getInstance()->get('APP_DEBUG') == false)
		{
			return;
		}
		if (function_exists('saeAutoLoader'))
		{
			$msg = "[{$level}]" . $msg;
			sae_set_display_errors(false);
			sae_debug(trim($msg));
			sae_set_display_errors(true);
		}
		else
		{
			$msg     = date('[ Y-m-d H:i:s ]') . "[{$level}]" . $msg . "\r\n";
			$logPath = Kotori_Config::getInstance()->get('APP_FULL_PATH') . '/Log/' . date('Ymd') . '.log';
			file_put_contents($logPath, $msg, FILE_APPEND);
		}
	}

	/**
	 * Write Normal Log
	 *
	 * @param string $msg Message
	 */
	public static function normal($msg)
	{
		self::write($msg, 'NORMAL');
	}

	/**
	 * Write SQL Log
	 *
	 * @param string $msg Message
	 */
	public static function sql($msg)
	{
		self::write($msg, 'SQL');
	}
}
