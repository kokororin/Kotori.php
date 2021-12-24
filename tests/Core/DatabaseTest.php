<?php
namespace Kotori\Tests\Core;

use Kotori\Core\Database;
use Kotori\Facade\Config;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    protected static $db = null;

    public static function setUpBeforeClass(): void
    {
        Config::initialize([
            'app_debug' => false,
            'db' => [
                'default' => [
                    'type' => 'mysql',
                    'name' => getenv('MYSQL_DB'),
                    'host' => getenv('MYSQL_HOST'),
                    'user' => getenv('MYSQL_USER'),
                    'pwd' => getenv('MYSQL_PWD'),
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
