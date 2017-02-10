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

use Kotori\Core\Config;
use Kotori\Core\Helper;

abstract class Log
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
    protected static function write($msg, $level = '')
    {
        if (Config::getSoul()->APP_DEBUG == false) {
            return;
        }

        if (function_exists('saeAutoLoader')) {
            $msg = "[{$level}]" . $msg;
            sae_set_display_errors(false);
            sae_debug(trim($msg));
            sae_set_display_errors(true);
        } else {
            $msg = date('[ Y-m-d H:i:s ]') . "[{$level}]" . $msg . "\r\n";
            $logPath = Config::getSoul()->APP_FULL_PATH . '/logs';
            if (!file_exists($logPath)) {
                Helper::mkdirs($logPath);
            }

            file_put_contents($logPath . '/' . date('Ymd') . '.log', $msg, FILE_APPEND);
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
