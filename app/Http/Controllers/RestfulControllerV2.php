<?php

namespace App\Http\Controllers;

use Dingo\Api\Http\Response;
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
     * @return Response
     */
    protected function successResponse(): Response
    {
        return $this->response->array([
            'response' => ['status' => 'success']
        ]);
    }

    /**
     * @param mixed $id
     * @return Response
     */
    protected function updatedResponse($id = null): Response
    {
        $params = [
            'response' => ['status' => 'success']
        ];

        if ($id) {
            $params['response']['data'] = ['id' => $id];
        }

        return $this->response->array($params);
    }

    /**
     * @param mixed $id
     * @return Response
     */
    protected function createdResponse($id = null): Response
    {
        $params = [
            'response' => ['status' => 'success']
        ];

        if ($id) {
            $params['response']['data'] = ['id' => $id];
        }

        return $this->response->created(null, $params);
    }

    /**
     * @return Response
     */
    protected function deletedResponse(): Response
    {
        return $this->response->noContent();
    }
}
