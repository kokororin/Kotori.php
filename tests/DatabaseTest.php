<?php
namespace Kotori\Tests;

use Kotori\Core\Config;
use Kotori\Core\Database;
use PHPUnit_Framework_TestCase;

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    protected function getDatabaseInstance()
    {
        $config = Config::getSoul();
        $config->initialize([
            'ENV' => 'test',
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
