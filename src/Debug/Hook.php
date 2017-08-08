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
 * Hook Class
 *
 * @package     Kotori
 * @subpackage  Debug
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Debug;

abstract class Hook
{
    /**
     * Hook tags
     *
     * @var array
     */
    protected static $tags = [];

    /**
     * Get the tags
     *
     * @return array
     */
    public static function getTags()
    {
        return self::$tags;
    }

    /**
     * Start Hook listen
     *
     * @param  string $name
     * @return int
     */
    public static function listen($name)
    {
        if (!isset(self::$tags[$name])) {
            self::$tags[$name] = round((microtime(true) - KOTORI_START_TIME) * pow(10, 6));
        }

        return self::$tags[$name];
    }

}
