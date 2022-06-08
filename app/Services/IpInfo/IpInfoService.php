<?php

namespace App\Services\IpInfo;

use App\DTOs\IpInfo\City;
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class IpInfoService implements IpInfoServiceInterface
{
    public function city(string $ip): City {
        return Cache::remember("ipinfo/city/$ip", 300, function () use($ip) {
            $localDisk = Storage::disk('local');
            $naDBPath = 'GeoIP2-City-North-America.mmdb';
            $allDBPath = 'GeoLite2-City.mmdb';
            $db = $naDBPath;
            if(!$localDisk->exists($naDBPath) && $localDisk->exists($allDBPath)) {
                $db = $allDBPath;
            }

            $reader = new Reader(Storage::disk('local')->path($db));
            return City::fromGeoIP2City($reader->city($ip));
        });
    }

    public function getRemoteIPAddress(): ?string
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return null;
    }
}
