<?php

namespace App\Services\IpInfo;

interface IpInfoServiceInterface
{
    public function city(string $ip);

    public function getRemoteIPAddress();
}
