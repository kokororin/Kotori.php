<?php
namespace Kotori\Tests;

use Kotori\App;
use Kotori\Core\Config;
use Kotori\Core\Database;
use PDO;
use PDOException;
use PHPUnit_Framework_TestCase;

class AppTest extends PHPUnit_Framework_TestCase
{
    protected static $MYSQL_HOST = '127.0.0.1';
    protected static $MYSQL_USER = 'root';
    protected static $MYSQL_PWD = '123456';
    protected static $MYSQL_DB = 'kotori_php_test_db';

    public function __construct()
    {
        if (getenv('CI')) {
            self::$MYSQL_PWD = '';
        }

        $this->createTestDatabase();
    }

    public function testApp()
    {
        $app = new App();
        $this->assertTrue(!empty($app));
    }

    public function testGetConfig()
    {
        $config = new Config();
        $config->initialize([
            'ENV' => 'test',
        ]);
        $this->assertEquals(true, $config->APP_DEBUG);
    }

    public function testSetConfig()
    {
        $config = new Config();
        $config->initialize([
            'ENV' => 'test',
            'MY_ENV' => 'MY_ENV',
        ]);
        $this->assertEquals('MY_ENV', $config->MY_ENV);
    }

    public function testGetConfigArray()
    {
        $config = new Config();
        $config->initialize([
            'ENV' => 'test',
        ]);
        $this->assertTrue(is_array($config->getArray()));
    }

    protected function createTestDatabase()
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
