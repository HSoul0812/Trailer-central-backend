<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Part;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Repositories\Parts\PartRepository as BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class PartRepository extends BaseRepository implements PartRepositoryInterface
{
    /**
     * @param int $id
     * @return Part
     */
    public function getById($id) {
        return Part::findOrFail($id);
    }

    /**
     * @param  array  $ids
     * @return Collection|array<Part>
     */
    public function getAllByIds(array $ids): Collection
    {
        return Part::query()->whereIn('id', $ids)->get();
    }
}
