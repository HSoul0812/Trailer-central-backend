<?php

namespace App\Services;

interface RequestAgentServiceInterface
{
    public function getUserAgent(): string;
}
