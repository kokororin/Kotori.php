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
 * Memcached Caching Class
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core\Cache;

use Kotori\Core\Container;
use Kotori\Debug\Hook;
use Kotori\Debug\Log;

class Memcached
{
    /**
     * Holds the memcached object
     *
     * @var object
     */
    protected $memcached;

    /**
     * Memcached configuration
     *
     * @var array
     */
    protected $memcacheConf = [
        'default' => [
            'HOST' => '127.0.0.1',
            'PORT' => 11211,
            'WEIGHT' => 1,
        ],
    ];

    /**
     * Class constructor
     *
     * Setup Memcache(d)
     *
     * @return void
     */
    public function __construct()
    {
        // Try to load memcached server info from the config file.
        $defaults = $this->memcacheConf['default'];
        $config = Container::get('config')->get('CACHE');
        $memcacheConf = isset($config['MEMCACHED']) ? $config['MEMCACHED'] : null;

        if (is_array($memcacheConf)) {
            $this->memcacheConf = [];

            foreach ($memcacheConf as $name => $conf) {
                $this->memcacheConf[$name] = $conf;
            }
        }

        if (class_exists('Memcached', false)) {
            $this->memcached = new \Memcached();
        } elseif (class_exists('Memcache', false)) {
            $this->memcached = new \Memcache();
        } else {
            Log::normal('[Error] Failed to create Memcache(d) object; extension not loaded?');
        }

        foreach ($this->memcacheConf as $cacheServer) {
            if (!isset($cacheServer['HOST'])) {
                $cacheServer['HOST'] = $defaults['HOST'];
            }

            if (!isset($cacheServer['PORT'])) {
                $cacheServer['PORT'] = $defaults['PORT'];
            }

            if (!isset($cacheServer['WEIGHT'])) {
                $cacheServer['WEIGHT'] = $defaults['WEIGHT'];
            }

            if (get_class($this->memcached) === 'Memcache') {
                // Third parameter is persistance and defaults to TRUE.
                $this->memcached->addServer(
                    $cacheServer['HOST'],
                    $cacheServer['PORT'],
                    true,
                    $cacheServer['WEIGHT']
                );
            } else {
                $this->memcached->addServer(
                    $cacheServer['HOST'],
                    $cacheServer['PORT'],
                    $cacheServer['WEIGHT']
                );
            }
        }

        Hook::listen(__CLASS__);
    }

    /**
     * Fetch from cache
     *
     * @param  string $id
     * @return mixed
     */
    public function get($id)
    {
        $data = $this->memcached->get($id);

        return is_array($data) ? $data[0] : $data;
    }

    /**
     * Set
     *
     * @param  string   $id
     * @param  mixed    $data
     * @param  int      $ttl
     * @param  boolean  $raw
     * @return boolean
     */
    public function set($id, $data, $ttl = 60, $raw = false)
    {
        if ($raw !== true) {
            $data = [$data, time(), $ttl];
        }

        if (get_class($this->memcached) === 'Memcached') {
            return $this->memcached->set($id, $data, $ttl);
        } elseif (get_class($this->memcached) === 'Memcache') {
            return $this->memcached->set($id, $data, 0, $ttl);
        }

        return false;
    }

    /**
     * Delete from Cache
     *
     * @param  mixed    $id
     * @return boolean
     */
    public function delete($id)
    {
        return $this->memcached->delete($id);
    }

    /**
     * Increment a raw value
     *
     * @param  string   $id
     * @param  int      $offset
     * @return mixed
     */
    public function increment($id, $offset = 1)
    {
        return $this->memcached->increment($id, $offset);
    }

    /**
     * Decrement a raw value
     *
     * @param  string $id
     * @param  int    $offset
     * @return mixed
     */
    public function decrement($id, $offset = 1)
    {
        return $this->memcached->decrement($id, $offset);
    }

    /**
     * Clean the Cache
     *
     * @return boolean
     */
    public function clean()
    {
        return $this->memcached->flush();
    }

    /**
     * Cache Info
     *
     * @return mixed
     */
    public function cacheInfo()
    {
        return $this->memcached->getStats();
    }

    /**
     * Get Cache Metadata
     *
     * @param  mixed  $id
     * @return mixed
     */
    public function getMetadata($id)
    {
        $stored = $this->memcached->get($id);

        if (count($stored) !== 3) {
            return false;
        }

        list($data, $time, $ttl) = $stored;

        return [
            'expire' => $time + $ttl,
            'mtime' => $time,
            'data' => $data,
        ];
    }

    /**
     * Is supported
     *
     * Returns FALSE if memcached is not supported on the system.
     * If it is, we setup the memcached object & return TRUE
     *
     * @return boolean
     */
    public function isSupported()
    {
        return (extension_loaded('memcached') or extension_loaded('memcache'));
    }
}
