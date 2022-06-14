<?php

namespace App\Repositories\WebsiteUser;

use App\Models\WebsiteUser\WebsiteUser;
use App\Repositories\CrudCommonWriterRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface WebsiteUserRepositoryInterface extends CrudCommonWriterRepositoryInterface
{
    public function get($attributes): Collection;
    public function findOrFail($userId): WebsiteUser;
}
