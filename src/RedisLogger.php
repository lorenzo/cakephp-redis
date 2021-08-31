<?php

namespace Cake\Redis;

use Cake\Log\Log;

class RedisLogger implements \Psr\Log\LoggerInterface
{
    /**
     * Writes a logged command into a log
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency($message, array $context = array()): void
    {
        $this->log('error',$message,[]);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert($message, array $context = array()): void
    {
        $this->log('error',$message,[]);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical($message, array $context = array()): void
    {
        $this->log('error',$message,[]);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = array()): void
    {
        $this->log('error',$message,[]);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning($message, array $context = array()): void
    {
        $this->log('debug',$message,[]);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice($message, array $context = array()): void
    {
        $this->log('debug',$message,[]);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = array()): void
    {
        $this->log('debug',$message,[]);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, array $context = array()): void
    {
        $this->log('debug',$message,[]);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level = 'debug', $message, array $context = array()): void
    {
        Log::write($level, $message, ['redisLog']);
    }
}
