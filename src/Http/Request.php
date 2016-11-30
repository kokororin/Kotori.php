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
 * Request Class
 *
 * @package     Kotori
 * @subpackage  Http
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Http;

use Kotori\Debug\Hook;

class Request
{
    /**
     * Params
     *
     * @var string
     */
    protected $_put = null;

    /**
     * Ip address
     *
     * @var array
     */
    protected $_ip = null;

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
     * Initialize Request.
     *
     * @return void
     */
    public function __construct()
    {
        Hook::listen(__CLASS__);
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
        if (strpos($name, '/')) {
            list($name, $type) = explode('/', $name, 2);
        } else {
            $type = 's';
        }
        if (strpos($name, '.')) {
            list($method, $name) = explode('.', $name, 2);
        } else {
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
                if (is_null($this->_put)) {
                    parse_str(file_get_contents('php://input'), $this->_put);
                }
                $input = $this->_put;
                break;
            case 'param':
                switch ($_SERVER['REQUEST_METHOD']) {
                    case 'POST':
                        $input = $_POST;
                        break;
                    case 'PUT':
                        if (is_null($this->_put)) {
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
            $data = $input;
            $filters = isset($filter) ? $filter : 'htmlspecialchars';
            if ($filters) {
                if (is_string($filters)) {
                    $filters = explode(',', $filters);
                }
                foreach ($filters as $filter) {
                    $data = $this->array_map_recursive($filter, $data); // 参数过滤
                }
            }
        } elseif (isset($input[$name])) {
            $data = $input[$name];
            $filters = isset($filter) ? $filter : 'htmlspecialchars';
            if ($filters) {
                if (is_string($filters)) {
                    if (0 === strpos($filters, '/') && 1 !== preg_match($filters, (string) $data)) {
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
                            $data = is_array($data) ? $this->array_map_recursive($filter, $data) : $filter($data);
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
        } else {
            // default
            $data = isset($default) ? $default : null;
        }
        is_array($data) && array_walk_recursive($data, array('Request', 'filter'));
        return $data;
    }

    /**
     * Callback Function
     *
     * @param string $filter Filter
     * @param $data mixed Orginal data
     * @return mixed
     */
    protected function array_map_recursive($filter, $data)
    {
        $result = array();
        foreach ($data as $key => $val) {
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
        if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
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
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO']) {
            return true;
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
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
        if (isset($_SERVER['HTTP_HOST']) && preg_match('/^((\[[0-9a-f:]+\])|(\d{1,3}(\.\d{1,3}){3})|[a-z0-9\-\.]+)(:\d+)?$/i', $_SERVER['HTTP_HOST'])) {
            $base_url = (Request::getSoul()->isSecure() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
            . substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
        } else {
            $base_url = 'http://localhost/';
        }
        return rtrim($base_url, '/') . '/';
    }

    /**
     * Returns Client Ip Address
     *
     * @param  integer $type Ip address or ipv4 address
     * @return string
     */
    public function getClientIp($type = 0)
    {
        $type = $type ? 1 : 0;

        if ($this->_ip !== null) {
            return $this->_ip[$type];
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }

            $this->_ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $this->_ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $this->_ip = $_SERVER['REMOTE_ADDR'];
        }

        // Check ip
        $long = sprintf("%u", ip2long($this->_ip));
        $this->_ip = $long ? array($this->_ip, $long) : array('0.0.0.0', 0);
        return $this->_ip[$type];
    }

    /**
     * Returns Http Host
     *
     * @return string
     */
    public function getHostName()
    {
        $possibleHostSources = array('HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR');
        $sourceTransformations = array(
            "HTTP_X_FORWARDED_HOST" => function ($value) {
                $elements = explode(',', $value);
                return trim(end($elements));
            },
        );
        $host = '';
        foreach ($possibleHostSources as $source) {
            if (!empty($host)) {
                break;
            }

            if (empty($_SERVER[$source])) {
                continue;
            }

            $host = $_SERVER[$source];
            if (array_key_exists($source, $sourceTransformations)) {
                $host = $sourceTransformations[$source]($host);
            }
        }

        // Remove port number from host
        $host = preg_replace('/:\d+$/', '', $host);

        return trim($host);
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

    /**
     * Is CLI?
     *
     * Test to see if a request was made from the command line.
     *
     * @return  boolean
     */
    public function isCli()
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * Detect whether user agent is Mobile
     *
     * @return boolean
     */
    public function isMobile()
    {
        $userAgent = isset($_SERVER['USER_AGENT']) ? $_SERVER['USER_AGENT'] : null;
        if ($userAgent == null) {
            return false;
        }
        return preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $userAgent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($userAgent, 0, 4));
    }
}
