# A Redis connection adapter for CakePHP

This library makes it possible to create connections for any Redis database that can be created
and managed with CakePHP's `ConnectionManager`.

Redis is a great Key-Value database with excelent performance. It also offers several unique features
for working with large lists, hashmaps and even pub-sub systems.

## Connecting to the redis database

This library assumes that you either have installed `phpredis` or `predis`. We recommend installing `phpredis`

* [PhpRedis](https://github.com/phpredis/phpredis) (recommended)
* [Predis](https://github.com/phpredis/phpredis)

### Configuring it in in your app.php file in

Just add any new named configuration under the `Datasources` key

```php
    'Datasources' => [
        ...
        'redis' => [
            'className' => 'Cake\Redis\RedisConnection',
            'driver' => 'phpredis', // Can also use a full class name or 'predis'
            'log' => false, // Log executed commands
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => [optional],
            'reconnectionDelay' => [optional],
            'persistentId' => [optional],
            'database' => [optional],
            'options' => [], // extra options for the driver
        ]
    ]
```

### Using ConnectionManager

You can also create new redis connections with the ``ConnectionManger``

```php
use Cake\Datasource\ConnectionManager;

ConnectionManager::config('redis', [
    'className' => 'Cake\Redis\RedisConnection',
    'driver' => 'Cake\Redis\Drier\PHPRedisDriver',
    'log' => false, // Log executed commands
    'host' => '127.0.0.1',
    'port' => 6379,
    'timeout' => [optional],
    'reconnectionDelay' => [optional],
    'persistentId' => [optional],
    'database' => [optional],
    'options' => [], // extra options for the driver
]);

```

## Executing commands in Redis

You need to get a hold of the connection and the execute commands:

```php
$redis = ConnectionManager::get('redis');
$redis->set('cakephp', 'awesome');
echo $redis->get('cakephp'); // Returns 'awesome'
```

Make sure you take a look at all the commands you can run in the [PHPRedis Reame](https://github.com/phpredis/phpredis/blob/develop/README.markdown).

## Executing a list of commands in a transaction

```php

$redis = ConnectionManager::get('redis');
$redis->transactional(function ($client) {
    $client
        ->set('cakephp', 'awesome')
        ->set('another_key', 'value')
});
```

The client instance the closure gets will buffer all the commands and execute them
atomically at the end of the chain.

This method uses the `multi()` command internally.
