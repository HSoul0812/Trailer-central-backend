<?php

namespace App\Services\ElasticSearch\Cache;

use Illuminate\Support\Facades\Redis;
use \Redis as PhpRedis;

class RedisResponseCache implements ResponseCacheInterface
{
    public const TTL = 172800; //2 days
    public const CURSOR_LIMIT = 1000;

    /**
     * @param  string  $key
     * @param $value
     * @return void
     */
    public function set(string $key, $value)
    {
        Redis::set($key, $value, 'EX', self::TTL);
    }

    /**
     * Invalidates all keys which match with key pattern list
     * This method perform the key list iteration in Laravel side because it will not block the Redis server,
     * we could have done this at Lua side, but sadly it blocks server making it unresponsive
     *
     * @param  string  ...$keyPatterns  a list of key patterns
     * @return void
     */
    public function forget(string ...$keyPatterns): void
    {
        /**  @var PhpRedis */
        $client = Redis::client();

        $prefix = $client->getOption(PhpRedis::OPT_PREFIX);
        $start = new \DateTime();
        $keysInvalidated = 0;

        $removeKeyPrefix = static function (array $keys) use ($prefix): array {
            return array_map(
                static function (string $key) use ($prefix): string {
                    return str_replace($prefix, '', $key);
                }, $keys
            );
        };

        echo sprintf('STARTED: %s', $start->format('%H:%I:%S')).PHP_EOL;

        foreach ($keyPatterns as $pattern) {
            /** @var null|int $cursor */
            $cursor = null;

            $keys = $client->scan($cursor, $pattern);

            /**
             * @see https://stackoverflow.com/a/36920063/6082936
             *
             * We need to start by looking up using cursor zero, that means we have less than 10 keys,
             * otherwise we have more than 10 keys, so we could iterate using a greater cursor counter
             */
            if (!empty($keys)) {
                $keysInvalidated += count($keys);
                $client->unlink($removeKeyPrefix($keys));
            } else {
                while (false !== ($keys = $client->scan($cursor, $pattern, self::CURSOR_LIMIT))) {
                    $keysInvalidated += count($keys);
                    $client->unlink($removeKeyPrefix($keys));
                }
            }
        }

        $end = new \DateTime();

        echo sprintf('KEYS INVALIDATED: %d', $keysInvalidated).PHP_EOL;
        echo sprintf('TIME ELAPSED: %s', $start->diff($end)->format('%H:%I:%S')).PHP_EOL;
    }
}
