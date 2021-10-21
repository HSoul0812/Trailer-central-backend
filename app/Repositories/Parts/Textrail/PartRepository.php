<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Part;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Repositories\Parts\PartRepository as BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class PartRepository extends BaseRepository implements PartRepositoryInterface
{
    /**     
     * @var App\Models\Parts\Textrail\Part
     */
    protected $model;

    public function __construct(Part $model) {
        $this->model = $model;
    }

    public function getById(int $id) : Part
    {
        return $this->model->findOrFail($id);
    }

    public function createOrUpdateBySku(array $params) : Part
    {
        return $this->model->updateOrCreate(['sku' => $params['sku']], $params);
    }

    public function getBySku($sku) {
        return $this->model->where('sku', $sku)->first();
    }

    public function update($params) {
        $part = $this->model->find($params['id']);
        $part->fill(Arr::except($params, 'id'));
        $part->save();
        return $part;
    }

}
