<?php

/*
  |---------------------------------------------------------------------------------------------------------------------
  | Credentials used by inventory-integration processes
  |---------------------------------------------------------------------------------------------------------------------
  |
  | `integration_client_id` is used to identified every single request coming from integration processes, so the backend
  | is able to avoid performing certain process like dispatching many Scout-ES/Cache-invalidation jobs
  |
  */
return [
    'credentials' => [
        'access_token' => env('INVENTORY_CACHE_ACCESS_TOKEN', '70e0a5a0630261c2e4428664aa2c9db575b3f16a'),
        'integration_client_id' => env('INVENTORY_INTEGRATION_CLIENT_ID', '70e0a5a0630261c2e4428664aa2c9db575b3f16a')
    ],
];
