<?php

namespace App\Repositories\Page;

use Illuminate\Database\Eloquent\Collection;

interface PageRepositoryInterface
{
    public function getAll(): Collection;
}
