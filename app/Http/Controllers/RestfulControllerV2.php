<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;

/**
 * Class RestfulControllerV2
 *
 * Alternate base restful controller
 *
 * @package App\Http\Controllers
 */
class RestfulControllerV2 extends Controller
{
    use Helpers;

    /**
     * @return \Dingo\Api\Http\Response
     */
    protected function successResponse()
    {
        return $this->response->array([
            'response' => ['status' => 'success']
        ]);
    }
}
