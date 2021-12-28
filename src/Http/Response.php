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
 * Response Class
 *
 * @package     Kotori
 * @subpackage  Http
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Http;

use Kotori\Debug\Hook;
use Kotori\Exception\ResponseException;

class Response
{
    /**
     * Status array
     *
     * @var array
     */
    protected $httpCode = [
        100 => 'Continue',
        101 => 'Switching Protocols',
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
    ];

    /**
     * Default Charset
     *
     * @var string
     */
    protected $charset = null;

    /**
     * Class constructor
     *
     * Initialize Response.
     */
    public function __construct()
    {
        Hook::listen(__CLASS__);
        $this->setCharset('UTF-8');
    }

    /**
     * Get current charset
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Set current charset
     *
     * @param  string $charset
     * @return void
     */
    public function setCharset($charset = null)
    {
        $this->charset = empty($charset) ? 'UTF-8' : $charset;
    }

    /**
     * Set HTTP Status Header
     *
     * @param  int    $code
     * @param  string $text
     * @return void
     *
     * @throws \Kotori\Exception\ResponseException
     */
    public function setStatus($code = 200, $text = '')
    {
        if (empty($code) or !is_numeric($code)) {
            throw new ResponseException('Status codes must be numeric.');
        }

        if (empty($text)) {
            if (!is_int($code)) {
                $code = (int) $code;
            }

            if (isset($this->httpCode[$code])) {
                $text = $this->httpCode[$code];
            } else {
                throw new ResponseException('No status text available. Please check your status code number or supply your own message text.');
            }
        }

        if (strpos(PHP_SAPI, 'cgi') === 0) {
            header('Status: ' . $code . ' ' . $text, true);
        } else {
            $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
            header($protocol . ' ' . $code . ' ' . $text, true, $code);
        }
    }

    /**
     * Set Header
     *
     * Lets you set a server header which will be sent with the final output.
     *
     * @param  string $name
     * @param  string $value
     * @return void
     */
    public function setHeader($name, $value)
    {
        header($name . ': ' . $value, true);
    }

    /**
     * Set Content-Type
     *
     * Let you set a Content-Type header
     *
     * @param  string $contentType
     * @return void
     */
    public function setContentType($contentType = 'text/html')
    {
        header('Content-Type: ' . $contentType . '; charset=' . $this->getCharset(), true);
    }

    /**
     * Thown JSON to output
     *
     * @param  mixed $data
     * @return void
     */
    public function throwJSON($data)
    {
        $this->setContentType('application/json');
        exit(json_encode($data));
    }

    /**
     * Header Redirect
     *
     * @param  string $location
     * @param  boolean $isPermanently
     * @return void
     */
    public function redirect($location, $isPermanently = false)
    {
        if ($isPermanently) {
            header('Location: ' . $location, false, 301);
            exit;
        } else {
            header('Location: ' . $location, false, 302);
            exit;
        }
    }

    /**
     * Output static file with 304 header
     *
     * @return void
     */
    public function setCacheHeader()
    {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $this->setStatus(304);
            exit;
        }

        $this->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + 365 * 24 * 60 * 60) . ' GMT');
        $this->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        $this->setHeader('Cache-Control', 'immutable');
    }
}
