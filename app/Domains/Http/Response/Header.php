<?php

namespace App\Domains\Http\Response;

class Header
{
    /**
     * @var array<string, string>
     */
    private array $queue = [];

    public static function queue(string $key, string $value): void
    {
        resolve(Header::class)->addQueue($key, $value);
    }

    public function addQueue(string $key, string $value): void
    {
        $this->queue[$key] = $value;
    }

    public function getQueue(): array
    {
        return $this->queue;
    }
}
