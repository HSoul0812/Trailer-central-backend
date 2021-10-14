<?php

namespace App\Repositories\Parts\Textrail;

use \App\Repositories\Repository;

interface TypeRepositoryInterface extends Repository {

    public function firstOrCreate($params);

}