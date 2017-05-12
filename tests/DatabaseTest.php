<?php
namespace Kotori\Tests;

use Kotori\Core\Config;
use Kotori\Core\Database;
use PHPUnit_Framework_TestCase;

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    protected static $db = null;

    public static function setUpBeforeClass()
    {
        $config = Config::getSoul();
        $config->initialize([
            'ENV' => getenv('APP_ENV'),
            'APP_DEBUG' => false,
            'DB' => [
                'db' => [
                    'TYPE' => 'mysql',
                    'NAME' => getenv('MYSQL_DB'),
                    'HOST' => getenv('MYSQL_HOST'),
                    'USER' => getenv('MYSQL_USER'),
                    'PWD' => getenv('MYSQL_PWD'),
                ],
            ],
        ]);
        self::$db = Database::getSoul();
    }

    public function testInsert()
    {
        $insertId = self::$db
            ->insert('table', [
                'id' => 1,
                'name' => 'kotori',
            ]);
        $this->assertNotEquals(0, $insertId);
    }

    public function testSelect()
    {
        $datas = self::$db
            ->select('table', '*', [
                'id' => 1,
            ]);
        $this->assertEquals('kotori', $datas[0]['name']);
    }

    public function testUpdate()
    {
        $effectedRows = self::$db
            ->update('table', [
                'name' => 'honoka',
            ], [
                'id' => 1,
            ]);
        $this->assertGreaterThan(0, $effectedRows);
    }

    public function testDelete()
    {
        $effectedRows = self::$db
            ->delete('table', [
                'id' => 1,
            ]);
        $this->assertGreaterThan(0, $effectedRows);
    }
}
