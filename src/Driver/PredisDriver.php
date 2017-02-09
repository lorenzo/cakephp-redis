<?php

namespace Cake\Redis\Driver;

use Predis\Client;

class PredisDriver extends Client
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
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => null,
            'database' => 0,
            'options' => [],
        ];

        parent::__construct($config, $config['options']);
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
        $transaction = $this->transaction();

        try {
            $operation($transaction);
            return $transaction->execute();
        } catch (\Exception $e) {
            $transaction->discard();
            throw $e;
        }
    }
}
