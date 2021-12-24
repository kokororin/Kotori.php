<?php
namespace Kotori\Tests\Http;

use Exception;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    protected static $END_POINT = null;

    public static function setUpBeforeClass(): void
    {
        self::$END_POINT = 'http://' . getenv('WEB_SERVER_HOST') . ':' . getenv('WEB_SERVER_PORT') . '/';
    }

    public function testRoute()
    {
        $routes = [
            'get' => 'news/1',
            'get' => 'add',
            'post' => 'add',
        ];

        try {
            foreach ($routes as $method => $route) {
                call_user_func_array(['\\Kotori\\Tests\\Util', $method], [self::$END_POINT . $route]);
            }
        } catch (Exception $e) {
            $this->fail();
        }

        $this->assertTrue(true);
    }
}
