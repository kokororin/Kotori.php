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
 * Handle Class
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Exception;
use Highlight\Highlighter;
use Kotori\Exception\ResponseException;
use Symfony\Component\Console\Application as ConsoleApp;
use Symfony\Component\Console\Output\ConsoleOutput;
use WyriHaximus\HtmlCompress\Factory as HtmlCompress;

abstract class Handle
{
    /**
     * Error Array
     *
     * @var array
     */
    public static $errors = [];

    /**
     * General Error Page
     *
     * Takes an error message as input
     * and displays it using the specified template.
     *
     * @param  string $message
     * @param  int    $code
     * @return void
     */
    public static function halt($message, $code = 404)
    {
        Container::get('response')->setStatus($code);

        if (!Container::get('config')->get('app_debug')) {
            $message = '404 Not Found.';
        }

        if (Container::get('config')->get('app_debug')) {
            $tplPath = null;
        } else {
            $tplPath = Container::get('config')->get('error_tpl');
        }

        if ($tplPath == null || !Helper::isFile(Container::get('config')->get('app_full_path') . '/views/' . $tplPath . '.html')) {
            $tpl = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title>Oops! some error(s) occurred with your application</title>
  <meta name="robots" content="NONE,NOARCHIVE">
  <style type="text/css">
    html * { padding:0; margin:0; }
    body * { padding:10px 20px; }
    body * * { padding:0; }
    body { font-family:Avenir,Helvetica,Arial,sans-serif; -webkit-font-smoothing:antialiased;  -moz-osx-font-smoothing:grayscale;background:#eee; }
    body > div { border-bottom:1px solid #ddd; }
    h1 { font-weight:normal; color:#5d5d5d;}
    h1 span { font-size:60%; color:#666; font-weight:normal; }
    table { border-collapse:collapse; width:100%; }
    td, th { vertical-align:top; padding:2px 3px; }
    th { width:12em; text-align:right; color:#666; padding-right:.5em; }
    #info { background:#f6f6f6; }
    #info p { font-size:16px; margin:5px; color:#5d5d5d; }
    #info strong { font-size:17px; color:#696969; }
    #summary { background:#eee; }
    #explanation { background:#eee; border-bottom: 0px none; }
  </style>
</head>
<body>
  <div id="summary">
    <h1>Oops! some error(s) occurred with your application</span></h1>
  </div>
  <div id="info">
    <p><strong>Request Method: </strong>' . strtoupper($_SERVER['REQUEST_METHOD']) . '</p>
    <p><strong>Request URL: </strong>' . Container::get('request')->getBaseUrl() . ltrim($_SERVER['REQUEST_URI'], '/') . '</p>
      ' . $message . '
  </div>

  <div id="explanation">
    <p>
      You\'re seeing this error because you have <code>app_debug = True</code> in
      your index.php file. Change that to <code>False</code>, and Kotori.php
      will display a standard 404 page.
    </p>
  </div>
</body>
</html>';
        } else {
            $tpl = file_get_contents(Container::get('config')->get('app_full_path') . '/views/' . $tplPath . '.html');
        }

        $tpl = str_replace('{$message}', $message, $tpl);
        $tpl = HtmlCompress::construct()->compress($tpl);
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
     * @param string $errno
     * @param int    $errstr
     * @param string $errfile
     * @param int    $errline
     * @return void
     */
    public static function error($errno, $errstr, $errfile, $errline)
    {
        $type = self::getErrorType($errno);
        $text = self::renderErrorText($type, $errstr, $errline, $errfile);
        $txt = self::renderLogBody($type, $errstr, $errline, $errfile);
        array_push(self::$errors, $text);
        Container::get('logger')->warning($txt);
        if (Container::get('request')->isCli()) {
            self::outputCliError($errstr);
        } else {
            self::setDebugHeader($txt);
        }
    }

    /**
     * Exception Handler
     *
     * Sends uncaught exceptions to the logger and displays them
     * only if display_errors is On so that they don't show up in
     * production environments.
     *
     * @param  Exception $exception
     * @return void
     */
    public static function exception($exception)
    {
        $text = self::renderHaltBody(get_class($exception), $exception->getMessage(), $exception->getLine(), $exception->getFile());
        $txt = self::renderLogBody(get_class($exception), $exception->getMessage(), $exception->getLine(), $exception->getFile());
        Container::get('logger')->critical($txt);
        if (Container::get('request')->isCli()) {
            self::outputCliError($exception->getMessage());
        } else {
            self::setDebugHeader($txt);
            self::halt($text, Container::get('config')->get('app_debug') ? 500 : 404);
        }

        exit;
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
            $type = self::getErrorType($last_error['type']);
            $text = self::renderHaltBody($type, $last_error['message'], $last_error['line'], $last_error['file']);

            $txt = self::renderLogBody($type, $last_error['message'], $last_error['file'], $last_error['line']);

            Container::get('logger')->error($txt);
            if (Container::get('request')->isCli()) {
                self::outputCliError($last_error['message']);
            } else {
                self::setDebugHeader($txt);
                self::halt($text, Container::get('config')->get('app_debug') ? 500 : 404);
            }

            exit;
        }
    }

    /**
     * output debug info to header
     *
     * @param  string $txt
     * @return void
     */
    protected static function setDebugHeader($txt)
    {
        if (Container::get('config')->get('app_debug')) {
            Container::get('response')->setHeader('Kotori-Debug', str_replace("\r\n", ' ', $txt));
        }
    }

    /**
     * output error info to cli
     *
     * @param  string $message
     * @return void
     */
    protected static function outputCliError($message)
    {
        $application = new ConsoleApp();
        $output = new ConsoleOutput();
        $application->renderException(new ResponseException($message), $output);
    }

    /**
     * convert PHP ERROR to error detail
     *
     * @param  int $errno
     * @return string
     */
    protected static function getErrorType($errno)
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

    /**
     * render Halt Body
     *
     * @param  string $type
     * @param  int    $message
     * @param  int    $line
     * @param  string $file
     * @return string
     */
    protected static function renderHaltBody($type, $message, $line, $file)
    {
        $text = '<p><strong>Error Type: </strong>' . $type . '</p>' . '<p><strong>Info: </strong>' . nl2br($message) . '</p>' . '<p><strong>Line: </strong>' . $line . '</p>' . '<p><strong>File: </strong>' . $file . '</p>';
        $source = self::getSourceCode($file, $line);

        $sourceLen = strlen(strval(count($source['source']) + $source['first']));
        $padding = 40 + ($sourceLen - 1) * 8;
        if (!empty($source)) {
            $text .= '<style>' . file_get_contents(Helper::getComposerVendorPath() . '/scrivo/highlight.php/styles/github.css') . '</style>';
            $text .= '<style>
.source-code {
    padding: 6px;
    border: 1px solid #ddd;
    background: #f9f9f9;
    overflow-x: auto;
}

.source-code pre {
    margin: 0;
}

.source-code pre ol {
    margin: 0;
    color: #4288ce;
    display: inline-block;
    min-width: 100%;
    box-sizing: border-box;
    font-size: 14px;
    font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
    padding-left: ' . $padding . 'px;
}

.source-code pre li {
    border-left: 1px solid #ddd;
    height: 18px;
    line-height: 18px;
}

.source-code pre code {
    color: #333;
    height: 100%;
    display: inline-block;
    border-left: 1px solid #fff;
    font-size: 14px;
    font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
}

.source-code pre li.line-error {
    background: #f8cbcb;
}

</style>';
            $text .= '<div class="source-code">
<pre id="code-block" class="hljs language-php">
    <ol start="' . $source['first'] . '">';
            $highlighter = new Highlighter();
            foreach ($source['source'] as $key => $value) {
                $currentLine = $key + $source['first'];
                $extendClass = ($currentLine == $line) ? ' line-error' : '';
                $text .= '<li class="line-' . $currentLine . $extendClass . '"><code>' . $highlighter->highlight('php', $value)->value . '</code></li>';
            }

            $text .= '</ol></pre></div>';
        }

        return $text;
    }

    /**
     * render log body
     *
     * @param  string $type
     * @param  int    $message
     * @param  string $file
     * @param  int    $line
     * @return string
     */
    protected static function renderLogBody($type, $message, $line, $file)
    {
        return '[Type] ' . $type . "\r\n" . '[Info] ' . $message . "\r\n" . '[Line] ' . $line . "\r\n" . '[File] ' . $file;
    }

    /**
     * render errors display in trace
     *
     * @param  string $type
     * @param  int $message
     * @param  string $file
     * @param  int $line
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected static function renderErrorText($type, $message, $line, $file)
    {
        return $message . ' in ' . $file . ' on line ' . $line;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Get source code from file
     *
     * @param  string $file
     * @param  int    $line
     * @return array
     */
    protected static function getSourceCode($file, $line)
    {
        $first = ($line - 9 > 0) ? $line - 9 : 1;

        try {
            $contents = file($file);
            $source = [
                'first' => $first,
                'source' => array_slice($contents, $first - 1, 19),
            ];
        } catch (Exception $e) {
            $source = [];
        }

        return $source;
    }
}
