<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tunnels
    |--------------------------------------------------------------------------
    |
    | Tunnels config details
    |
    | Used for marketing clients/extensions like HTW and Facebook Marketplace
    |
    */

    // Type of Tunnel to Use
    'type' => env('TUNNEL_TYPE', 'socks4'),

    // Host/IP Where Tunnel Connects Through
    'host' => env('TUNNEL_HOST', 'dealer-tunnel.trailercentral.com'),

    // Max Ping Delay Before We Assumed Its Disconnected
    'max_ping' => env('TUNNEL_MAX_PING', 300),
];