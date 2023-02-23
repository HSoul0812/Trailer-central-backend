<?php

namespace App\Services\Dealers;

use Http;

class DealerService implements DealerServiceInterface
{
    const ENDPOINT_USERS_BY_NAME = '/users-by-name';
    
    public function listByName(string $name): ?array
    {
        return Http::tcApi()->get(self::ENDPOINT_USERS_BY_NAME, [
            'name' => $name,
        ])->json('data');
    }
}
