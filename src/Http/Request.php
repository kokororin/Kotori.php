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
 * Request Class
 *
 * @package     Kotori
 * @subpackage  Http
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Http;

use Kotori\Core\Container;
use Kotori\Debug\Hook;
use Kotori\Exception\NotFoundException;

class Request
{
    /**
     * Ip address
     *
     * @var mixed
     */
    protected $ip = null;

    /**
     * Http headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Session has been started or not
     *
     * @var boolean
     */
    protected $hasSessionStarted = false;

    /**
     * Class constructor
     *
     * Initialize Request.
     */
    public function __construct()
    {
        Hook::listen(__CLASS__);
    }

    /**
     * Internal method used to retrieve values from given arrays.
     *
     * @param  array $source
     * @param  mixed $key
     * @param  mixed $default
     * @return mixed
     */
    protected function getRequestParams(&$source, $key = null, $default = null)
    {
        // If $key is NULL, it means that the whole $source is requested
        if (!isset($key) || $key == null) {
            $key = array_keys($source);
        }

        if (is_array($key)) {
            $output = [];
            foreach ($key as $k) {
                $output[$k] = $this->getRequestParams($source, $k);
            }

            return $output;
        }

        if (isset($source[$key])) {
            $value = $source[$key];
        } else {
            return $default;
        }

        return $value;

    }

    /**
     * Fetch an item from the GET array
     *
     * @param  mixed $key
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return $this->getRequestParams($_GET, $key, $default);
    }

    /**
     * Fetch an item from the POST array
     *
     * @param  mixed $key
     * @param  mixed $default
     * @return mixed
     */
    public function post($key = null, $default = null)
    {
        $rawPostData = file_get_contents('php://input');
        $source = json_decode($rawPostData, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            $source = $_POST;
        }

        return $this->getRequestParams($source, $key, $default);
    }

    /**
     * Fetch an item from the SERVER array
     *
     * @param  mixed $key
     * @param  mixed $default
     * @return mixed
     */
    public function server($key = null, $default = null)
    {
        return $this->getRequestParams($_SERVER, $key, $default);
    }

    /**
     * Set or Get cookie
     *
     * @param  mixed  $key
     * @param  string $value
     * @param  mixed  $options
     * @return mixed
     */
    public function cookie($key = '', $value = '', $options = null)
    {
        $defaultOptions = [
            'prefix' => '',
            'expire' => 86400,
            'path' => '/',
            'secure' => false,
            'httponly' => false,
        ];

        if (!is_null($options)) {
            if (is_numeric($options)) {
                $options = ['expire' => $options];
            } elseif (is_string($options)) {
                parse_str($options, $options);
            }

            $options = array_merge($defaultOptions, array_change_key_case($options));
        }

        if (!empty($options['httponly'])) {
            ini_set('session.cookie_httponly', 1);
        }

        if (is_null($key)) {
            if (empty($_COOKIE)) {
                return null;
            }

            $prefix = empty($value) ? $options['prefix'] : $value;
            if (!empty($prefix)) {
                foreach ($_COOKIE as $key => $val) {
                    if (0 === stripos($key, $prefix)) {
                        setcookie($key, '', time() - 3600, $options['path'], $options['domain'], $options['secure'], $options['httponly']);
                        unset($_COOKIE[$key]);
                    }
                }
            }

            return null;
        } elseif ('' === $key) {
            // Get All Cookie
            return $_COOKIE;
        }

        $key = $options['prefix'] . str_replace('.', '_', $key);
        if ('' === $value) {
            if (isset($_COOKIE[$key])) {
                $value = $_COOKIE[$key];
                if (0 === strpos($value, 'kotori:')) {
                    $value = substr($value, 6);
                    return array_map('urldecode', json_decode(MAGIC_QUOTES_GPC ? stripslashes($value) : $value, true));
                } else {
                    return $value;
                }
            } else {
                return null;
            }
        } else {
            if (is_null($value)) {
                setcookie($key, '', time() - 3600, $options['path'], $options['domain'], $options['secure'], $options['httponly']);
                unset($_COOKIE[$key]); // Delete Cookie
            } else {
                // Set Cookie
                if (is_array($value)) {
                    $value = 'kotori:' . json_encode(array_map('urlencode', $value));
                }

                $expire = !empty($options['expire']) ? time() + intval($options['expire']) : 0;
                setcookie($key, $value, $expire, $options['path'], $options['domain'], $options['secure'], $options['httponly']);
                $_COOKIE[$key] = $value;
            }
        }

        return null;
    }

