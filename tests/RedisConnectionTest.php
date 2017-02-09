<?php

use PHPUnit\Framework\TestCase;
use Cake\Redis\RedisConnection;
use Cake\Redis\RedisLogger;

class RedisConnectionTest extends TestCase
{

    protected $connection;

    public function setUp()
    {
        $config = [
            'host' => env('REDIS_HOST'),
            'port' => env('REDIS_PORT'),
            'driver' => env('REDIS_DRIVER') ?: 'phpredis',
            'persistent' => env('REDIS_PERSISTENT') ? true : false
        ];
        $this->connection = new RedisConnection($config);
    }

    public function testConnection()
    {
        $this->assertNotEmpty($this->connection->driver());
    }

    public function testSimpleCommand()
    {
        $this->connection->set('cake', 'awesome');
        $this->assertEquals('awesome', $this->connection->get('cake'));
    }

    public function testTransaction()
    {
        $result = $this->connection->transactional(function ($pipeline) {
            return $pipeline
                ->set('first', 'value')
                ->set('second', 'value2')
                ->get('first');
        });

        $this->assertEquals('value', $result[2]);
    }

    public function testLog()
    {
        $this->connection->logQueries(true);
        $logger = $this->getMockBuilder(RedisLogger::class)
            ->setMethods(['log'])
            ->getMock();
        $this->connection->logger($logger);

        $logger->expects($this->at(0))
            ->method('log')
            ->willReturnCallback(function ($command) {
                $this->assertEquals($command->query, 'SET cake awesome');
                $this->assertTrue($command->took > 0);
            });

        $logger->expects($this->at(1))
            ->method('log')
            ->willReturnCallback(function ($command) {
                $this->assertEquals($command->query, 'GET foo');
                $this->assertTrue($command->took > 0);
            });

        $logger->expects($this->at(2))
            ->method('log')
            ->willReturnCallback(function ($command) {
                $this->assertEquals($command->query, 'MGET cake foo bar');
                $this->assertTrue($command->took > 0);
                $this->assertEquals($command->numRows, 3);
            });

        $this->connection->set('cake', 'awesome');
        $this->connection->get('foo');

        $this->connection->mget(['cake', 'foo', 'bar']);
    }
}
