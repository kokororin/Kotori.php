<?php
namespace Kotori\Core;

use Kotori\Exception\ContainerException;
use ReflectionClass;
use ReflectionException;

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
     * Class constructor
     *
     * Bind default accessors
     *
     * @return void
     */
    public function __construct()
    {
        $this->bind([
            'cache' => \Kotori\Core\Cache::class,
            'config' => \Kotori\Core\Config::class,
            'controller' => \Kotori\Core\Controller::class,
            'request' => \Kotori\Http\Request::class,
            'response' => \Kotori\Http\Response::class,
            'route' => \Kotori\Http\Route::class,
            'trace' => \Kotori\Debug\Trace::class,
            'model/provider' => \Kotori\Core\Model\Provider::class,
            'logger' => \Kotori\Debug\Logger::class,
        ]);
    }

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
     *
     * @throws \Kotori\Exception\ContainerException
     */
    public static function get($abstract)
    {
        if (!isset(self::getInstance()->containers[$abstract])) {
            try {
                $reflect = new ReflectionClass(self::getInstance()->bind[$abstract]);
            } catch (ReflectionException $e) {
                throw new ContainerException('Cannot find "' . $abstract . '" in container');
            }
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
