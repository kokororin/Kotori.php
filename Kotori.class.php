<?php
/**
 * 获取和设置配置参数 支持批量定义
 * 如果$key是关联型数组，则会按K-V的形式写入配置
 * 如果$key是数字索引数组，则返回对应的配置数组
 * @param string|array $key 配置变量
 * @param array|null $value 配置值
 * @return array|null
 */
function C($key, $value = null)
{
    static $_config = array();
    $args = func_num_args();
    if ($args == 1) {
        if (is_string($key)) {
            //如果传入的key是字符串
            return isset($_config[$key]) ? $_config[$key] : null;
        }
        if (is_array($key)) {
            if (array_keys($key) !== range(0, count($key) - 1)) {
                //如果传入的key是关联数组
                $_config = array_merge($_config, $key);
            } else {
                $ret = array();
                foreach ($key as $k) {
                    $ret[$k] = isset($_config[$k]) ? $_config[$k] : null;
                }
                return $ret;
            }
        }
    } else {
        if (is_string($key)) {
            $_config[$key] = $value;
        } else {
            halt('配置出错啦~');
        }
    }
    return null;
}

/**
 * 输出错误并终止程序
 * @param string $str 出错原因
 * @return void
 */
function halt($str)
{
    $html = '<!DOCTYPE html> <html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN" prefix="og: http://ogp.me/ns#"> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> <title>发生错误辣！</title> <style type="text/css"> html { background: #f1f1f1; } body { background: #fff; color: #444; font-family: "Open Sans", sans-serif; margin: 2em auto; padding: 1em 2em; max-width: 700px; -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13); box-shadow: 0 1px 3px rgba(0,0,0,0.13); } h1 { border-bottom: 1px solid #dadada; clear: both; color: #666; font: 24px "Open Sans", sans-serif; margin: 30px 0 0 0; padding: 0; padding-bottom: 7px; } #error-page { margin-top: 50px; } #error-page p { font-size: 14px; line-height: 1.5; margin: 25px 0 20px; } #error-page code { font-family: Consolas, Monaco, monospace; } ul li { margin-bottom: 10px; font-size: 14px ; } a { color: #21759B; text-decoration: none; } a:hover { color: #D54E21; } .button { background: #f7f7f7; border: 1px solid #cccccc; color: #555; display: inline-block; text-decoration: none; font-size: 13px; line-height: 26px; height: 28px; margin: 0; padding: 0 10px 1px; cursor: pointer; -webkit-border-radius: 3px; -webkit-appearance: none; border-radius: 3px; white-space: nowrap; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; -webkit-box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0,0,0,.08); box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0,0,0,.08); vertical-align: top; } .button.button-large { height: 29px; line-height: 28px; padding: 0 12px; } .button:hover, .button:focus { background: #fafafa; border-color: #999; color: #222; } .button:focus { -webkit-box-shadow: 1px 1px 1px rgba(0,0,0,.2); box-shadow: 1px 1px 1px rgba(0,0,0,.2); } .button:active { background: #eee; border-color: #999; color: #333; -webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 ); box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 ); } </style> </head> <body id="error-page"> <h1>发生错误辣！</h1><p>' . $str . '</p></p></body> </html>';
    exit($html);
}

/**
 * 框架自定义错误
 * @param int $errno 错误号
 * @param string $errstr 错误信息
 * @param string $errfile 错误文件
 * @param int $errline 错误行号
 * @return void
 */
function kotori_error($errno, $errstr, $errfile, $errline)
{
    $text = '<b>信息：</b>' . $errstr . '<br>' . '<b>行数：</b>' . $errline . '<br>' . '<b>文件：</b>' . $errfile;
    $txt = '错误类型：' . $errno . ' 信息：' . $errstr . ' 行数：' . $errline . ' 文件：' . $errfile;
    Log::normal($txt);
    halt($text);

}

/**
 * 框架自定义异常
 * @param string $exception 异常信息
 * @return void
 */
function kotori_exception($exception)
{
    $text = '<b>异常：</b>' . $exception->getMessage();
    $txt = '错误类型：Exception' . ' 信息：' . $exception->getMessage();
    Log::normal($txt);
    halt($text);
}

/**
 * 框架自定义致命错误
 * @return void
 */
function kotori_end()
{
    if (error_get_last()) {
        $arr = error_get_last();
        $text = '<b>信息：</b>' . $arr['message'] . '<br>' . '<b>行数：</b>' . $arr['line'] . '<br>' . '<b>文件：</b>' . $arr['file'];
        $txt = '错误类型：' . 'FATAL!' . ' 信息：' . $arr['message'] . ' 行数：' . $arr['line'] . ' 文件：' . $arr['file'];
        Log::normal($txt);
        halt($text);
    }
}

