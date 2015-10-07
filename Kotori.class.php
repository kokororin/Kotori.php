<?php
// +----------------------------------------------------------------------
// | Kotori Framework (a Tiny Controller-View PHP Framework)
// +----------------------------------------------------------------------
// | Copyright (c) 2015 https://kotori.love All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Kokororin <ritsuka.sunny@gmail.com>
// +----------------------------------------------------------------------

/**
 * Kotori核心类
 */
class Kotori
{
    /**
     * 运行应用实例
     * @param mixed $conf 配置文件
     * @return void
     */
    public static function run($conf)
    {
        //error_reporting(0);

        Config::init($conf);
        self::init();
    }

    /**
     * 框架初始化
     * @return void
     */
    private static function init()
    {
        set_error_handler(array('Handle', 'error'));
        set_exception_handler(array('Handle', 'exception'));
        register_shutdown_function(array('Handle', 'end'));

        if (Config::get('USE_SESSION') == true) {
            session_start();
        }

        Common::need(Config::get('APP_FULL_PATH') . '/common.php');

        spl_autoload_register(array('Kotori', 'autoload'));
        //定义一些常用的常量

        define('PUBLIC_DIR', Request::getBaseUrl() . 'Public');
        //装载路由类
        Dispatcher::dispatch();

        //全局安全过滤
        array_walk_recursive($_GET, array('Request', 'filter'));
        array_walk_recursive($_POST, array('Request', 'filter'));
        array_walk_recursive($_REQUEST, array('Request', 'filter'));

    }

    /**
     * 自动加载函数
     * @param string $class 类名
     * @return void
     */
    private static function autoload($class)
    {
        if (substr($class, -10) == 'Controller') {
            Common::need(Config::get('APP_FULL_PATH') . '/Controller/' . $class . '.class.php');
        } else {
            Common::need(Config::get('APP_FULL_PATH') . '/Lib/' . $class . '.class.php');
        }
    }
}

/**
 * 通用类
 */
class Common
{
    /**
     * require存放数组
     * @var array
     */
    private static $_require = array();

    /**
     * 优化的require 区分大小写
     * @param string $path 文件路径
     * @return boolean
     */
    public static function need($path)
    {
        if (!isset(self::$_require[$path])) {
            if (self::isFile($path)) {
                require $path;
                self::$_require[$path] = true;
            } else {
                self::$_require[$path] = false;
            }
        }
        return self::$_require[$path];

    }

