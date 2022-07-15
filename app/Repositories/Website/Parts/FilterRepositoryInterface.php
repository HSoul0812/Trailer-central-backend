<?php

namespace App\Repositories\Website\Parts;

use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

/**
 *
 *
 * @author Eczek
 */
interface FilterRepositoryInterface extends Repository {
    public function getAllEcomm(): Collection;
}
