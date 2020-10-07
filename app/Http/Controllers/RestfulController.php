<?php

namespace App\Http\Controllers;

use Dingo\Api\Http\Request;
use App\Exceptions\NotImplementedException;
use Dingo\Api\Routing\Helpers;

/**
 *
 * @author Eczek
 */
class RestfulController extends Controller {

    use Helpers;

    /**
     * @OA\Info(
     *   title="TC API",
     *   version="1.0",
     *   @OA\Contact(
     *     email="alberto@trailercentral.com",
     *     name="Trailercentral"
     *   )
     * )
     */


    /**
     * Displays a list of all records in the DB.
     * Paginated or not paginated
     */
    public function index(Request $request) {
        throw new NotImplementedException();
    }

    /**
     * Stores a record in the DB
     *
     * @param Request $request
     */
    public function create(Request $request) {
        throw new NotImplementedException();
    }

    /**
     * Display data about the record in the DB
     *
     * @param int $id
     */
    public function show(int $id) {
        throw new NotImplementedException();
    }

    /**
     * Updates the record data in the DB
     *
     * @param int $id
     * @param Request $request
     */
    public function update(int $id, Request $request) {
        throw new NotImplementedException();
    }

    /**
     * Deletes the record in the DB
     *
     * @param int $id
     */
    public function destroy(int $id) {
        throw new NotImplementedException();
    }

    /**
     * @return \Dingo\Api\Http\Response
     */
    protected function successResponse()
    {
        return $this->response->array([
            'response' => ['status' => 'success']
        ]);
    }

    /**
     * @param mixed $id
     * @return \Dingo\Api\Http\Response
     */
    protected function createdResponse($id = null)
    {
        $params = [
            'response' => ['status' => 'success']
        ];

        if ($id) {
            $params['response']['data'] = ['id' => $id];
        }

        return $this->response->array($params);
    }
}
