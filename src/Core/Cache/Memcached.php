<?php

/**
 * Kotori.php
 *
 * A Tiny Model-View-Controller PHP Framework
 *
 * This content is released under the Apache 2 License
 *
 * Copyright (c) 2015-2022 kokororin. All rights reserved.
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
 * @subpackage  Cache
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core\Cache;

use Kotori\Core\Container;
use Kotori\Debug\Hook;
use Kotori\Exception\CacheException;

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
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 1,
        ],
    ];

    /**
     * Class constructor
     *
     * Setup Memcache(d)
     *
     * @param  array $config
     *
     * @throws \Kotori\Exception\CacheException
     */
    public function __construct($config = [])
    {
        // Try to load memcached server info from the config file.
        $defaults = $this->memcacheConf['default'];
        if (empty($config)) {
            $config = Container::get('config')->get('cache');
        }

        $memcacheConf = isset($config['memcached']) ? $config['memcached'] : null;

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
            throw new CacheException('Failed to create Memcache(d) object; extension not loaded?');
        }

        foreach ($this->memcacheConf as $cacheServer) {
            if (!isset($cacheServer['host'])) {
                $cacheServer['host'] = $defaults['host'];
            }

            if (!isset($cacheServer['port'])) {
                $cacheServer['port'] = $defaults['port'];
            }

            if (!isset($cacheServer['weight'])) {
                $cacheServer['weight'] = $defaults['weight'];
            }

            if (get_class($this->memcached) === 'Memcache') {
                // Third parameter is persistance and defaults to TRUE.
                $this->memcached->addServer(
                    $cacheServer['host'],
                    $cacheServer['port'],
                    true,
                    $cacheServer['weight']
                );
            } else {
                $this->memcached->addServer(
                    $cacheServer['host'],
                    $cacheServer['port'],
                    $cacheServer['weight']
                );
            }
        }

        Hook::listen(__CLASS__);
    }

    /**
     * Fetch from cache
     *
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->memcached->get($key);

        return is_array($value) ? $value[0] : $value;
    }

    /**
     * Set
     *
     * @param  string   $key
     * @param  mixed    $value
     * @param  int      $ttl
     * @param  boolean  $raw
     * @return boolean
     */
    public function set($key, $value, $ttl = 60, $raw = false)
    {
        if ($raw !== true) {
            $value = [$value, time(), $ttl];
        }

        if (get_class($this->memcached) === 'Memcached') {
            return $this->memcached->set($key, $value, $ttl);
        } elseif (get_class($this->memcached) === 'Memcache') {
            return $this->memcached->set($key, $value, 0, $ttl);
        }

        return false;
    }

    /**
     * Delete from Cache
     *
     * @param  mixed    $key
     * @return boolean
     */
    public function delete($key)
    {
        return $this->memcached->delete($key);
    }

    /**
     * Clean the Cache
     *
     * @return boolean
     */
    public function clear()
    {
        return $this->memcached->flush();
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
        return (extension_loaded('memcached') || extension_loaded('memcache'));
    }
}
