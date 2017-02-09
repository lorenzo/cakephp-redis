<?php

namespace Cake\Redis;

use Cake\Log\Log;

class RedisLogger
{
    /**
     * Writes a logged command into a log
     *
     * @param mixed $command to be written in log
     * @return void
     */
    public function log($loggedCommand)
    {
        Log::write('debug', $loggedCommand, ['redisLog']);
    }
}
