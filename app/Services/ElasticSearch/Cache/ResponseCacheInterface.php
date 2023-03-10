<?php

namespace App\Services\ElasticSearch\Cache;

interface ResponseCacheInterface
{
    public function set(string $key, string $value): void;

    public function forget(string ...$keyPatterns): void;

    public function invalidate(string ...$keyPatterns): void;
}
