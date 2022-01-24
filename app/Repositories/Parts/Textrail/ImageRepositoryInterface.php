<?php

namespace App\Repositories\Parts\Textrail;

use \App\Repositories\Repository;

interface ImageRepositoryInterface extends Repository {

    public function firstOrCreate(array $params, string $fileName, string $imageData);

}