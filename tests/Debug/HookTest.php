<?php
namespace Kotori\Tests\Hook;

use Kotori\Debug\Hook;
use PHPUnit\Framework\TestCase;

class HookTest extends TestCase
{
    public function testListenAndGetTags()
    {
        Hook::listen('kotori');
        $this->assertArrayHasKey('kotori', Hook::getTags());
    }
}
