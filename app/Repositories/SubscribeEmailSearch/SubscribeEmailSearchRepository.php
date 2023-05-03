<?php

declare(strict_types=1);

namespace App\Repositories\SubscribeEmailSearch;

use App\Models\SubscribeEmailSearch\SubscribeEmailSearch;

class SubscribeEmailSearchRepository implements SubscribeEmailSearchRepositoryInterface
{
    /**
     * @var App\Models\SubscribeEmailSearch\SubscribeEmailSearch
     */
    protected $model;

    public function __construct(SubscribeEmailSearch $model)
    {
        $this->model = $model;
    }

    public function create($params): SubscribeEmailSearch
    {
        return $this->model->create($params);
    }
}
