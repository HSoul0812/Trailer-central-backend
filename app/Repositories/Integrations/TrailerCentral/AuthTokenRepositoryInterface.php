<?php

namespace App\Repositories\Integrations\TrailerCentral;

use Illuminate\Database\Eloquent\Model;

interface AuthTokenRepositoryInterface
{
    public function get(array $params): Model|null;
}
