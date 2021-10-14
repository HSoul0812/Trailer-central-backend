<?php

namespace App\Repositories\Parts\Textrail;

use \App\Repositories\Repository;

interface ManufacturerRepositoryInterface extends Repository {

    public function firstOrCreate($params);

}