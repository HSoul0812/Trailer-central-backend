<?php

namespace App\Services\IpInfo;

use App\DTOs\IpInfo\City;
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class IpInfoService implements IpInfoServiceInterface
{
    const DB_PATHS = [
        'GeoIP2-City-North-America.mmdb',
        'GeoLite2-City.mmdb',
        'maxmind/GeoLite2-City.mmdb',
    ];

    public function city(string $ip): City {
        return Cache::remember("ipinfo/city/$ip", 300, function () use($ip) {
            $storage = Storage::disk('local');

            // Get the first path that exist in the storage
            $path = collect(self::DB_PATHS)->first(
                callback: fn(string $path) => $storage->exists($path)
            );

            $reader = new Reader($storage->path($path));

            return City::fromGeoIP2City(
                city: $reader->city($ip)
            );
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
