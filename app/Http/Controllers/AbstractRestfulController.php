<?php

namespace App\Http\Controllers;

use App\Http\Requests\WithRequestBindings;
use Dingo\Api\Routing\Helpers;

abstract class AbstractRestfulController implements RestfulControllerInterface
{
    use Helpers;
    use WithRequestBindings;
}
