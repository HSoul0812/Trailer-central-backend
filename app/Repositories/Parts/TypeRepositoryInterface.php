<?php

namespace App\Repositories\Parts;

use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

interface TypeRepositoryInterface
{
  public function getAll($params): Collection;
}