<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Part;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Repositories\Parts\PartRepository as BaseRepository;

class PartRepository extends BaseRepository implements PartRepositoryInterface
{
    public function getById($id) {
        return Part::findOrFail($id);
    }
}
