<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Part;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Repositories\Parts\PartRepository as BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class PartRepository extends BaseRepository implements PartRepositoryInterface
{
    /**
     * @var \App\Models\Parts\Textrail\Part
     */
    protected $model;

    public function __construct(Part $model) {
        parent::__construct($model);

        $this->model = $model;
    }

    public function getById(int $id) : ?Part
    {
        return $this->model->find($id);
    }

    public function createOrUpdateBySku(array $params) : Part
    {
        return $this->model->updateOrCreate(['sku' => $params['sku']], $params);
    }

    public function getBySku($sku) {
        return $this->model->where('sku', $sku)->first();
    }

    /**
     * @param  array  $ids
     * @return Collection|array<Part>
     */
    public function getAllByIds(array $ids): Collection
    {
        return $this->model->whereIn('id', $ids)->get();
    }
}
