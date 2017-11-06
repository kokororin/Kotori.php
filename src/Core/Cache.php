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
 * Caching Class
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Kotori\Core\Container;
use Kotori\Debug\Hook;
use Psr\SimpleCache\CacheInterface;

class Cache implements CacheInterface
{
    /**
     * Valid cache drivers
     *
     * @var array
     */
    protected $validDrivers = [
        'dummy',
        'memcached',
        'redis',
    ];

    /**
     * Reference to the driver
     *
     * @var mixed
     */
    protected $adapter = 'dummy';

    /**
     * Cache key prefix
     *
     * @var string
     */
    public $keyPrefix = '';

    /**
     * Constructor
     *
     * Initialize class properties based on the configuration array.
     *
     * @return void
     */
    public function __construct()
    {
        $config = Container::get('config')->get('cache');
        if (isset($config['adapter'])) {
            $this->adapter = $config['adapter'];
        }

        if (isset($config['prefix'])) {
            $this->keyPrefix = $config['prefix'];
        }

        $className = '\\Kotori\\Core\\Cache\\' . ucfirst($this->adapter);
        $this->{$this->adapter} = new $className();

        if (!$this->isSupported($this->adapter)) {
            Container::get('logger')->error('Cache adapter "{adapter}" is unavailable. Cache is now using "Dummy" adapter.', ['adapter' => $this->adapter]);
            $this->adapter = 'dummy';
        }

        Hook::listen(__CLASS__);
    }

    /**
     * Fetches a value from the cache.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $this->{$this->adapter}->get($this->keyPrefix . $key);
        return $value ? $value : $default;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param  string   $key
     * @param  mixed    $value
     * @param  int      $ttl
     * @return boolean
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->{$this->adapter}->set($this->keyPrefix . $key, $value, $ttl);
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param  string  $key
     * @return boolean
     */
    public function delete($key)
    {
        return $this->{$this->adapter}->delete($this->keyPrefix . $key);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return boolean
     */
    public function clear()
    {
        return $this->{$this->adapter}->clear();
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param  iterable $keys
     * @param  mixed    $default
     * @return iterable
     */
    public function getMultiple($keys, $default = null)
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param  iterable $values
     * @param  int      $ttl
     * @return boolean
     */
    public function setMultiple($values, $ttl = null)
    {
        $failTimes = 0;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $failTimes++;
            }
        }

        return $failTimes == 0;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param  iterable $keys
     * @return boolean
     */
    public function deleteMultiple($keys)
    {
        $failTimes = 0;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $failTimes++;
            }
        }

        return $failTimes == 0;
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * @param  string $key
     * @return boolean
     */
    public function has($key)
    {
        return (bool) $this->get($key);
    }

    /**
     * Is the requested driver supported in this environment?
     *
     * @param  string $driver
     * @return array
     */
    public function isSupported($driver)
    {
        static $support;

        if (!isset($support, $support[$driver])) {
            $support[$driver] = $this->{$driver}->isSupported();
        }

        return $support[$driver];
    }

}
