<?php

namespace App\Helpers;

/**
 * Class IpHelper
 * @package App\Helpers
 */
class IpHelper
{
    /**
     * @const array<string> Session Keys to CHeck for IP In
     */
    const IP_SESSION_KEYS = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    /**
     * Find IP For Existing Dealer
     *
     * @return null|string
     */
    public function findIp(): ?string
    {
        // Get Correct IP!
        foreach (self::IP_SESSION_KEYS as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }

        // Return Response IP
        return null;
    }
}
