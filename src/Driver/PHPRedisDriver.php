<?php

namespace Cake\Redis\Driver;

use Cake\Redis\DriverInterface;

use Redis;

class PHPRedisDriver extends Redis implements DriverInterface
{
    /**
     * Initializes the Redis client
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $config = array_filter($config);
        $config += [
            'host' => '127.0.0.1',
            'port' => null,
            'timeout' => null,
            'reconnectionDelay' => null,
            'persistentId' => null,
            'database' => 0,
            'options' => [],
        ];

        $connected = false;

        if (empty($config['persistent'])) {
            $connected = $this->connect($config['host'], $config['port'], $config['timeout'], $config['reconnectionDelay']);
        }

        if (!empty($config['persistent'])) {
            $connected = $this->pconnect($config['host'], $config['port'], $config['timeout'], $config['persistentId']);
        }

        if (!$connected) {
            throw new \RuntimeException("Could not connect to the redis server at {$config['host']}");
        }

        if (!empty($config['password']) && !$this->auth($config['password'])) {
            throw new \RuntimeException("Could not authenticate to the redis server at {$config['host']}");
        }

        if (isset($config['database'])) {
            $this->select($config['database']);
        }

        foreach ($config['options'] as $option => $value) {
            $this->setOption($option, $value);
        }
    }

    /**
     * Starts a multi command and calls the provided callable by passing the
     * pipeline object as first parameter.
     *
     * @param callable $operation Callable that will get the Redis client as first parameter
     * @param array The result of each command executed in the trasaction
     */
    public function transactional(callable $operation)
    {
        $transaction = $this->multi();

        try {
            $operation($transaction);
            return $transaction->exec();
        } catch (\Exception $e) {
            $transaction->discard();
            throw $e;
        }
    }
    
    public function setLogger() {}
    public function getLogger() {}
}
