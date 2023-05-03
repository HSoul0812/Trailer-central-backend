<?php

namespace App\Repositories\Integrations\TrailerCentral;

use Illuminate\Database\Eloquent\Model;
use stdClass;

interface AuthTokenRepositoryInterface
{
    public function get(array $params): Model|stdClass|null;
}
