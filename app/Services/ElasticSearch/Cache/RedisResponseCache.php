<?php

namespace App\Services\ElasticSearch\Cache;

use App\Jobs\ElasticSearch\Cache\InvalidateCacheJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use \Redis as PhpRedis;

class RedisResponseCache implements ResponseCacheInterface
{
    use DispatchesJobs;

    public const TTL = 172800; //2 days
    public const CURSOR_LIMIT = 1000;

    /** @var PhpRedis */
    private $client;

    /**
     * @var UniqueCacheInvalidationInterface
     */
    private $uniqueCacheInvalidation;

    /**
     * @param PhpRedis $client
     * @param UniqueCacheInvalidationInterface $uniqueCacheInvalidation
     */
    public function __construct(PhpRedis $client, UniqueCacheInvalidationInterface $uniqueCacheInvalidation)
    {
        $this->client = $client;
        $this->uniqueCacheInvalidation = $uniqueCacheInvalidation;
    }

    /**
     * @param string $key
     * @param $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->client->set($key, $value, self::TTL);
    }

    /**
     * Invalidates all keys which match with key pattern list in asynchronous way.
     *
     * @param string ...$keyPatterns a list of key patterns
     * @return void
     */
    public function forget(string ...$keyPatterns): void
    {
        $keyPatterns = $this->uniqueCacheInvalidation->keysWithNoJobs($keyPatterns);

        if (count($keyPatterns)) {

            $this->uniqueCacheInvalidation->createJobsForKeys($keyPatterns);
            $this->dispatch(new InvalidateCacheJob($keyPatterns));
        }
    }

    /**
     * Invalidates all keys which match with key pattern list in synchronous way.
     *
     * This method perform the key list iteration in Laravel side because it will not block the Redis server,
     * we could have done this at Lua side, but sadly it blocks server making it unresponsive
     *
     * @param string ...$keyPatterns a list of key patterns
     * @return void
     */
    public function invalidate(string ...$keyPatterns): void
    {
        foreach ($keyPatterns as $pattern) {
            /** @var null|int $cursor */
            $cursor = null;

            $keys = $this->client->scan($cursor, $pattern);

            /**
             * @see https://stackoverflow.com/a/36920063/6082936
             *
             * We need to start by looking up using cursor zero, that means we have less than 10 keys,
             * otherwise we have more than 10 keys, so we could iterate using a greater cursor counter
             */
            if (!empty($keys)) {
                $this->client->unlink($this->removeKeyPrefix($keys));
            }

            $this->unlink($cursor, $pattern);
        }
    }

    /**
     * @param int  &$cursor
     * @param string $pattern
     * @return void number of deleted keys
     */
    private function unlink(int &$cursor, string $pattern)
    {
        while (false !== ($keys = $this->client->scan($cursor, $pattern, self::CURSOR_LIMIT))) {
            $this->client->unlink($this->removeKeyPrefix($keys));
        }
    }

    /**
     * @param array $keys list of keys with prefix
     * @return string[] list of keys without prefix
     */
    private function removeKeyPrefix(array $keys): array
    {
        $prefix = $this->client->getOption(PhpRedis::OPT_PREFIX);

        return array_map(
            static function (string $key) use ($prefix): string {
                return str_replace($prefix, '', $key);
            }, $keys
        );
    }
}
