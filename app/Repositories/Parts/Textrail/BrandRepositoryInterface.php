<?php

namespace App\Repositories\Parts\Textrail;

use \App\Repositories\Repository;

interface BrandRepositoryInterface extends Repository {

    public function firstOrCreate($params);

}