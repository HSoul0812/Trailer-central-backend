<?php

namespace App\Services\ElasticSearch\Cache;

use Illuminate\Support\Facades\Redis;

class RedisResponseCache implements ResponseCacheInterface
{
    const TTL = 172800; //2 days

    /**
     * @param string $key
     * @param $value
     * @return void
     */
    public function set(string $key, $value)
    {
        Redis::set($key, $value, 'EX', self::TTL);
    }

    /**
     * @param string ...$keys
     * @return void
     */
    public function forget(string ...$keys)
    {
        Redis::pipeline(function ($pipe) use ($keys) {
            foreach ($keys as $key) {
                $pipe->eval("return redis.call('unlink', unpack(redis.call('keys','$key')))", []);
            }
        });
    }
}
