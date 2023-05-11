<?php

namespace App\Repositories\Page;

use App\Models\Page\Page;
use Illuminate\Database\Eloquent\Collection;

class PageRepository implements PageRepositoryInterface
{
    protected Page $model;

    public function __construct(Page $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }
}
