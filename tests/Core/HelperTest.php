<?php
namespace Kotori\Tests\Core;

use Kotori\Core\Helper;
use PHPUnit_Framework_TestCase;

class HelperTest extends PHPUnit_Framework_TestCase
{
    public function testIsFile()
    {
        $this->assertTrue(Helper::isFile(__FILE__));
    }

    public function testImport()
    {
        $this->assertTrue(Helper::import(__DIR__ . '/../../vendor/autoload.php'));
    }
}
