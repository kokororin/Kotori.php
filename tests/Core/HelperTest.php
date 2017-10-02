<?php
namespace Kotori\Tests\Core;

use Kotori\Core\Helper;
use PHPUnit_Framework_TestCase as TestCase;

class HelperTest extends TestCase
{
    public function testIsFile()
    {
        $this->assertTrue(Helper::isFile(__FILE__));
    }

    public function testImport()
    {
        $this->assertTrue(Helper::import(__DIR__ . '/../../vendor/autoload.php'));
    }

    public function testGetComposerVendorPath()
    {
        $this->assertFileExists(Helper::getComposerVendorPath());
    }
}
