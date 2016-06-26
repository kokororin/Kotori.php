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
        $tpl_path = Config::getSoul()->ERROR_TPL;

        if ($tpl_path == null) {
            $tpl = '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN" prefix="og: http://ogp.me/ns#">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
<title>Error Occurred.</title>
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
    word-wrap: break-word;
    word-break: normal;
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
 var el = document.createElement("a");
 document.body.appendChild(el);
 el.href = url;
 el.target = "_blank";
 el.click();
 document.body.removeChild(el);
}
</script>
</head>

<body id="error-page">
    <h1>Error Occurred.</h1>
    {$message}
    <button class="button" onclick="open_link(\'https://github.com/kokororin/Kotori.php\')">Go to GitHub Page</button>
    <button class="button" onclick="open_link(\'https://github.com/kokororin/Kotori.php/issues\')">Report a Bug</button>
</body>
</html>';
        } else {
            $tpl = file_get_contents(Config::getSoul()->APP_FULL_PATH . '/views/' . $tpl_path . '.html');
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
        $text = '<p><strong>Exception:</strong>' . $exception->getMessage() . '</p>';
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
