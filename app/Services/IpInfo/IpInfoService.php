<?php

namespace App\Services\IpInfo;

use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class IpInfoService implements IpInfoServiceInterface
{
    public function city(string $ip): array {
        return Cache::remember("ipinfo/city/$ip", 300, function () use($ip) {
            $localDisk = Storage::disk('local');
            $naDBPath = 'GeoIP2-City-North-America.mmdb';
            $allDBPath = 'GeoLite2-City.mmdb';
            $db = $naDBPath;
            if(!$localDisk->exists($naDBPath) && $localDisk->exists($allDBPath)) {
                $db = $allDBPath;
            }

            $reader = new Reader(Storage::disk('local')->path($db));
            return $reader->city($ip);
        });
    }

    public function getRemoteIPAddress()
    {
        if(isset($_GET['x-remote-addr'])) {
            return $_GET['x-remote-addr'];
        }

        $list = $this->getRemoteAddrList();
        return empty($list) ? null : $list[0];
    }

    protected function getRemoteAddrList(): array
    {
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return $ipList;
        }

        $ipList = array();

        if(!$this->isLanAddress($_SERVER['REMOTE_ADDR'])) {
            array_unshift($ipList, $_SERVER['REMOTE_ADDR']);
        }

        if(isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ipList = array($_SERVER['HTTP_X_REAL_IP']);
        }

        return $ipList;
    }

    protected function isLanAddress($ip): bool
    {
        $compare = ip2long($ip);

        foreach([
                    [0xA000000,  0xAFFFFFF ],  //     10.0.0.0/8    10.0.0.0 -  10.255.255.255           (single class A)
                    [0xAC100000, 0xAC1FFFFF],  //  172.16.0.0/12  172.16.0.0 -  172.16.255.255  (16 contiguous class B's)
                    [0xC0A80000, 0xC0A8FFFF]   // 192.168.0.0/16 192.168.0.0 - 192.168.255.255 (256 contiguous class C's)
                ] as $range) {
            if($compare >= $range[0] && $compare <= $range[1]) {
                return true;
            }
        }

        return false;
    }

}
