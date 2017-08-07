<?php
namespace Kotori\Core;

abstract class Container
{
    /**
     * facade containers
     *
     * @var array
     */
    protected static $_containers = [];

    /**
     * Set facade instances
     *
     * @param string $name
     */
    protected static function set($name)
    {
        self::$_containers[$name] = new $name;
    }

    /**
     * get facade instances
     *
     * @param  string $name
     * @return \Kotori\Core\Facade
     */
    public static function get($name)
    {
        if (!isset(self::$_containers[$name])) {
            self::set($name);
        }

        return self::$_containers[$name];
    }
}
