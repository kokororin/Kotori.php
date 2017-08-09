<?php
namespace Kotori\Core;

use ReflectionClass;

class Container
{
    /**
     * container instances
     *
     * @var array
     */
    protected $containers = [];

    /**
     * bind maps
     *
     * @var array
     */
    protected $bind = [];

    /**
     * instance handle
     *
     * @var object
     */
    protected static $instance;

    /**
     * Get singleton
     *
     * @return \Kotori\Core\Container
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Set the object instance in the container
     *
     * @param string $abstract
     * @param string $concrete
     */
    public static function set($abstract, $concrete)
    {
        self::getInstance()->containers[$abstract] = $concrete;
    }

    /**
     * Get the object instance in the container
     *
     * @param  string $abstract
     * @return object
     */
    public static function get($abstract)
    {
        if (!isset(self::getInstance()->containers[$abstract])) {
            $reflect = new ReflectionClass(self::getInstance()->bind[$abstract]);
            self::set($abstract, $reflect->newInstanceArgs([]));
        }

        return self::getInstance()->containers[$abstract];
    }

    /**
     * bind object maps for the container
     *
     * @param  mixed  $abstract
     * @param  object $concrete
     * @return \Kotori\Core\Container
     */
    public function bind($abstract, $concrete = null)
    {
        if (is_array($abstract)) {
            $this->bind = array_merge($this->bind, $abstract);
        } else {
            $this->bind[$abstract] = $concrete;
        }

        return $this;
    }
}