/**
 * 获取数据库实例
 * @return DB
 */
function M()
{
    $dbConf = C(array('DB_HOST', 'DB_USER', 'DB_PWD', 'DB_NAME'));
    return DB::getInstance($dbConf);
}

/**
 * 调用控制器
 * @param string $name 控制器名
 * @return class
 */
function A($name)
{
    $name = $name . 'Controller';
    return Kotori::call($name);
}

/**
 * 生成Url
 * @param string $url Url
 * @param array $params 参数数组
 * @return void
 */
function U($url = '', $params = array())
{
    return View::url($url, $params);
}

/**
 * 包含模板文件
 * @param string $path 文件路径
 * @param array $data 需要传入的参数
 * @return void
 */
function N($path, $data = array())
{
    View::need($path, $data);
}

/**
 * 快捷生成link,script标签
 * @param string $type 类型
 * @param string $fiels文件 逗号分隔
 * @param string $base 根路径
 * @return void
 */
function L($type, $files, $base)
{
    $file_arr = explode(',', $files);
    switch ($type) {
        case 'css':
            foreach ($file_arr as $value) {
                echo '<link rel="stylesheet" href="' . U($base . '/' . $value . '.css') . '"/>';
            }
            break;
        case 'js':
            echo '<script src="' . U($base . '/' . $value . '.js') . '"></script>';
            break;
        default:
            return;
    }
}

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
function I($name, $default = '', $filter = null, $datas = null)
{
    static $_PUT = null;
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
            if (is_null($_PUT)) {
                parse_str(file_get_contents('php://input'), $_PUT);
            }
            $input = $_PUT;
            break;
        case 'param':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input = $_POST;
                    break;
                case 'PUT':
                    if (is_null($_PUT)) {
                        parse_str(file_get_contents('php://input'), $_PUT);
                    }
                    $input = $_PUT;
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
                $data = array_map_recursive($filter, $data); // 参数过滤
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
                        $data = is_array($data) ? array_map_recursive($filter, $data) : $filter($data); // 参数过滤
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
    is_array($data) && array_walk_recursive($data, 'kotori_filter');
    return $data;

}

/**
 * 回调函数
 * @param string $filter 过滤方法
 * @param $data mixed 源数据
 * @return mixed
 */
function array_map_recursive($filter, $data)
{
    $result = array();
    foreach ($data as $key => $val) {
        $result[$key] = is_array($val)
        ? array_map_recursive($filter, $val)
        : call_user_func($filter, $val);
    }
    return $result;
}

/**
 * 其他安全过滤
 * @param  $value Value
 * @return void
 */
function kotori_filter(&$value)
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
function is_https()
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
 * 优化的require 区分大小写
 * @param string $path 文件路径
 * @return boolean
 */
function kotori_require($path)
{
    static $_importFiles = array();

    if (!isset($_importFiles[$path])) {
        if (kotori_file_exists($path)) {
            require $path;
            $_importFiles[$path] = true;
        } else {
            $_importFiles[$path] = false;
        }
    }
    return $_importFiles[$path];

}

/**
 * 区分大小写的文件存在判断
 * @param string $path 文件路径
 * @return boolean
 */
function kotori_file_exists($path)
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

/**
 * Kotori核心类
 */
class Kotori
{
    /**
     * 控制器
     * @var string
     */
    private $c;
    /**
     * Action
     * @var string
     */
    private $a;
    /**
     * 单例
     * @var Kotori
     */
    private static $_instance;

    /**
     * 构造函数，初始化配置
     * @param array $conf
     */
    private function __construct($conf)
    {
        C($conf);
    }
    private function __clone()
    {}

