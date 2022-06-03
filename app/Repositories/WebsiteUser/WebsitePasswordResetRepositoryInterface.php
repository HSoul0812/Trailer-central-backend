<?php

namespace App\Repositories\WebsiteUser;

use App\Repositories\CrudCommonWriterRepositoryInterface;

interface WebsitePasswordResetRepositoryInterface
{
    public function create(array $attributes);
}
