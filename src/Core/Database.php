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
use Kotori\Debug\Log;
use Kotori\Exception\DatabaseException;
use Kotori\Facade\Config;
use Medoo\Medoo;
use PDOException;

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
    protected static $_instance;

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
     * get singleton
     *
     * @return object
     */
    public static function getInstance($key = null)
    {
        if (count(Config::get('DB')) == 0) {
            return null;
        } elseif ($key == null) {
            $dbKeys = array_keys(Config::get('DB'));
            if (isset($dbKeys[0])) {
                $key = $dbKeys[0];
            } else {
                return null;
            }
        }

        Config::set('SELECTED_DB_KEY', $key);

        if (!isset(self::$_instance[$key])) {
            try {
                self::$_instance[$key] = new self(Config::get('DB')[$key]);
            } catch (PDOException $e) {
                throw new DatabaseException($e);
            } catch (Exception $e) {
                throw new DatabaseException($e);
            }
        }

        return self::$_instance[$key];
    }

    /**
     * Class constructor
     *
     * Initialize Database.
     *
     * @return void
     */
    public function __construct($options = null)
    {
        parent::__construct([
            'database_type' => $options['TYPE'],
            'database_name' => $options['NAME'],
            'server' => $options['HOST'],
            'username' => $options['USER'],
            'password' => $options['PWD'],
            'charset' => $options['CHARSET'],
            'port' => $options['PORT'],
        ]);
        Hook::listen(__CLASS__);
    }

    public function query($query, $map = [])
    {
        $statement = parent::exec($query, $map);
        $lastSQL = parent::last();
        Log::sql($lastSQL);
        array_push(self::$queries, $lastSQL);
        return $statement;
    }

    public function exec($query, $map = [])
    {
        $statement = parent::exec($query, $map);
        $lastSQL = parent::last();
        Log::sql($lastSQL);
        array_push(self::$queries, $lastSQL);
        return $statement;
    }

}