    /**
     * 区分大小写的文件存在判断
     * @param string $path 文件路径
     * @return boolean
     */
    private static function isFile($path)
    {
        if (is_file($path)) {
            if (strstr(PHP_OS, 'WIN')) {
                if (basename(realpath($path)) != basename($path)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}

/**
 * 配置类
 */

class Config
{
    /**
     * 配置数组
     * @var config
     */
    private static $config = array();

    /**
     * 初始化配置
     * @param mixed $conf 配置文件
     * @return void
     */
    public static function init($conf)
    {
        if (is_array($conf)) {
            if (array_keys($conf) !== range(0, count($conf) - 1)) {
                self::$config = array_merge(self::$config, $conf);
                self::$config = array_merge(self::defaults(), self::$config);
            }
        }
        return false;
    }

    /**
     * 默认配置
     * @return array
     *
     */
    private static function defaults()
    {
        return array(
            'APP_DEBUG' => 'false',
            'APP_PATH' => './App',
            'APP_FULL_PATH' => dirname(__FILE__) . '/' . self::get('APP_PATH'),
            'DB_TYPE' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_USER' => 'root',
            'DB_PWD' => 'root',
            'DB_NAME' => 'test',
            'DB_PORT' => 3306,
            'DB_CHARSET' => 'utf8',
            'USE_SESSION' => true,
            'URL_MODE' => 'QUERY_STRING',
            'URL_PARAMS_BIND' => 'NORMAL',
            'ERROR_TPL' => 'Public/error',
        );

    }
    /**
     * 设置配置值
     * @param string $key 配置名
     * @param $key 配置值
     * @return void
     */
    public static function set($key, $value)
    {
        if (is_string($key)) {
            $_config[$key] = $value;
        } else {
            Handle::halt('配置出错啦~');
        }
    }

    /**
     * 获取配置值
     * @param $key 配置值
     * @return mixed
     */
    public static function get($key)
    {
        if (is_string($key)) {
            return isset(self::$config[$key]) ? self::$config[$key] : null;
        }
        return null;
    }
}

class Handle
{
    /**
     * 输出错误并终止程序
     * @param string $str 出错原因
     * @param int $code 状态码
     * @return void
     */
    public static function halt($str, $code = '')
    {
        Response::setStatus($code);
        $_view = new View();
        if (Config::get('APP_DEBUG') == false) {
            $str = '404';
        }
        $_view->assign('str', $str);
        $_view->display(Config::get('ERROR_TPL'));
        exit;

    }

/**
 * 框架自定义错误
 * @param int $errno 错误号
 * @param string $errstr 错误信息
 * @param string $errfile 错误文件
 * @param int $errline 错误行号
 * @return void
 */
    public static function error($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_WARNING:
                $errtype = 'WARNING';
                break;
            case E_NOTICE:
                $errtype = 'NOTICE';
                break;
            case E_STRICT:
                $errtype = 'STRICT';
                break;
            case 8192:
                $errtype = 'DEPRECATED';
                break;
            default:
                $errtype = 'UNKNOWN';
                break;
        }

        $text = '<b>错误类型：</b>' . $errtype . '<br>' . '<b>信息：</b>' . $errstr . '<br>' . '<b>行数：</b>' . $errline . '<br>' . '<b>文件：</b>' . $errfile;
        $txt = '错误类型：' . $errtype . ' 信息：' . $errstr . ' 行数：' . $errline . ' 文件：' . $errfile;
        Log::normal($txt);
        self::halt($text, 500);

    }

/**
 * 框架自定义异常
 * @param string $exception 异常信息
 * @return void
 */
    public static function exception($exception)
    {
        $text = '<b>异常：</b>' . $exception->getMessage();
        $txt = '错误类型：Exception' . ' 信息：' . $exception->getMessage();
        Log::normal($txt);
        self::halt($text, 500);
    }

/**
 * 框架自定义致命错误
 * @return void
 */
    public static function end()
    {
        $last_error = error_get_last();
        if (isset($last_error) &&
            ($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))) {
            self::error($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
        }

    }
}

/**
 * URL调度类
 */
class Dispatcher
{
    /**
     * URL映射到控制器和操作
     */
    public static function dispatch()
    {

        switch (Config::get('URL_MODE')) {
            case 'PATH_INFO':
                $uri = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO']
                : (isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO']
                    : (isset($_SERVER['REDIRECT_PATH_INFO']) ? $_SERVER['REDIRECT_PATH_INFO'] : ''));
                break;
            case 'QUERY_STRING':
                $uri = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
                if (trim($uri, '/') == '') {
                    $uri = '';
                } elseif (strncmp($uri, '/', 1) == 0) {
                    $uri = explode('?', $uri, 2);
                    $_SERVER['QUERY_STRING'] = isset($uri[1]) ? $uri[1] : '';
                    $uri = $uri[0];
                }
                break;
            default:
                break;
        }

        $uriArray = ($uri != '') ? explode('/', trim($uri, '/')) : array();

        $_controller = self::getController($uriArray);
        $_action = self::getAction($uriArray);
        define('CONTROLLER_NAME', $_controller);
        define('ACTION_NAME', $_action);
        unset($uriArray[0], $uriArray[1]);

        $controller = Util::call($_controller);

        if (!method_exists($controller, $_action)) {
            throw new Exception('请求的方法：' . $_action . '不存在');
        }

        $params = self::getParams($uriArray);

        $_GET = array_merge($params, $_GET);
        $_REQUEST = array_merge($_POST, $_GET, $_COOKIE);
        //以下来自http://jingyan.todgo.com/jiaoyu/1883184mab.html

        call_user_func_array(array($controller, $_action), $params);

    }

    /**
     * 获得实际的控制器名称
     * @param $uriArray 解析的uri数组
     * @return string
     */
    private static function getController($uriArray)
    {
        if (isset($_GET['_controller']) && isset($_GET['_action'])) {
            return strip_tags($_GET['_controller']);
        }
        if (isset($uriArray[0]) && $uriArray[0] !== '') {
            $_controller = $uriArray[0];
        } else {
            $_controller = 'Index';
        }
        return strip_tags($_controller);
    }

    /**
     * 获得实际的操作名称
     * @param $uriArray 解析的uri数组
     * @return string
     */
    private static function getAction($uriArray)
    {
        if (isset($_GET['_controller']) && isset($_GET['_action'])) {
            return strip_tags($_GET['_action']);
        }
        if (isset($uriArray[1])) {
            $_action = $uriArray[1];
        } else {
            $_action = 'index';
        }
        return strip_tags($_action);
    }

    /**
     * 获得请求参数
     * @param $uriArray 解析的uri数组
     * @return array
     */
    private static function getParams($uriArray)
    {

        $params = array();

        if (isset($_GET['_controller']) && isset($_GET['_action'])) {
            unset($_GET['_controller'], $_GET['_action']);
            $params = $_GET;
            return $params;
        }

        if (Config::get('URL_PARAMS_BIND') && 'ORDER' == Config::get('URL_PARAMS_BIND')) {
            $params = $uriArray;
        } else {
            preg_replace_callback('/(\w+)\/([^\/]+)/', function ($match) use (&$params) {$params[$match[1]] = strip_tags($match[2]);}, implode('/', $uriArray));
        }
        return $params;
    }

}

/**
 * 控制器类
 */
abstract class Controller
{
    /**
     * 视图实例对象
     * @var View
     */
    protected $_view;

    /**
     * 数据库实例对象
     * @var DB
     */
    protected $db;

    /**
     * 实例化过的控制器
     * @var array
     */
    private $_controller = array();

    /**
     * 构造函数，初始化视图实例，调用hook
     */
    public function __construct()
    {
        $this->_view = new View();
        $this->_init();
        $this->dbInit();
    }

    /**
     * 前置hook
     */
    protected function _init()
    {}

    /**
     * 渲染模板并输出
     * @param string $tpl 模板文件路径
     * @return void
     */
    protected function display($tpl = '')
    {
        if ($tpl === '') {
            $trace = debug_backtrace();
            $controller = substr($trace[1]['class'], 0, -10);
            $action = $trace[1]['function'];
            $tpl = $controller . '/' . $action;
        } elseif (strpos($tpl, '/') === false) {
            $trace = debug_backtrace();
            $controller = substr($trace[1]['class'], 0, -10);
            $tpl = $controller . '/' . $tpl;
        }
        $this->_view->display($tpl);
    }

    /**
     * 为视图引擎设置一个模板变量
     * @param string $name 要在模板中使用的变量名
     * @param mixed $value 模板中该变量名对应的值
     * @return void
     */
    protected function assign($name, $value)
    {
        $this->_view->assign($name, $value);
    }

    /**
     * 初始化数据库类
     * @return DB
     */
    private function dbInit()
    {
        return new medoo(array(
            'database_type' => Config::get('DB_TYPE'),
            'database_name' => Config::get('DB_NAME'),
            'server' => Config::get('DB_HOST'),
            'username' => Config::get('DB_USER'),
            'password' => Config::get('DB_PWD'),
            'charset' => Config::get('DB_CHARSET'),
            'port' => Config::get('DB_PORT'),
        ));
    }
}

/**
 * 视图类
 */
class View
{
    /**
     * 视图文件目录
     * @var string
     */
    private $_tplDir;
    /**
     * 视图文件路径
     * @var string
     */
    private $_viewPath;
    /**
     * 视图变量列表
     * @var array
     */
    private $_data = array();
    /**
     * 给tplInclude用的变量列表
     * @var array
     */
    private static $tmpData;

    /**
     * @param string $tplDir
     */
    public function __construct($tplDir = '')
    {
        if ($tplDir == '') {
            $this->_tplDir = Config::get('APP_FULL_PATH') . '/View/';
        } else {
            $this->_tplDir = $tplDir;
        }

    }
    /**
     * 为视图引擎设置一个模板变量
     * @param string $key 要在模板中使用的变量名
     * @param mixed $value 模板中该变量名对应的值
     * @return void
     */
    public function assign($key, $value)
    {
        $this->_data[$key] = $value;
    }
    /**
     * 渲染模板并输出
     * @param null|string $tplFile 模板文件路径，相对于App/View/文件的相对路径，不包含后缀名，例如index/index
     * @return void
     */
    public function display($tplFile)
    {
        $this->_viewPath = $this->_tplDir . $tplFile . '.html';
        unset($tplFile);
        extract($this->_data);
        include $this->_viewPath;
    }
    /**
     * 模板文件中包含其他模板
     * @param string $path 相对于View目录的路径
     * @param array $data 传递给子模板的变量列表，key为变量名，value为变量值
     * @return void
     */
    public static function includeTpl($path, $data = array())
    {
        self::$tmpData = array(
            'path' => Config::get('APP_FULL_PATH') . '/View/' . $path . '.html',
            'data' => $data,
        );
        unset($path);
        unset($data);
        extract(self::$tmpData['data']);
        include self::$tmpData['path'];
    }

    /**
     * 生成Url核心方法
     * @param string $url Url
     * @param array $params 参数数组
     * @return string
     */
    public static function buildUrl($url = '', $params = array())
    {
        $base_url = Request::getBaseUrl();
        if (!empty($params)) {
            $keys = array_keys($params);
            $values = array_values($params);
            $count = count($params);
            $http_query = '/';
            for ($i = 0; $i < $count; $i++) {
                if ('ORDER' == Config::get('URL_PARAMS_BIND')) {
                    $http_query .= $values[$i];
                } else {
                    $http_query .= $keys[$i] . '/' . $values[$i];
                }

                if ($i != ($count - 1)) {
                    $http_query .= '/';
                }
            }

        } else {
            $http_query = '';
        }

        switch (Config::get('URL_MODE')) {
            case 'PATH_INFO':
                return $base_url . $url . $http_query;
                break;
            case 'QUERY_STRING':
                return $base_url . '?' . $url . $http_query;
                break;
            default:
                return;
                break;
        }

    }
}
/**
 * 实用工具类
 */
class Util
{

    /**
     * 缓存控制器数组
     * @var array
     */
    private static $_controller = array();

    /**
     * 生成Url
     * @param string $url Url
     * @param array $params 参数数组
     * @return void
     */
    public static function url($url = '', $params = array())
    {
        return View::buildUrl($url, $params);
    }

    /**
     * 包含模板文件
     * @param string $path 文件路径
     * @param array $data 需要传入的参数
     * @return void
     */
    public static function need($path, $data = array())
    {
        return View::includeTpl($path, $data);
    }

    /**
     * 调用控制器
     * @param string $controller 控制器名
     * @return class
     */
    public static function call($controllerName)
    {
        //判断是否实例化过，直接调用
        $controllerClass = $controllerName . 'Controller';
        if (isset(self::$_controller[$controllerClass])) {
            return self::$_controller[$controllerClass];
        }

        if (!class_exists($controllerClass)) {
            throw new Exception('请求的控制器：' . $controllerClass . '不存在');
        } else {
            $controller = new $controllerClass();
            self::$_controller[$controllerClass] = $controller;
            return $controller;
        }

    }
}

/**
 * 服务器处理类
 */
class Request
{
    /**
     * 获取参数
     * @var string
     */
    private static $_put = null;

    /**
     * 获取输入参数 支持过滤和默认值
     * 使用方法:
     * <code>
     * I('id',0); 获取id参数 自动判断get或者post
     * I('post.name','','htmlspecialchars'); 获取$_POST['name']
     * I('get.'); 获取$_GET
     * </code>
     * @param string $name 变量的名称 支持指定类型
     * @param mixed $default 不存在的时候默认值
     * @param mixed $filter 参数过滤方法
     * @param mixed $datas 要获取的额外数据源
     * @return mixed
     */
    public static function input($name, $default = '', $filter = null, $datas = null)
    {
        if (strpos($name, '/')) {
            // 指定修饰符
            list($name, $type) = explode('/', $name, 2);
        } else {
            // 默认强制转换为字符串
            $type = 's';
        }
        if (strpos($name, '.')) {
            // 指定参数来源
            list($method, $name) = explode('.', $name, 2);
        } else {
            // 默认为自动判断
            $method = 'param';
        }
        switch (strtolower($method)) {
            case 'get':
                $input = &$_GET;
                break;
            case 'post':
                $input = &$_POST;
                break;
            case 'put':
                if (is_null(self::$_put)) {
                    parse_str(file_get_contents('php://input'), self::$_put);
                }
                $input = self::$_put;
                break;
            case 'param':
                switch ($_SERVER['REQUEST_METHOD']) {
                    case 'POST':
                        $input = $_POST;
                        break;
                    case 'PUT':
                        if (is_null(self::$_put)) {
                            parse_str(file_get_contents('php://input'), self::$_put);
                        }
                        $input = self::$_put;
                        break;
                    default:
                        $input = $_GET;
                }
                break;
            case 'path':
                $input = array();
                if (!empty($_SERVER['PATH_INFO'])) {
                    $depr = '/';
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
        if ('' == $name) {
            // 获取全部变量
            $data = $input;
            $filters = isset($filter) ? $filter : 'htmlspecialchars';
            if ($filters) {
                if (is_string($filters)) {
                    $filters = explode(',', $filters);
                }
                foreach ($filters as $filter) {
                    $data = self::array_map_recursive($filter, $data); // 参数过滤
                }
            }
        } elseif (isset($input[$name])) {
            // 取值操作
            $data = $input[$name];
            $filters = isset($filter) ? $filter : 'htmlspecialchars';
            if ($filters) {
                if (is_string($filters)) {
                    if (0 === strpos($filters, '/') && 1 !== preg_match($filters, (string) $data)) {
                        // 支持正则验证
                        return isset($default) ? $default : null;
                    } else {
                        $filters = explode(',', $filters);
                    }
                } elseif (is_int($filters)) {
                    $filters = array($filters);
                }

                if (is_array($filters)) {
                    foreach ($filters as $filter) {
                        if (function_exists($filter)) {
                            $data = is_array($data) ? self::array_map_recursive($filter, $data) : $filter($data); // 参数过滤
                        } else {
                            $data = filter_var($data, is_int($filter) ? $filter : filter_id($filter));
                            if (false === $data) {
                                return isset($default) ? $default : null;
                            }
                        }
                    }
                }
            }
            if (!empty($type)) {
                switch (strtolower($type)) {
                    case 'a': // 数组
                        $data = (array) $data;
                        break;
                    case 'd': // 数字
                        $data = (int) $data;
                        break;
                    case 'f': // 浮点
                        $data = (float) $data;
                        break;
                    case 'b': // 布尔
                        $data = (boolean) $data;
                        break;
                    case 's': // 字符串
                    default:
                        $data = (string) $data;
                }
            }
        } else {
            // 变量默认值
            $data = isset($default) ? $default : null;
        }
        is_array($data) && array_walk_recursive($data, array('Request', 'filter'));
        return $data;

    }

    /**
     * 回调函数
     * @param string $filter 过滤方法
     * @param $data mixed 源数据
     * @return mixed
     */
    private static function array_map_recursive($filter, $data)
    {
        $result = array();
        foreach ($data as $key => $val) {
            $result[$key] = is_array($val)
            ? self::array_map_recursive($filter, $val)
            : call_user_func($filter, $val);
        }
        return $result;
    }

    /**
     * 其他安全过滤
     * @param  $value Value
     * @return void
     */
    public static function filter(&$value)
    {
        // 过滤查询特殊字符
        if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
            $value .= ' ';
        }
    }

    /**
     * 判断是否SSL 用于生成Url
     * @return boolean
     */
    public static function isSecure()
    {
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }
        return false;
    }

    /**
     * 获取根地址
     * @return string
     */
    public static function getBaseUrl()
    {
        if (isset($_SERVER['HTTP_HOST']) && preg_match('/^((\[[0-9a-f:]+\])|(\d{1,3}(\.\d{1,3}){3})|[a-z0-9\-\.]+)(:\d+)?$/i', $_SERVER['HTTP_HOST'])) {
            $base_url = (self::isSecure() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
            . substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
        } else {
            $base_url = 'http://localhost/';
        }
        return rtrim($base_url, '/') . '/';
    }

    /**
     * 判断是否为get
     * @return boolean
     */
    public static function isGet()
    {
        return 'GET' == $_SERVER['REQUEST_METHOD'];
    }

    /**
     * 判断是否为post
     * @return boolean
     */
    public static function isPost()
    {
        return 'POST' == $_SERVER['REQUEST_METHOD'];
    }

    /**
     * 判断是否为put
     * @return boolean
     */
    public static function isPut()
    {
        return 'PUT' == $_SERVER['REQUEST_METHOD'];
    }

    /**
     * 判断是否为ajax
     * @return boolean
     */
    public static function isAjax()
    {
        return ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) ? true : false;
    }
}

class Response
{
    /**
     * 状态码数组
     * @var array
     */
    private static $_httpCode = array(
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
     * 设置状态码
     * @param int $code 状态码
     * @param string $text 自定义文本
     * @return void
     */
    public static function setStatus($code = 200, $text = '')
    {
        if (empty($code) or !is_numeric($code)) {
            Handle::halt('状态码不正确喔~', 500);
        }

        if (empty($text)) {
            is_int($code) or $code = (int) $code;

            if (isset(self::$_httpCode[$code])) {
                $text = self::$_httpCode[$code];
            } else {
                Handle::halt('状态码不规范或者没有指定文本内容。', 500);
            }
        }

        if (strpos(PHP_SAPI, 'cgi') === 0) {
            header('Status: ' . $code . ' ' . $text, true);
        } else {
            $server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
            header($server_protocol . ' ' . $code . ' ' . $text, true, $code);
        }
    }

    /**
     * 设置http头
     * @param string $name 名称
     * @param string $value 对应值
     * @return void
     */
    public static function setHeader($name, $value)
    {
        header($name . ': ' . $value, true);
    }

    /**
     * 抛出json回执信息
     *
     * @access public
     * @param string $data 消息体
     * @return void
     */
    public static function throwJson($data)
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }

    /**
     * 重定向函数
     * @param string $location 重定向路径
     * @param boolean $isPermanently 是否为永久重定向
     * @return void
     */
    public static function redirect($location, $isPermanently = false)
    {
        if ($isPermanently) {
            header('Location: ' . $location, false, 301);
            exit;
        } else {
            header('Location: ' . $location, false, 302);
            exit;
        }
    }

}

/*!
 * Medoo database framework
 * http://medoo.in
 * Version 0.9.8.3
 *
 * Copyright 2015, Angel Lai
 * Released under the MIT license
 */
class medoo
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
    protected $option = array();
    // Variable
    protected $logs = array();
    protected $debug_mode = false;
    protected $_instance = array();

    public function __construct($options = null)
    {
        try {
            $commands = array();
            if (is_string($options) && !empty($options)) {
                if (strtolower($this->database_type) == 'sqlite') {
                    $this->database_file = $options;
                } else {
                    $this->database_name = $options;
                }
            } elseif (is_array($options)) {
                foreach ($options as $option => $value) {
                    $this->$option = $value;
                }
            }
            if (
                isset($this->port) &&
                is_int($this->port * 1)
            ) {
                $port = $this->port;
            }
            $type = strtolower($this->database_type);
            $is_port = isset($port);
            switch ($type) {
                case 'mariadb':
                    $type = 'mysql';
                case 'mysql':
                    if ($this->socket) {
                        $dsn = $type . ':unix_socket=' . $this->socket . ';dbname=' . $this->database_name;
                    } else {
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
                    $dsn = $type . ':' . $this->database_file;
                    $this->username = null;
                    $this->password = null;
                    break;
            }
            if (
                in_array($type, explode(' ', 'mariadb mysql pgsql sybase mssql')) &&
                $this->charset
            ) {
                $commands[] = "SET NAMES '" . $this->charset . "'";
            }
            $this->pdo = new PDO(
                $dsn,
                $this->username,
                $this->password,
                $this->option
            );
            foreach ($commands as $value) {
                $this->pdo->exec($value);
            }
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function query($query)
    {
        if ($this->debug_mode) {
            echo $query;
            $this->debug_mode = false;
            return false;
        }
        array_push($this->logs, $query);
        Log::sql($this->last_query());
        return $this->pdo->query($query);
    }
    public function exec($query)
    {
        if ($this->debug_mode) {
            echo $query;
            $this->debug_mode = false;
            return false;
        }
        array_push($this->logs, $query);
        Log::sql($this->last_query());
        return $this->pdo->exec($query);
    }
    public function quote($string)
    {
        return $this->pdo->quote($string);
    }
    protected function column_quote($string)
    {
        return '"' . str_replace('.', '"."', preg_replace('/(^#|\(JSON\))/', '', $string)) . '"';
    }
    protected function column_push($columns)
    {
        if ($columns == '*') {
            return $columns;
        }
        if (is_string($columns)) {
            $columns = array($columns);
        }
        $stack = array();
        foreach ($columns as $key => $value) {
            preg_match('/([a-zA-Z0-9_\-\.]*)\s*\(([a-zA-Z0-9_\-]*)\)/i', $value, $match);
            if (isset($match[1], $match[2])) {
                array_push($stack, $this->column_quote($match[1]) . ' AS ' . $this->column_quote($match[2]));
            } else {
                array_push($stack, $this->column_quote($value));
            }
        }
        return implode($stack, ',');
    }
    protected function array_quote($array)
    {
        $temp = array();
        foreach ($array as $value) {
            $temp[] = is_int($value) ? $value : $this->pdo->quote($value);
        }
        return implode($temp, ',');
    }
    protected function inner_conjunct($data, $conjunctor, $outer_conjunctor)
    {
        $haystack = array();
        foreach ($data as $value) {
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
        foreach ($data as $key => $value) {
            $type = gettype($value);
            if (
                preg_match("/^(AND|OR)(\s+#.*)?$/i", $key, $relation_match) &&
                $type == 'array'
            ) {
                $wheres[] = 0 !== count(array_diff_key($value, array_keys(array_keys($value)))) ?
                '(' . $this->data_implode($value, ' ' . $relation_match[1]) . ')' :
                '(' . $this->inner_conjunct($value, ' ' . $relation_match[1], $conjunctor) . ')';
            } else {
                preg_match('/(#?)([\w\.]+)(\[(\>|\>\=|\<|\<\=|\!|\<\>|\>\<|\!?~)\])?/i', $key, $match);
                $column = $this->column_quote($match[2]);
                if (isset($match[4])) {
                    $operator = $match[4];
                    if ($operator == '!') {
                        switch ($type) {
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
                    if ($operator == '<>' || $operator == '><') {
                        if ($type == 'array') {
                            if ($operator == '><') {
                                $column .= ' NOT';
                            }
                            if (is_numeric($value[0]) && is_numeric($value[1])) {
                                $wheres[] = '(' . $column . ' BETWEEN ' . $value[0] . ' AND ' . $value[1] . ')';
                            } else {
                                $wheres[] = '(' . $column . ' BETWEEN ' . $this->quote($value[0]) . ' AND ' . $this->quote($value[1]) . ')';
                            }
                        }
                    }
                    if ($operator == '~' || $operator == '!~') {
                        if ($type == 'string') {
                            $value = array($value);
                        }
                        if (!empty($value)) {
                            $like_clauses = array();
                            foreach ($value as $item) {
                                if ($operator == '!~') {
                                    $column .= ' NOT';
                                }
                                if (preg_match('/^(?!%).+(?<!%)$/', $item)) {
                                    $item = '%' . $item . '%';
                                }
                                $like_clauses[] = $column . ' LIKE ' . $this->fn_quote($key, $item);
                            }
                            $wheres[] = implode(' OR ', $like_clauses);
                        }
                    }
                    if (in_array($operator, array('>', '>=', '<', '<='))) {
                        if (is_numeric($value)) {
                            $wheres[] = $column . ' ' . $operator . ' ' . $value;
                        } elseif (strpos($key, '#') === 0) {
                            $wheres[] = $column . ' ' . $operator . ' ' . $this->fn_quote($key, $value);
                        } else {
                            $wheres[] = $column . ' ' . $operator . ' ' . $this->quote($value);
                        }
                    }
                } else {
                    switch ($type) {
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
        if (is_array($where)) {
            $where_keys = array_keys($where);
            $where_AND = preg_grep("/^AND\s*#?$/i", $where_keys);
            $where_OR = preg_grep("/^OR\s*#?$/i", $where_keys);
            $single_condition = array_diff_key($where, array_flip(
                explode(' ', 'AND OR GROUP ORDER HAVING LIMIT LIKE MATCH')
            ));
            if ($single_condition != array()) {
                $where_clause = ' WHERE ' . $this->data_implode($single_condition, '');
            }
            if (!empty($where_AND)) {
                $value = array_values($where_AND);
                $where_clause = ' WHERE ' . $this->data_implode($where[$value[0]], ' AND');
            }
            if (!empty($where_OR)) {
                $value = array_values($where_OR);
                $where_clause = ' WHERE ' . $this->data_implode($where[$value[0]], ' OR');
            }
            if (isset($where['MATCH'])) {
                $MATCH = $where['MATCH'];
                if (is_array($MATCH) && isset($MATCH['columns'], $MATCH['keyword'])) {
                    $where_clause .= ($where_clause != '' ? ' AND ' : ' WHERE ') . ' MATCH ("' . str_replace('.', '"."', implode($MATCH['columns'], '", "')) . '") AGAINST (' . $this->quote($MATCH['keyword']) . ')';
                }
            }
            if (isset($where['GROUP'])) {
                $where_clause .= ' GROUP BY ' . $this->column_quote($where['GROUP']);
                if (isset($where['HAVING'])) {
                    $where_clause .= ' HAVING ' . $this->data_implode($where['HAVING'], ' AND');
                }
            }
            if (isset($where['ORDER'])) {
                $rsort = '/(^[a-zA-Z0-9_\-\.]*)(\s*(DESC|ASC))?/';
                $ORDER = $where['ORDER'];
                if (is_array($ORDER)) {
                    if (
                        isset($ORDER[1]) &&
                        is_array($ORDER[1])
                    ) {
                        $where_clause .= ' ORDER BY FIELD(' . $this->column_quote($ORDER[0]) . ', ' . $this->array_quote($ORDER[1]) . ')';
                    } else {
                        $stack = array();
                        foreach ($ORDER as $column) {
                            preg_match($rsort, $column, $order_match);
                            array_push($stack, '"' . str_replace('.', '"."', $order_match[1]) . '"' . (isset($order_match[3]) ? ' ' . $order_match[3] : ''));
                        }
                        $where_clause .= ' ORDER BY ' . implode($stack, ',');
                    }
                } else {
                    preg_match($rsort, $ORDER, $order_match);
                    $where_clause .= ' ORDER BY "' . str_replace('.', '"."', $order_match[1]) . '"' . (isset($order_match[3]) ? ' ' . $order_match[3] : '');
                }
            }
            if (isset($where['LIMIT'])) {
                $LIMIT = $where['LIMIT'];
                if (is_numeric($LIMIT)) {
                    $where_clause .= ' LIMIT ' . $LIMIT;
                }
                if (
                    is_array($LIMIT) &&
                    is_numeric($LIMIT[0]) &&
                    is_numeric($LIMIT[1])
                ) {
                    if ($this->database_type === 'pgsql') {
                        $where_clause .= ' OFFSET ' . $LIMIT[0] . ' LIMIT ' . $LIMIT[1];
                    } else {
                        $where_clause .= ' LIMIT ' . $LIMIT[0] . ',' . $LIMIT[1];
                    }
                }
            }
        } else {
            if ($where != null) {
                $where_clause .= ' ' . $where;
            }
        }
        return $where_clause;
    }
    protected function select_context($table, $join, &$columns = null, $where = null, $column_fn = null)
    {
        $table = '"' . $table . '"';
        $join_key = is_array($join) ? array_keys($join) : null;
        if (
            isset($join_key[0]) &&
            strpos($join_key[0], '[') === 0
        ) {
            $table_join = array();
            $join_array = array(
                '>' => 'LEFT',
                '<' => 'RIGHT',
                '<>' => 'FULL',
                '><' => 'INNER',
            );
            foreach ($join as $sub_table => $relation) {
                preg_match('/(\[(\<|\>|\>\<|\<\>)\])?([a-zA-Z0-9_\-]*)\s?(\(([a-zA-Z0-9_\-]*)\))?/', $sub_table, $match);
                if ($match[2] != '' && $match[3] != '') {
                    if (is_string($relation)) {
                        $relation = 'USING ("' . $relation . '")';
                    }
                    if (is_array($relation)) {
                        // For ['column1', 'column2']
                        if (isset($relation[0])) {
                            $relation = 'USING ("' . implode($relation, '", "') . '")';
                        } else {
                            $joins = array();
                            foreach ($relation as $key => $value) {
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
        } else {
            if (is_null($columns)) {
                if (is_null($where)) {
                    if (
                        is_array($join) &&
                        isset($column_fn)
                    ) {
                        $where = $join;
                        $columns = null;
                    } else {
                        $where = null;
                        $columns = $join;
                    }
                } else {
                    $where = $join;
                    $columns = null;
                }
            } else {
                $where = $columns;
                $columns = $join;
            }
        }
        if (isset($column_fn)) {
            if ($column_fn == 1) {
                $column = '1';
                if (is_null($where)) {
                    $where = $columns;
                }
            } else {
                if (empty($columns)) {
                    $columns = '*';
                    $where = $join;
                }
                $column = $column_fn . '(' . $this->column_push($columns) . ')';
            }
        } else {
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
        if (!isset($datas[0])) {
            $datas = array($datas);
        }
        foreach ($datas as $data) {
            $values = array();
            $columns = array();
            foreach ($data as $key => $value) {
                array_push($columns, $this->column_quote($key));
                switch (gettype($value)) {
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
            $this->exec('INSERT INTO "' . $table . '" (' . implode(', ', $columns) . ') VALUES (' . implode($values, ', ') . ')');
            $lastId[] = $this->pdo->lastInsertId();
        }
        return count($lastId) > 1 ? $lastId : $lastId[0];
    }
    public function update($table, $data, $where = null)
    {
        $fields = array();
        foreach ($data as $key => $value) {
            preg_match('/([\w]+)(\[(\+|\-|\*|\/)\])?/i', $key, $match);
            if (isset($match[3])) {
                if (is_numeric($value)) {
                    $fields[] = $this->column_quote($match[1]) . ' = ' . $this->column_quote($match[1]) . ' ' . $match[3] . ' ' . $value;
                }
            } else {
                $column = $this->column_quote($key);
                switch (gettype($value)) {
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
        return $this->exec('UPDATE "' . $table . '" SET ' . implode(', ', $fields) . $this->where_clause($where));
    }
    public function delete($table, $where)
    {
        return $this->exec('DELETE FROM "' . $table . '"' . $this->where_clause($where));
    }
    public function replace($table, $columns, $search = null, $replace = null, $where = null)
    {
        if (is_array($columns)) {
            $replace_query = array();
            foreach ($columns as $column => $replacements) {
                foreach ($replacements as $replace_search => $replace_replacement) {
                    $replace_query[] = $column . ' = REPLACE(' . $this->column_quote($column) . ', ' . $this->quote($replace_search) . ', ' . $this->quote($replace_replacement) . ')';
                }
            }
            $replace_query = implode(', ', $replace_query);
            $where = $search;
        } else {
            if (is_array($search)) {
                $replace_query = array();
                foreach ($search as $replace_search => $replace_replacement) {
                    $replace_query[] = $columns . ' = REPLACE(' . $this->column_quote($columns) . ', ' . $this->quote($replace_search) . ', ' . $this->quote($replace_replacement) . ')';
                }
                $replace_query = implode(', ', $replace_query);
                $where = $replace;
            } else {
                $replace_query = $columns . ' = REPLACE(' . $this->column_quote($columns) . ', ' . $this->quote($search) . ', ' . $this->quote($replace) . ')';
            }
        }
        return $this->exec('UPDATE "' . $table . '" SET ' . $replace_query . $this->where_clause($where));
    }
    public function get($table, $join = null, $column = null, $where = null)
    {
        $query = $this->query($this->select_context($table, $join, $column, $where) . ' LIMIT 1');
        if ($query) {
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            if (isset($data[0])) {
                $column = $where == null ? $join : $column;
                if (is_string($column) && $column != '*') {
                    return $data[0][$column];
                }
                return $data[0];
            } else {
                return false;
            }
        } else {
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
        if ($query) {
            $max = $query->fetchColumn();
            return is_numeric($max) ? $max + 0 : $max;
        } else {
            return false;
        }
    }
    public function min($table, $join, $column = null, $where = null)
    {
        $query = $this->query($this->select_context($table, $join, $column, $where, 'MIN'));
        if ($query) {
            $min = $query->fetchColumn();
            return is_numeric($min) ? $min + 0 : $min;
        } else {
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
            'server' => 'SERVER_INFO',
            'driver' => 'DRIVER_NAME',
            'client' => 'CLIENT_VERSION',
            'version' => 'SERVER_VERSION',
            'connection' => 'CONNECTION_STATUS',
        );
        foreach ($output as $key => $value) {
            $output[$key] = $this->pdo->getAttribute(constant('PDO::ATTR_' . $value));
        }
        return $output;
    }
}

/**
 * 日志类
 * 保存路径为 App/Log，按天存放
 */
class Log
{
    /**
     * 打日志，支持SAE环境
     * @param string $msg 日志内容
     * @param string $level 日志等级
     */
    private static function write($msg, $level = '')
    {
        if (Config::get('APP_DEBUG') == false) {
            return;
        }
        if (function_exists('saeAutoLoader')) {
            //如果是SAE，则使用sae_debug函数打日志
            $msg = "[{$level}]" . $msg;
            sae_set_display_errors(false);
            sae_debug(trim($msg));
            sae_set_display_errors(true);
        } else {
            $msg = date('[ Y-m-d H:i:s ]') . "[{$level}]" . $msg . "\r\n";
            $logPath = Config::get('APP_FULL_PATH') . '/Log/' . date('Ymd') . '.log';
            file_put_contents($logPath, $msg, FILE_APPEND);
        }
    }

    /**
     * 打印非SQL日志
     * @param string $msg 日志信息
     */
    public static function normal($msg)
    {
        self::write($msg, 'NORMAL');
    }

    /**
     * 打印sql日志
     * @param string $msg 日志信息
     */
    public static function sql($msg)
    {
        self::write($msg, 'SQL');
    }
}
