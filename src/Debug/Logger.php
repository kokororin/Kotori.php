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
 * Logging Class
 *
 * @package     Kotori
 * @subpackage  Debug
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Debug;

use Kotori\Core\Container;
use Kotori\Core\Helper;

class Logger
{
    /**
     * Class constructor
     *
     * Initialize Logger.
     *
     * @return void
     */
    public function __construct()
    {
        Hook::listen(__CLASS__);
    }

    /**
     * Write Log File
     *
     * @param  string $msg
     * @param  string $level
     * @return void
     */
    public function write($msg, $level = 'APP')
    {
        if (!Container::get('config')->get('app_debug') && $level != 'APP') {
            return;
        }

        $msg = date('[Y-m-d H:i:s]') . "\r\n" . "[{$level}]" . "\r\n" . $msg . "\r\n\r\n";
        $logPath = Container::get('config')->get('app_full_path') . '/logs';
        if (!file_exists($logPath)) {
            Helper::mkdirs($logPath);
        }

        if (file_exists($logPath)) {
            file_put_contents($logPath . '/' . date('Ymd') . '.log', $msg, FILE_APPEND);
        }
    }

    /**
     * Write Normal Log
     *
     * @param string $msg
     */
    public function normal($msg)
    {
        $this->write($msg, 'NORMAL');
    }

    /**
     * Write SQL Log
     *
     * @param string $msg
     */
    public function sql($msg)
    {
        $this->write($msg, 'SQL');
    }
}
