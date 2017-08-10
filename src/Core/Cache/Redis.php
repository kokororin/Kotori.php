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
 * Redis Caching Class
 *
 * @package     Kotori
 * @subpackage  Cache
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core\Cache;

use Kotori\Core\Container;
use Kotori\Exception\CacheException;
use RedisException;

class Redis
{
    /**
     * Default config
     *
     * @var array
     */
    protected $redisConf = [
        'host' => '127.0.0.1',
        'password' => null,
        'port' => 6379,
        'timeout' => 0,
        'database' => 0,
    ];

    /**
     * Redis connection
     *
     * @var Redis
     */
    protected $redis;

    /**
     * Class constructor
     *
     * Setup Redis
     *
     * Loads Redis config file if present. Will halt execution
     * if a Redis connection can't be established.
     *
     * @param   array $config
     * @return  void
     *
     * @throws \Kotori\Exception\CacheException
     */
    public function __construct($config = [])
    {
        if (!$this->isSupported()) {
            throw new CacheException('Failed to create Redis object; extension not loaded?');
        }

        if (empty($config)) {
            $config = Container::get('config')->get('cache');
            $config = array_merge($this->redisConf, $config);
        }

        $this->redis = new \Redis();

        try
        {
            if (!$this->redis->connect($config['host'], ($config['host'][0] === '/' ? 0 : $config['port']), $config['timeout'])) {
                throw new CacheException('Redis connection failed. Check your configuration.');
            }

            if (isset($config['password']) && !$this->redis->auth($config['password'])) {
                throw new CacheException('Redis authentication failed.');
            }

            if (isset($config['database']) && $config['database'] > 0 && !$this->redis->select($config['database'])) {
                throw new CacheException('Redis select database failed.');
            }
        } catch (RedisException $e) {
            throw new CacheException('Redis connection refused (' . $e->getMessage() . ')');
        }
    }

    /**
     * Get cache
     *
     * @param   string  $key
     * @return  mixed
     */
    public function get($key)
    {
        $data = $this->redis->hMGet($key, ['type', 'value']);

        if (!isset($data['type'], $data['value']) or $data['value'] === false) {
            return false;
        }

        switch ($data['type']) {
            case 'array':
            case 'object':
                return unserialize($data['value']);
            case 'boolean':
            case 'integer':
            case 'double': // Yes, 'double' is returned and NOT 'float'
            case 'string':
            case 'NULL':
                return settype($data['value'], $data['type'])
                ? $data['value']
                : false;
            case 'resource':
            default:
                return false;
        }
    }

    /**
     * Save cache
     *
     * @param   string    $id
     * @param   mixed     $data
     * @param   int       $ttl
     * @return  boolean
     */
    public function set($id, $data, $ttl = 60)
    {
        $dataType = gettype($data);

        switch ($dataType) {
            case 'array':
            case 'object':
                $data = serialize($data);
                break;
            case 'boolean':
            case 'integer':
            case 'double': // Yes, 'double' is returned and NOT 'float'
            case 'string':
            case 'NULL':
                break;
            case 'resource':
            default:
                return false;
        }

        if (!$this->redis->hMSet($id, ['type' => $dataType, 'value' => $data])) {
            return false;
        } elseif ($ttl) {
            $this->redis->expireAt($id, time() + $ttl);
        }

        return true;
    }

    /**
     * Delete from cache
     *
     * @param   string  $key
     * @return  boolean
     */
    public function delete($key)
    {
        return ($this->redis->delete($key) === 1);
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
        return $this->redis->hIncrBy($id, 'data', $offset);
    }

    /**
     * Decrement a raw value
     *
     * @param   string  $id
     * @param   int     $offset
     * @return  mixed
     */
    public function decrement($id, $offset = 1)
    {
        return $this->redis->hIncrBy($id, 'data', -$offset);
    }

    /**
     * Clean cache
     *
     * @return  boolean
     */
    public function clean()
    {
        return $this->redis->flushDB();
    }

    /**
     * Get cache driver info
     *
     * @return  array
     */
    public function cacheInfo()
    {
        return $this->redis->info();
    }

    /**
     * Get cache metadata
     *
     * @param   string  $key
     * @return  array
     */
    public function getMetadata($key)
    {
        $value = $this->get($key);

        if ($value !== false) {
            return [
                'expire' => time() + $this->redis->ttl($key),
                'data' => $value,
            ];
        }

        return false;
    }

    /**
     * Check if Redis driver is supported
     *
     * @return  boolean
     */
    public function isSupported()
    {
        return extension_loaded('redis');
    }

    /**
     * Class destructor
     *
     * Closes the connection to Redis if present.
     *
     * @return  void
     */
    public function __destruct()
    {
        if ($this->redis) {
            $this->redis->close();
        }
    }
}
