<?php

namespace App\Services\Dealers;

use Http;

class DealerService implements DealerServiceInterface
{
    public function listByName(string $name): array
    {
        return Http::tcApi()->get('/users-by-name', [
            'name' => $name,
        ])->json('data');
    }
}
