<?php

namespace App\Services;

class RequestAgentService implements RequestAgentServiceInterface
{
    public function getUserAgent(): string
    {
        return request()->header('User-Agent') ?? '';
    }
}
