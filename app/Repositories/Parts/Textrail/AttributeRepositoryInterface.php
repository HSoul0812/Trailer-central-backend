<?php

namespace App\Repositories\Parts\Textrail;

use \App\Repositories\Repository;

interface AttributeRepositoryInterface extends Repository
{
    public function firstOrCreate(array $params);
}
