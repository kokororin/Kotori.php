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
 * Handle Class
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Kotori\Debug\Log;
use Kotori\Http\Request;
use Kotori\Http\Response;

class Handle
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
    public static function halt($message, $code = 404)
    {
        Response::getSoul()->setStatus($code);
        if (Config::getSoul()->APP_DEBUG == false) {
            $message = '404 Not Found.';
        }
        $tplPath = Config::getSoul()->ERROR_TPL;

        if ($tplPath == null || !Common::isFile(Config::getSoul()->APP_FULL_PATH . '/views/' . $tplPath . '.html')) {
            $tpl = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title>Kotori.php 500 Internal Error</title>
  <meta name="robots" content="NONE,NOARCHIVE">
  <style type="text/css">
    html * { padding:0; margin:0; }
    body * { padding:10px 20px; }
    body * * { padding:0; }
    body { font:small sans-serif; background:#eee; }
    body>div { border-bottom:1px solid #ddd; }
    h1 { font-weight:normal; margin-bottom:.4em; }
    h1 span { font-size:60%; color:#666; font-weight:normal; }
    table { border:none; border-collapse: collapse; width:100%; }
    td, th { vertical-align:top; padding:2px 3px; }
    th { width:12em; text-align:right; color:#666; padding-right:.5em; }
    #info { background:#f6f6f6; }
    #summary { background: #ffc; }
    #explanation { background:#eee; border-bottom: 0px none; }
  </style>
</head>
<body>
  <div id="summary">
    <h1>Kotori.php Internal Error <span>(500)</span></h1>
    <table class="meta">
      <tr>
        <th>Request Method:</th>
        <td>' . strtoupper($_SERVER['REQUEST_METHOD']) . '</td>
      </tr>
      <tr>
        <th>Request URL:</th>
        <td>' . Request::getSoul()->getBaseUrl() . ltrim($_SERVER["REQUEST_URI"], '/') . '</td>
      </tr>

    </table>
  </div>
  <div id="info">
      <h2>' . $message . '</h2>
  </div>

  <div id="explanation">
    <p>
      You\'re seeing this error because you have <code>APP_DEBUG = True</code> in
      your index.php file. Change that to <code>False</code>, and Kotori.php
      will display a standard 404 page.
    </p>
  </div>
</body>
</html>';
        } else {
            $tpl = file_get_contents(Config::getSoul()->APP_FULL_PATH . '/views/' . $tplPath . '.html');
        }

        $tpl = str_replace('{$message}', $message, $tpl);
        exit($tpl);
    }

    /**
     * Error Handler
     *
     * This function lets us invoke the exception class and
     * display errors using the standard error template located
     * in app/views/Public/error.html
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
        $txt = '[Type] ' . self::getErrorType($errno) . ' [Info] ' . $errstr . ' [Line] ' . $errline . ' [File] ' . $errfile;
        array_push(self::$errors, $txt);
        Log::normal($txt);
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
        $text = '<p><strong>Exception:</strong> ' . $exception->getMessage() . '</p>';
        $txt = '[Type] Exception' . ' [Info] ' . $exception->getMessage();
        Log::normal($txt);
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
            ($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))) {
            $text = '<p><strong>Error Type: </strong>' . self::getErrorType($last_error['type']) . '</p>' . '<p><strong>Info: </strong>' . $last_error['message'] . '</p>' . '<p><strong>Line: </strong>' . $last_error['line'] . '</p>' . '<p><strong>File: </strong>' . $last_error['file'] . '</p>';
            $txt = '[Type] ' . $last_error['type'] . ' [Info] ' . $last_error['message'] . ' [Line] ' . $last_error['line'] . ' [File] ' . $last_error['file'];
            Log::normal($txt);
            self::halt($text, 500);
        }

    }

    public static function getErrorType($errno)
    {
        switch ($errno) {
            case E_ERROR:
                $errtype = 'A fatal error that causes script termination.';
                break;
            case E_WARNING:
                $errtype = 'Run-time warning that does not cause script termination.';
                break;
            case E_PARSE:
                $errtype = 'Compile time parse error.';
                break;
            case E_NOTICE:
                $errtype = 'Run time notice caused due to error in code.';
                break;
            case E_CORE_ERROR:
                $errtype = 'Fatal errors that occur during PHP\'s initial startup (installation).';
                break;
            case E_CORE_WARNING:
                $errtype = 'Warnings that occur during PHP\'s initial startup.';
                break;
            case E_COMPILE_ERROR:
                $errtype = 'Fatal compile-time errors indication problem with script.';
                break;
            case E_COMPILE_WARNING:
                $errtype = 'Non-Fatal Run Time Warning generated by Zend Engine.';
                break;
            case E_USER_ERROR:
                $errtype = 'User-generated error message.';
                break;
            case E_USER_WARNING:
                $errtype = 'User-generated warning message.';
                break;
            case E_USER_NOTICE:
                $errtype = 'User-generated notice message.';
                break;
            case E_STRICT:
                $errtype = 'Run-time notices.';
                break;
            case E_RECOVERABLE_ERROR:
                $errtype = 'Catchable fatal error indicating a dangerous error.';
                break;
            default:
                $errtype = 'Unknown';
                break;
        }
        return $errtype;
    }

}
