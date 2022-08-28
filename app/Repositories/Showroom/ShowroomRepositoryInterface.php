<?php

namespace App\Repositories\Showroom;

use App\Repositories\Repository;
use Illuminate\Support\Collection;

interface ShowroomRepositoryInterface extends Repository {

    /**
     * @return Collection List of manufactures names
     */
    public function distinctByManufacturers(): Collection;
}
