<?php

namespace App\Repositories\Parts\Textrail;

use \App\Repositories\Repository;

interface CategoryRepositoryInterface extends Repository {

    public function firstOrCreate($params);

}