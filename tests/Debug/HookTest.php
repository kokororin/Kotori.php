<?php
namespace Kotori\Tests\Hook;

use Kotori\Debug\Hook;
use PHPUnit_Framework_TestCase;

class HookTest extends PHPUnit_Framework_TestCase
{
    public function testListenAndGetTags()
    {
        Hook::listen('kotori');
        $this->assertArrayHasKey('kotori', Hook::getTags());
    }
}
