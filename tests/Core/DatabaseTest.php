<?php
namespace Kotori\Tests\Core;

use Kotori\Core\Database;
use Kotori\Facade\Config;
use PHPUnit_Framework_TestCase;

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    protected static $db = null;

    public static function setUpBeforeClass()
    {
        Config::initialize([
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
        self::$db = Database::getInstance();
    }

    public function testInsert()
    {
        $query = self::$db
            ->insert(getenv('MYSQL_TABLE'), [
                'id' => 1,
                'name' => 'kotori',
            ]);
        $this->assertGreaterThan(0, $query->rowCount());
    }

    public function testSelect()
    {
        $datas = self::$db
            ->select(getenv('MYSQL_TABLE'), '*', [
                'id' => 1,
            ]);
        $this->assertEquals('kotori', $datas[0]['name']);
    }

    public function testUpdate()
    {
        $query = self::$db
            ->update(getenv('MYSQL_TABLE'), [
                'name' => 'honoka',
            ], [
                'id' => 1,
            ]);
        $this->assertGreaterThan(0, $query->rowCount());
    }

    public function testDelete()
    {
        $query = self::$db
            ->delete(getenv('MYSQL_TABLE'), [
                'id' => 1,
            ]);
        $this->assertGreaterThan(0, $query->rowCount());
    }
}