    /**
     * 获取单例
     * @param array $conf
     * @return Kotori
     */
    public static function getInstance($conf)
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($conf);
        }
        return self::$_instance;
    }
    /**
     * 运行应用实例
     * @access public
     * @return void
     */
    public function run()
    {
        error_reporting(0);
        set_error_handler('kotori_error');
        set_exception_handler('kotori_exception');
        register_shutdown_function('kotori_end');
        if (C('USE_SESSION') == true) {
            session_start();
        }
        C('APP_FULL_PATH', dirname(__FILE__) . '/' . C('APP_PATH'));
        kotori_require(C('APP_FULL_PATH') . '/common.php');
        spl_autoload_register(array('Kotori', 'autoload'));
        if (isset($_SERVER['PATH_INFO'])) {
            $pathInfo = $_SERVER['PATH_INFO'];
        } else {
            if (isset($_SERVER['ORIG_PATH_INFO'])) {
                $pathInfo = $_SERVER['ORIG_PATH_INFO'];
            } else {
                if (isset($_SERVER['REDIRECT_PATH_INFO'])) {
                    $pathInfo = $_SERVER['REDIRECT_PATH_INFO'];
                } else {
                    $pathInfo = '';
                }
            }
        }

        $pathInfoArr = ($pathInfo != '') ? explode('/', trim($pathInfo, '/')) : array();

        if (isset($pathInfoArr[0]) && $pathInfoArr[0] !== '') {
            $this->c = $pathInfoArr[0];
        } else {
            $this->c = 'Index';
        }
        if (isset($pathInfoArr[1])) {
            $this->a = $pathInfoArr[1];
        } else {
            $this->a = 'index';
        }
        define('CONTROLLER_NAME', $this->c);
        define('ACTION_NAME', $this->a);
        unset($pathInfoArr[0], $pathInfoArr[1]);
        $controllerClass = $this->c . 'Controller';

        $controller = self::call($controllerClass);

        if (!method_exists($controller, $this->a)) {
            throw new Exception('请求的方法：' . $this->a . '不存在');
        }

        $params = array();
        //源自ThinkPHP
        preg_replace_callback('/(\w+)\/([^\/]+)/', function ($match) use (&$params) {$params[$match[1]] = strip_tags($match[2]);}, implode('/', $pathInfoArr));
        $_GET = array_merge($params, $_GET);
        //以下来自http://jingyan.todgo.com/jiaoyu/1883184mab.html
        call_user_func_array(array($controller, $this->a), $params);
    }

    /**
     * 调用控制器核心方法
     * @param string $controllerClass 控制器名
     * @return class
     */
    public static function call($controllerClass)
    {
        //kotori_require(C('APP_FULL_PATH') . '/Controller/' . $controllerClass . '.class.php');
        if (!class_exists($controllerClass)) {
            throw new Exception('请求的控制器：' . $controllerClass . '不存在');
        }
        $controller = new $controllerClass();
        return new $controllerClass();

    }

    /**
     * 自动加载函数
     * @param string $class 类名
     */
    public static function autoload($class)
    {
        if (substr($class, -10) == 'Controller') {
            kotori_require(C('APP_FULL_PATH') . '/Controller/' . $class . '.class.php');
        } else {
            kotori_require(C('APP_FULL_PATH') . '/Lib/' . $class . '.class.php');
        }
    }
}

/**
 * 控制器类
 */
class Controller
{
    /**
     * 视图实例
     * @var View
     */
    private $_view;

