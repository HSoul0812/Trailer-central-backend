<?php

namespace App\Domains\Throttle;

use Dingo\Api\Http\RateLimit\Handler as DingoHandler;

class Handler extends DingoHandler
{
    protected function key($key): string
    {
        return sprintf('dingo.api.%s.%s.%s', $key, $this->keyPrefix, $this->getRateLimiter());
    }
}
