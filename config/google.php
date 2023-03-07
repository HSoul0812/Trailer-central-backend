<?php

return [
    'maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
        'url' => env('GOOGLE_MAPS_URL', 'https://maps.googleapis.com/maps/api/geocode/json')
    ]
];
