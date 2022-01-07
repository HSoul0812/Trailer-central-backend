<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Category;
use Illuminate\Support\Facades\DB;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**     
     * @var App\Models\Parts\Textrail\Category
     */
    protected $model;

    public function __construct(Category $model) {
        $this->model = $model;
    }

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function firstOrCreate(array $params) : Category
    {
        return $this->model->firstOrCreate($params);
    }

}
