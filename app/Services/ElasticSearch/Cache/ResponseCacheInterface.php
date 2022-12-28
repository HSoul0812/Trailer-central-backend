<?php

namespace App\Services\ElasticSearch\Cache;

interface ResponseCacheInterface
{
    /**
     * @param  string  $key
     * @param  string  $value
     * @return mixed
     */
    public function set(string $key, string $value);

    /**
     * @param  string  ...$keyPatterns
     */
    public function forget(string ...$keyPatterns);

    /**
     * @param  string  ...$keyPatterns
     */
    public function invalidate(string ...$keyPatterns);
}