    /**
     * 构造函数，初始化视图实例，调用hook
     */
    public function __construct()
    {
        $this->_view = new View();
        $this->_init();
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
     * 将数据用json格式输出至浏览器，并停止执行代码
     * @param array $data 要输出的数据
     */
    protected function ajaxReturn($data)
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }
    /**
     * 重定向至指定url
     * @param string $url 要跳转的url
     * @param void
     */
    protected function redirect($url)
    {
        header("Location: $url");
        exit;
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
            $this->_tplDir = C('APP_FULL_PATH') . '/View/';
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
    public static function need($path, $data = array())
    {
        self::$tmpData = array(
            'path' => C('APP_FULL_PATH') . '/View/' . $path . '.html',
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
    public static function url($url = '', $params = array())
    {
        if (isset($_SERVER['HTTP_HOST']) && preg_match('/^((\[[0-9a-f:]+\])|(\d{1,3}(\.\d{1,3}){3})|[a-z0-9\-\.]+)(:\d+)?$/i', $_SERVER['HTTP_HOST'])) {
            $base_url = (is_https() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
            . substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
        } else {
            $base_url = 'http://localhost/';
        }
        if (!empty($params)) {
            $keys = array_keys($params);
            $values = array_values($params);
            $count = count($params);
            $http_query = '/';
            for ($i = 0; $i < $count; $i++) {
                $http_query .= $keys[$i] . '/' . $values[$i];
                if ($i != ($count - 1)) {
                    $http_query .= '/';
                }
            }

        } else {
            $http_query = '';
        }
        return $base_url . $url . $http_query;

    }
}

/*
 * PHP-PDO-MySQL-Class
 * https://github.com/lincanbin/PHP-PDO-MySQL-Class
 *
 * Copyright 2015, Lin Canbin
 * http://www.94cb.com/
 *
 * Licensed under the Apache License, Version 2.0:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * A PHP MySQL PDO class similar to the the Python MySQLdb.
 */
class DB
{
    private $Host;
    private $DBName;
    private $DBUser;
    private $DBPassword;
    private $pdo;
    private $sQuery;
    private $bConnected = false;
    private $parameters;
    public $rowCount = 0;
    public $columnCount = 0;
    public $querycount = 0;
    //单例模式
    private static $_instance = array();

    public function __construct($options = null)
    {
        $this->Host = $options['DB_HOST'];
        $this->DBName = $options['DB_NAME'];
        $this->DBUser = $options['DB_USER'];
        $this->DBPassword = $options['DB_PWD'];
        $this->Connect();
        $this->parameters = array();
    }

    public static function getInstance($dbConf)
    {
        $key = $dbConf['DB_HOST'];
        if (!isset(self::$_instance[$key]) || !(self::$_instance[$key] instanceof self)) {
            self::$_instance[$key] = new self($dbConf);
        }
        return self::$_instance[$key];
    }

    private function Connect()
    {
        try {
            $this->pdo = new PDO('mysql:dbname=' . $this->DBName . ';host=' . $this->Host . ';charset=utf8',
                $this->DBUser,
                $this->DBPassword,
                array(
                    //For PHP 5.3.6 or lower
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    PDO::ATTR_EMULATE_PREPARES => false,
                    //长连接
                    //PDO::ATTR_PERSISTENT => true,

                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                )
            );
            /*
            //For PHP 5.3.6 or lower
            $this->pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //$this->pdo->setAttribute(PDO::ATTR_PERSISTENT, true);//长连接
            $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
             */
            $this->bConnected = true;

        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function CloseConnection()
    {
        $this->pdo = null;
    }

    private function Init($query, $parameters = "")
    {
        if (!$this->bConnected) {
            $this->Connect();
        }
        try {
            $this->parameters = $parameters;
            $this->sQuery = $this->pdo->prepare($this->BuildParams($query, $this->parameters));

            if (!empty($this->parameters)) {
                if (array_key_exists(0, $parameters)) {
                    $parametersType = true;
                    array_unshift($this->parameters, "");
                    unset($this->parameters[0]);
                } else {
                    $parametersType = false;
                }
                foreach ($this->parameters as $column => $value) {
                    $this->sQuery->bindParam($parametersType ? intval($column) : ":" . $column, $this->parameters[$column]); //It would be query after loop end(before 'sQuery->execute()').It is wrong to use $value.
                }
            }

            $this->succes = $this->sQuery->execute();
            $this->querycount++;
            Log::sql($this->DebugQuery($query, $parameters));
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }

        $this->parameters = array();
    }

    private function BuildParams($query, $params = null)
    {
        if (!empty($params)) {
            $rawStatement = explode(" ", $query);
            foreach ($rawStatement as $value) {
                if (strtolower($value) == 'in') {
                    return str_replace("(?)", "(" . implode(",", array_fill(0, count($params), "?")) . ")", $query);
                }
            }
        }
        return $query;
    }

    public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $query = trim($query);
        $rawStatement = explode(" ", $query);
        $this->Init($query, $params);
        $statement = strtolower($rawStatement[0]);
        if ($statement === 'select' || $statement === 'show') {
            return $this->sQuery->fetchAll($fetchmode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } else {
            return null;
        }
    }

    public function count($query, $params = null)
    {
        return count($this->query($query, $params));
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function column($query, $params = null)
    {
        $this->Init($query, $params);
        $resultColumn = $this->sQuery->fetchAll(PDO::FETCH_COLUMN);
        $this->rowCount = $this->sQuery->rowCount();
        $this->columnCount = $this->sQuery->columnCount();
        $this->sQuery->closeCursor();
        return $resultColumn;
    }
    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $this->Init($query, $params);
        $resultRow = $this->sQuery->fetch($fetchmode);
        $this->rowCount = $this->sQuery->rowCount();
        $this->columnCount = $this->sQuery->columnCount();
        $this->sQuery->closeCursor();
        return $resultRow;
    }

    public function single($query, $params = null)
    {
        $this->Init($query, $params);
        return $this->sQuery->fetchColumn();
    }

    private function DebugQuery($query, $params = null)
    {
        $keys = array();
        $values = array();
        if ($params == null) {
            return $query;
        }
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }
            if (is_numeric($value)) {
                $values[] = intval($value);
            } else {
                $values[] = '"' . $value . '"';
            }
            $query = preg_replace($keys, $values, $query, 1, $count);
            return $query;
        }
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
    public static function write($msg, $level = '')
    {
        if (function_exists('sae_debug')) {
            //如果是SAE，则使用sae_debug函数打日志
            $msg = "[{$level}]" . $msg;
            sae_set_display_errors(false);
            sae_debug(trim($msg));
            sae_set_display_errors(true);
        } else {
            $msg = date('[ Y-m-d H:i:s ]') . "[{$level}]" . $msg . "\r\n";
            $logPath = C('APP_FULL_PATH') . '/Log/' . date('Ymd') . '.log';
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
