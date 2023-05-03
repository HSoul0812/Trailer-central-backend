<?php

namespace App\Http\Controllers\v1\WebsiteUser;

use App\Http\Requests\WithRequestBindings;
use App\Transformers\WebsiteUser\WebsiteUserTransformer;
use Dingo\Api\Routing\Helpers;

class ProfileController
{
    use Helpers;
    use WithRequestBindings;

    public function __construct(private WebsiteUserTransformer $transformer)
    {
    }

    protected function constructRequestBindings(): void
    {
    }
}
