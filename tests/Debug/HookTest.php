<?php
namespace Kotori\Tests\Hook;

use Kotori\Debug\Hook;
use PHPUnit_Framework_TestCase as TestCase;

class HookTest extends TestCase
{
    public function testListenAndGetTags()
    {
        Hook::listen('kotori');
        $this->assertArrayHasKey('kotori', Hook::getTags());
    }
}
