<?php
namespace Kotori\Tests;

use Kotori\Core\Config;
use Kotori\Core\Database;
use PDO;
use PDOException;
use PHPUnit_Framework_TestCase;

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    protected static $MYSQL_HOST = '';
    protected static $MYSQL_USER = '';
    protected static $MYSQL_PWD = '';
    protected static $MYSQL_DB = '';

    public static function setUpBeforeClass()
    {
        self::$MYSQL_HOST = getenv('MYSQL_HOST');
        self::$MYSQL_USER = getenv('MYSQL_USER');
        self::$MYSQL_PWD = getenv('CI') ? '' : getenv('MYSQL_PWD');
        self::$MYSQL_DB = getenv('MYSQL_DB');

        self::createTestDatabase();
    }

    protected static function createTestDatabase()
    {
        try {
            $pdo = new PDO('mysql:host=' . self::$MYSQL_HOST, self::$MYSQL_USER, self::$MYSQL_PWD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec('DROP DATABASE IF EXISTS `' . self::$MYSQL_DB . '`;
CREATE DATABASE `' . self::$MYSQL_DB . '`;
USE `' . self::$MYSQL_DB . '`;

DROP TABLE IF EXISTS `table`;
CREATE TABLE `table` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        } catch (PDOException $e) {
            throw $e;
        }

    }

    protected function getDatabaseInstance()
    {
        $config = Config::getSoul();
        $config->initialize([
            'ENV' => 'test',
            'APP_DEBUG' => false,
            'DB' => [
                'db' => [
                    'TYPE' => 'mysql',
                    'NAME' => self::$MYSQL_DB,
                    'HOST' => self::$MYSQL_HOST,
                    'USER' => self::$MYSQL_USER,
                    'PWD' => self::$MYSQL_PWD,
                ],
            ],
        ]);
        $database = Database::getSoul();
        return $database;
    }

    public function testInsert()
    {
        $insertId = $this->getDatabaseInstance()
            ->insert('table', [
                'id' => 1,
                'name' => 'kotori',
            ]);
        $this->assertNotEquals(0, $insertId);
    }

    public function testSelect()
    {
        $datas = $this->getDatabaseInstance()
            ->select('table', '*', [
                'id' => 1,
            ]);
        $this->assertEquals('kotori', $datas[0]['name']);
    }

    public function testUpdate()
    {
        $effectedRows = $this->getDatabaseInstance()
            ->update('table', [
                'name' => 'honoka',
            ], [
                'id' => 1,
            ]);
        $this->assertGreaterThan(0, $effectedRows);
    }

    public function testDelete()
    {
        $effectedRows = $this->getDatabaseInstance()
            ->delete('table', [
                'id' => 1,
            ]);
        $this->assertGreaterThan(0, $effectedRows);
    }
}
