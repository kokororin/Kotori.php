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
use Kotori\Debug\Log;

class Cache
{
    /**
     * Valid cache drivers
     *
     * @var array
     */
    protected $validDrivers = [
        'dummy',
        'memcached',
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
            Log::normal('[Error] Cache adapter "' . $this->adapter . '" is unavailable. Cache is now using "Dummy" adapter.');
            $this->adapter = 'dummy';
        }

        Hook::listen(__CLASS__);
    }

    /**
     * Get
     *
     * Look for a value in the cache. If it exists, return the data
     * if not, return FALSE
     *
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->{$this->adapter}->get($this->keyPrefix . $id);
    }

    /**
     * Cache Set
     *
     * @param  string   $id
     * @param  mixed    $data
     * @param  int      $ttl
     * @param  boolean  $raw
     * @return boolean
     */
    public function set($id, $data, $ttl = 60, $raw = false)
    {
        return $this->{$this->adapter}->set($this->keyPrefix . $id, $data, $ttl, $raw);
    }

    /**
     * Delete from Cache
     *
     * @param  string  $id
     * @return boolean
     */
    public function delete($id)
    {
        return $this->{$this->adapter}->delete($this->keyPrefix . $id);
    }

    /**
     * Increment a raw value
     *
     * @param  string  $id
     * @param  int     $offset
     * @return mixed
     */
    public function increment($id, $offset = 1)
    {
        return $this->{$this->adapter}->increment($this->keyPrefix . $id, $offset);
    }

    /**
     * Decrement a raw value
     *
     * @param  string  $id
     * @param  int     $offset
     * @return mixed
     */
    public function decrement($id, $offset = 1)
    {
        return $this->{$this->adapter}->decrement($this->keyPrefix . $id, $offset);
    }

    /**
     * Clean the cache
     *
     * @return boolean
     */
    public function clean()
    {
        return $this->{$this->adapter}->clean();
    }

    /**
     * Cache Info
     *
     * @param  string $type
     * @return mixed
     */
    public function cacheInfo($type = 'user')
    {
        return $this->{$this->adapter}->cacheInfo($type);
    }

    /**
     * Get Cache Metadata
     *
     * @param  string $id
     * @return mixed
     */
    public function getMetadata($id)
    {
        return $this->{$this->adapter}->getMetadata($this->keyPrefix . $id);
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
