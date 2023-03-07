<?php

namespace App\Repositories\User;

use App\Models\User\DealerUser;
use App\Models\User\User;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface DealerUserRepositoryInterface
 * @package App\Repositories\User
 */
interface DealerUserRepositoryInterface extends Repository
{
    public function getByDealer(int $dealerId): Collection;

    public function getByDealerEmail(string $dealerEmail): ?User;

    public function updateBulk(array $params): Collection;

    /**
     * @param array{dealer_user_id: int} $params
     * @return DealerUser
     * @throws \InvalidArgumentException when dealer_id is not provided
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function get($params): DealerUser;
}
