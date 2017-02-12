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

use Kotori\Debug\Hook;
use Kotori\Debug\Log;

class Cache implements SoulInterface
{
    use SoulTrait;
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
    protected $_adapter = 'dummy';

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
        $config = Config::getSoul()->CACHE;
        if (isset($config['ADAPTER'])) {
            $this->_adapter = $config['ADAPTER'];
        }

        if (isset($config['PREFIX'])) {
            $this->keyPrefix = $config['PREFIX'];
        }

        $className = '\\Kotori\\Core\\Cache\\' . ucfirst($this->_adapter);
        $this->{$this->_adapter} = new $className();

        if (!$this->isSupported($this->_adapter)) {
            Log::normal('[Error] Cache adapter "' . $this->_adapter . '" is unavailable. Cache is now using "Dummy" adapter.');
            $this->_adapter = 'dummy';
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
     * @return mixed value matching $id or FALSE on failure
     */
    public function get($id)
    {
        return $this->{$this->_adapter}->get($this->keyPrefix . $id);
    }

    /**
     * Cache Set
     *
     * @param string $id Cache ID
     * @param mixed $data Data to store
     * @param int $ttl Cache TTL (in seconds)
     * @param bool $raw Whether to store the raw value
     * @return bool TRUE on success, FALSE on failure
     */
    public function set($id, $data, $ttl = 60, $raw = false)
    {
        return $this->{$this->_adapter}->set($this->keyPrefix . $id, $data, $ttl, $raw);
    }

    /**
     * Delete from Cache
     *
     * @param string Cache ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function delete($id)
    {
        return $this->{$this->_adapter}->delete($this->keyPrefix . $id);
    }

    /**
     * Increment a raw value
     *
     * @param string Cache ID
     * @param int Step/value to add
     * @return mixed New value on success or FALSE on failure
     */
    public function increment($id, $offset = 1)
    {
        return $this->{$this->_adapter}->increment($this->keyPrefix . $id, $offset);
    }

    /**
     * Decrement a raw value
     *
     * @param string $id Cache ID
     * @param int $offset Step/value to reduce by
     * @return mixed New value on success or FALSE on failure
     */
    public function decrement($id, $offset = 1)
    {
        return $this->{$this->_adapter}->decrement($this->keyPrefix . $id, $offset);
    }

    /**
     * Clean the cache
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function clean()
    {
        return $this->{$this->_adapter}->clean();
    }

    /**
     * Cache Info
     *
     * @param string $type user/filehits
     * @return mixed array containing cache info on success OR FALSE on failure
     */
    public function cacheInfo($type = 'user')
    {
        return $this->{$this->_adapter}->cacheInfo($type);
    }

    /**
     * Get Cache Metadata
     *
     * @param string $id key to get cache metadata on
     * @return mixed cache item metadata
     */
    public function getMetadata($id)
    {
        return $this->{$this->_adapter}->getMetadata($this->keyPrefix . $id);
    }

    /**
     * Is the requested driver supported in this environment?
     *
     * @param string $key The driver to test
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
