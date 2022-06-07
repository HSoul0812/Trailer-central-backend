<?php

namespace App\Repositories\WebsiteUser;

use App\Repositories\CrudCommonWriterRepositoryInterface;

interface WebsiteUserRepositoryInterface extends CrudCommonWriterRepositoryInterface
{
    public function get($attributes);
    public function findOrFail($userId);
}
