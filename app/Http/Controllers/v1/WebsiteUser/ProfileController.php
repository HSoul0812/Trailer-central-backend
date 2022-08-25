<?php

namespace App\Http\Controllers\v1\WebsiteUser;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\WebsiteUser\AuthenticateUserRequest;
use App\Http\Requests\WebsiteUser\UpdateUserRequest;
use App\Http\Requests\WithRequestBindings;
use App\Transformers\WebsiteUser\WebsiteUserTransformer;
use Dingo\Api\Routing\Helpers;
use Sentry\Response;

class ProfileController
{
    use Helpers;
    use WithRequestBindings;

    public function __construct(private WebsiteUserTransformer $transformer) {}



    protected function constructRequestBindings(): void
    {

    }
}