    /**
     * Session Initialize
     *
     * @return void
     */
    public function sessionInit()
    {
        ini_set('session.auto_start', 0);
        $config = Container::get('config')->get('session');
        if ($config['adapter'] != '') {
            $class = '\\Kotori\\Http\\Session\\' . ucfirst($config['adapter']);
            if (!class_exists($class) || !session_set_save_handler(new $class($config))) {
                throw new NotFoundException('error session handler:' . $class);
            }
        }

        session_name('KOTORI_SESSID');
        if (isset($config['auto_start']) && $config['auto_start']) {
            session_start();
            $this->hasSessionStarted = true;
        }
    }

    /**
     * Start a Session
     *
     * @return void
     */
    public function sessionStart()
    {
        if (!$this->hasSessionStarted) {
            if (PHP_SESSION_ACTIVE != session_status()) {
                session_start();
            }

            $this->hasSessionStarted = true;
        }
    }

    /**
     * Set or Get session
     *
     * @param  mixed  $key
     * @param  string $value
     * @return mixed
     */
    public function session($key = '', $value = '')
    {
        $this->sessionStart();

        if (is_null($key)) {
            if (empty($_SESSION)) {
                return null;
            }

            unset($_SESSION);
        } elseif ('' === $key) {
            // Get All Session
            return $_SESSION;
        }

        if ('' === $value) {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        } else {
            if (is_null($value)) {
                unset($_SESSION[$key]);
            } else {
                $_SESSION[$key] = $value;
            }
        }

        return null;
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
            $base_url = ($this->isSecure() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
            . substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
        } else {
            $base_url = 'http://localhost/';
        }

        return rtrim($base_url, '/') . '/';
    }

    /**
     * Returns Client Ip Address
     *
     * @param  int     $type
     * @return string
     */
    public function getClientIp($type = 0)
    {
        $type = $type ? 1 : 0;

        if ($this->ip !== null) {
            return $this->ip[$type];
        }

        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $this->ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }

            $this->ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $this->ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }

        // Check ip
        $long = sprintf("%u", ip2long($this->ip));
        $this->ip = $long ? [$this->ip, $long] : ['0.0.0.0', 0];
        return $this->ip[$type];
    }

    /**
     * Returns Http Host
     *
     * @return string
     */
    public function getHostName()
    {
        $possibleHostSources = ['HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR'];
        $sourceTransformations = [
            "HTTP_X_FORWARDED_HOST" => function ($value) {
                $elements = explode(',', $value);
                return trim(end($elements));
            },
        ];
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
     * Return specified header
     *
     * @param  string $name
     * @return string
     */
    public function getHeader($name)
    {
        if (empty($this->headers)) {
            if (function_exists('apache_request_headers')) {
                $this->headers = apache_request_headers();
            } else {
                $server = $_SERVER;
                foreach ($server as $key => $value) {
                    if (0 === strpos($key, 'HTTP_')) {
                        $key = str_replace('_', '-', strtolower(substr($key, 5)));
                        $this->headers[$key] = $value;
                    }
                }

                if (isset($server['CONTENT_TYPE'])) {
                    $this->headers['content-type'] = $server['CONTENT_TYPE'];
                }

                if (isset($server['CONTENT_LENGTH'])) {
                    $this->headers['content-length'] = $server['CONTENT_LENGTH'];
                }
            }

            $this->headers = array_change_key_case($this->headers);
        }

        $name = str_replace('_', '-', strtolower($name));
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    /**
     * Detect whether request method is GET
     *
     * @return boolean
     */
    public function isGet()
    {
        return 'get' == strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Detect whether request method is POST
     *
     * @return boolean
     */
    public function isPost()
    {
        return 'post' == strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Detect whether request method is PUT
     *
     * @return boolean
     */
    public function isPut()
    {
        return 'put' == strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Detect whether request method is PATCH
     *
     * @return boolean
     */
    public function isPatch()
    {
        return 'patch' == strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Detect whether request method is DELETE
     *
     * @return boolean
     */
    public function isDelete()
    {
        return 'delete' == strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Detect whether request method is OPTIONS
     *
     * @return boolean
     */
    public function isOptions()
    {
        return 'options' == strtolower($_SERVER['REQUEST_METHOD']);
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
