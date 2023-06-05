<?php

namespace App\Services;

class RequestAgentService
{
    public static function getUserAgent(): string
    {
        return request()->header('User-Agent');
    }
}
