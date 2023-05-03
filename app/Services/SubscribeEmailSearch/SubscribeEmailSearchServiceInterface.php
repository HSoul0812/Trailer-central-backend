<?php

declare(strict_types=1);

namespace App\Services\SubscribeEmailSearch;

use App\DTOs\SubscribeEmailSearch\SubscribeEmailSearchDTO;
use App\Models\SubscribeEmailSearch\SubscribeEmailSearch;

interface SubscribeEmailSearchServiceInterface
{
    /**
     * @param array
     */
    public function send(array $params): SubscribeEmailSearch;

    /**
     * @param array
     */
    public function fill(array $params): SubscribeEmailSearchDTO;
}
