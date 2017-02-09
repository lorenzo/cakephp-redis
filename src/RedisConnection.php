<?php

namespace Cake\Redis;

use Cake\Redis\Driver\PHPRedisDriver;
use Cake\Redis\Driver\PredisDriver;
use Cake\Datasource\ConnectionInterface;
use Cake\Core\App;

class RedisConnection implements ConnectionInterface, DriverInterface
{
    /**
     * The actual redis client to use for running commands
     *
     * @var mixed
     */
    protected $_driver;

    /**
     * Conncetion configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * Whether or not to log commands
     *
     * @var bool
     */
    protected $_logQueries = false;

    /**
     * The logger object
     *
     * @var mixed
     */
    protected $_logger;

    /**
     * Connects to Redis using the specified driver
     */
    public function __construct($config)
    {
        $config += ['driver' => PHPRedisDriver::class];
        $this->_config = $config;
        $this->driver($config['driver'], $config);

        if (!empty($config['log'])) {
            $this->logQueries($config['log']);
        }
    }

    /**
     * Sets the driver instance. If a string is passed it will be treated
     * as a class name and will be instantiated.
     *
     * If no params are passed it will return the current driver instance.
     *
     * @param \Cake\Redis\DriverInterface|string|null $driver The driver instance to use.
     * @param array $config Either config for a new driver or null.
     * @throws \Cake\Datasource\Exception\MissingDatasourceException When a driver class is missing.
     * @return \Cake\Redis\DriverInterface
     */
    public function driver($driver = null, $config = [])
    {
        if ($driver === null) {
            return $this->_driver;
        }

        if (is_string($driver)) {

            if ($driver === 'phpredis') {
                $driver = PHPRedisDriver::class;
            }

            if ($driver === 'predis') {
                $driver = PredisDriver::class;
            }

            $className = App::className($driver, 'Redis/Driver');

            if (!$className || !class_exists($className)) {
                throw new MissingDatasourceException(['driver' => $driver]);
            }

            $driver = new $className($config);
        }

        return $this->_driver = $driver;
    }

    /**
     * {@inheritDoc}
     */
    public function config()
    {
        return $this->_config;
    }

    /**
     * {@inheritDoc}
     */
    public function configName()
    {
        if (empty($this->_config['name'])) {
            return '';
        }
        return $this->_config['name'];
    }

    /**
     * {@inheritDoc}
     */
    public function logQueries($enable = null)
    {
        if ($enable === null) {
            return $this->_logQueries;
        }
        $this->_logQueries = $enable;
    }

    /**
     * {@inheritDoc}
     */
    public function logger($instance = null)
    {
        if ($instance === null) {
            if ($this->_logger === null) {
                $this->_logger = new RedisLogger();
            }
            return $this->_logger;
        }
        $this->_logger = $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function transactional(callable $operation)
    {
        return $this->driver()->transactional($operation);
    }

    /**
     * {@inheritDoc}
     */
    public function disableConstraints(callable $operation)
    {
        return $operation($this);
    }

    /**
     * Does the actual command excetution in the Redis driver
     *
     * @param string $method the command to execute
     * @param arrat $parameters the parameters to pass to the driver command
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $callable = [$this->driver(), $method];

        if ($this->_logQueries) {
            $callable = function (...$parameters) use ($method, $callable) {
                $command = new LoggedCommand($method, $parameters);
                $start = microtime(true);

                $result = $callable(...$parameters);

                $ellapsed = microtime(true) - $start;
                $command->took = $ellapsed;
                $command->numRows = 1;

                if ($result === false) {
                    $command->numRows = 0;
                }

                if (is_array($result)) {
                    $command->numRows = count($result);
                }

                $this->logger()->log($command);

                return $result;
            };
        }

        return $callable(...$parameters);
    }
}
