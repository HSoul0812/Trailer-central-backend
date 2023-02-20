<?php

namespace App\Services\ElasticSearch\Cache;

use App\Jobs\ElasticSearch\Cache\InvalidateCacheJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use \Redis as PhpRedis;

class RedisResponseCache implements ResponseCacheInterface
{
    use DispatchesJobs;

    public const HASH_SCAN_COUNTER = 10000;

    public const SEARCH_HASHMAP_KEY = 'inventory_search_hashmap';

    public const SINGLE_HASHMAP_KEY = 'inventory_single_hashmap';

    /** @var PhpRedis */
    private $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function set(string $key, $value): void
    {
        // it stores a new empty field within a hashmap using long key name
        $this->client->hSet($this->extractHashKey($key), $key, '');

        // it stores a new key-value using an exact key name which is known by the cache client (DW)
        $this->client->set(
            $this->extractExactKey($key),
            gzencode($value, (int)config('elastic.scout_driver.cache.compression_level', 9)),
            (int)config('elastic.scout_driver.cache.ttl', 86400)
        );
    }

    /**
     * Dispatch all jobs given the key patterns.
     *
     * @param string ...$keyPatterns a list of key patterns
     * @return void
     */
    public function forget(string ...$keyPatterns): void
    {
        $this->dispatch(new InvalidateCacheJob($keyPatterns));
    }

    /**
     * Invalidates all keys which match with key pattern list in synchronous way.
     *
     * This method perform the key list iteration (hash scan) in Laravel side because it will not block the Redis server,
     * we could have done this at Lua side, but sadly it blocks server becoming unresponsive
     *
     * @param string ...$keyPatterns a list of key patterns
     * @return void
     */
    public function invalidate(string ...$keyPatterns): void
    {
        foreach ($keyPatterns as $pattern) {
            if ($pattern === RedisResponseCacheKey::CLEAR_ALL_PATTERN &&
                !in_array((int)$this->client->getDbNum(), [0, 1, 2, 3], true)
            ) {
                $this->client->flushDB();

                return; // since it will flush the DB, we dont need to continue
            }

            $hashKey = $this->extractHashKey($pattern);

            if (preg_match('/inventories\.single\.\d+\.dealer:\d+$/', $pattern)) {
                $this->client->unlink($this->extractExactKey($pattern));
                $this->client->hDel($hashKey, $pattern);

                continue;
            }

            $this->hScanAndUnlink($hashKey, $pattern);
        }
    }

    private function getExactKeysFromLongKeyNames(array $patterns): array
    {
        return array_map(
            function (string $pattern): string {
                return $this->extractExactKey($pattern);
            }, $patterns
        );
    }

    private function hScanAndUnlink(string $hashKey, string $pattern): void
    {
        /** @var null|int $cursor */
        $cursor = null;

        while ($elements = $this->client->hScan($hashKey, $cursor, $pattern, self::HASH_SCAN_COUNTER)) {
            $keys = array_keys($elements); // it only needs the key

            $this->client->unlink($this->getExactKeysFromLongKeyNames($keys)); // delete by exact key names
            $this->client->hDel($hashKey, ...$keys); // delete keys from hashmap
        }
    }

    /**
     * @param string $key
     * @return string an exact key like `inventories.search.bbb02f1f9dcd91350272e6e4f42150150` or `inventories.single.3207402`
     */
    private function extractExactKey(string $key): string
    {
        $parts = explode('.', $key);

        return sprintf('inventories.%s.%s', $parts[1], $parts[2]);
    }

    /**
     * @param string $key
     * @return string a hash key, `inventory_search_list` or `inventory_single_list`
     */
    private function extractHashKey(string $key): string
    {
        $parts = explode('.', $key);

        return count($parts) > 1 && $parts[1] === 'search' ? self::SEARCH_HASHMAP_KEY : self::SINGLE_HASHMAP_KEY;
    }
}
