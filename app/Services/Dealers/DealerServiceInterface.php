<?php

namespace App\Services\Dealers;

use App\DTOs\Dealer\TcApiResponseDealer;
use Illuminate\Support\Collection;

interface DealerServiceInterface
{
    /**
     * @return Collection<int, TcApiResponseDealer>
     */
    public function listByName(string $name): Collection;
}
