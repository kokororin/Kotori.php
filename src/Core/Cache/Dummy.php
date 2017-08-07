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
 * Dummy Caching Class
 *
 * @package     Kotori
 * @subpackage  Cache
 * @author      Kokororin
 * @link        https://kotori.love
 */

namespace Kotori\Core\Cache;

use Kotori\Debug\Hook;

class Dummy
{

    /**
     * Class constructor
     *
     * Setup Memcache(d)
     *
     * @return void
     */
    public function __construct()
    {
        Hook::listen(__CLASS__);
    }

    /**
     * Get
     *
     * Since this is the dummy class, it's always going to return FALSE.
     *
     * @param   string  $id
     * @return  boolean
     */
    public function get($id)
    {
        return false;
    }

    /**
     * Cache Set
     *
     * @param   string  $id
     * @param   mixed   $data
     * @param   int     $ttl
     * @param   boolean $raw
     * @return  boolean
     */
    public function set($id, $data, $ttl = 60, $raw = false)
    {
        return true;
    }

    /**
     * Delete from Cache
     *
     * @param   mixed   $id
     * @return  boolean
     */
    public function delete($id)
    {
        return true;
    }

    /**
     * Increment a raw value
     *
     * @param   string  $id
     * @param   int     $offset
     * @return  mixed
     */
    public function increment($id, $offset = 1)
    {
        return true;
    }

    /**
     * Decrement a raw value
     *
     * @param   string $id
     * @param   int    $offset
     * @return  mixed
     */
    public function decrement($id, $offset = 1)
    {
        return true;
    }

    /**
     * Clean the cache
     *
     * @return boolean
     */
    public function clean()
    {
        return true;
    }

    /**
     * Cache Info
     *
     * @param   string $type
     * @return  boolean
     */
    public function cacheInfo($type = null)
    {
        return false;
    }

    /**
     * Get Cache Metadata
     *
     * @param   mixed $id
     * @return  boolean
     */
    public function getMetadata($id)
    {
        return false;
    }

    /**
     * Is this caching driver supported on the system?
     * Of course this one is.
     *
     * @return boolean
     */
    public function isSupported()
    {
        return true;
    }

}
