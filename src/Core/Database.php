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
 * Database Class
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Exception;
use Kotori\Debug\Hook;
use Kotori\Exception\DatabaseException;
use Medoo\Medoo;
use PDOException;
use PDOStatement;

class Database extends Medoo
{
    /**
     * SQL queries
     *
     * @var array
     */
    public static $queries = [];

    /**
     * Instance Handle
     *
     * @var array
     */
    protected static $instance;

    /**
     * Disable Clone
     *
     * @return boolean
     */
    public function __clone()
    {
        return false;
    }

    /**
     * Get singleton
     *
     * @param  string  $key
     * @return object
     *
     * @throws \Kotori\Exception\DatabaseException
     */
    public static function getInstance($key = null)
    {
        $config = Container::get('config')->get('db');
        if (!is_array($config) || count($config) == 0) {
            return null;
        } elseif ($key == null) {
            $dbKeys = array_keys($config);
            if (isset($dbKeys[0])) {
                $key = $dbKeys[0];
            } else {
                return null;
            }
        }

        Container::get('config')->set('selected_db_key', $key);

        if (!isset(self::$instance[$key])) {
            try {
                self::$instance[$key] = new self($config[$key]);
            } catch (PDOException $e) {
                throw new DatabaseException($e);
            } catch (Exception $e) {
                throw new DatabaseException($e);
            }
        }

        return self::$instance[$key];
    }

    /**
     * Class constructor
     *
     * Initialize Database.
     */
    public function __construct(array $options = [])
    {
        parent::__construct([
            'type' => $options['type'],
            'database' => $options['name'],
            'host' => $options['host'],
            'username' => $options['user'],
            'password' => $options['pwd'],
            'charset' => $options['charset'],
            'port' => $options['port'],
        ]);
        Hook::listen(__CLASS__);
    }

    /**
     * medoo::query()
     *
     * @param  string $query
     * @param  array  $map
     * @return \PDOStatement
     */
    public function query(string $query, array $map = []): ?PDOStatement
    {
        $statement = parent::exec($query, $map);
        $lastSQL = parent::last();
        Container::get('logger')->info($lastSQL);
        array_push(self::$queries, $lastSQL);
        return $statement;
    }

    /**
     * medoo:exec()
     *
     * @param  string $query
     * @param  array  $map
     * @return \PDOStatement
     */
    public function exec(string $query, array $map = [], callable $callback = null): ?PDOStatement
    {
        $statement = parent::exec($query, $map, $callback);
        $lastSQL = parent::last();
        Container::get('logger')->info($lastSQL);
        array_push(self::$queries, $lastSQL);
        return $statement;
    }
}
