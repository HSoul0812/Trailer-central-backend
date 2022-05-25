<?php

declare(strict_types=1);

namespace App\Repositories\SubscribeEmailSearch;

use Illuminate\Database\Eloquent\Collection;
use App\Models\SubscribeEmailSearch\SubscribeEmailSearch;

interface SubscribeEmailSearchRepositoryInterface
{
  public function create($params): SubscribeEmailSearch;
}