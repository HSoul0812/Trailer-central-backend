<?php

namespace App\Repositories\User;

use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface DealerUserRepositoryInterface
 * @package App\Repositories\User
 */
interface DealerUserRepositoryInterface extends Repository 
{
    public function getByDealer(int $dealerId) : Collection;
    
    public function updateBulk(array $params) : Collection;
}
