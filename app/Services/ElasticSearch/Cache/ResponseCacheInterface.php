<?php

namespace App\Services\ElasticSearch\Cache;

interface ResponseCacheInterface
{
    /**
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function set(string $key, string $value);

    /**
     * @param string ...$keys
     */
    public function forget(string ...$keys);
}
