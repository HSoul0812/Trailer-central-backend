<?php

namespace App\Http\Controllers\v1\Showroom;

use Dingo\Api\Http\Request;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Showroom\ShowroomGetRequest;
use App\Transformers\Inventory\ManufacturerTransformer;
use App\Repositories\Showroom\ShowroomBulkUpdateRepository;
use App\Http\Requests\Showroom\ShowroomBulkUpdateYearRequest;
use App\Http\Requests\Showroom\ShowroomBulkUpdateVisibilityRequest;

/**
 * Class BulkUpdateController
 * @package App\Http\Controllers\v1\Manufacturer
 */
class ShowroomBulkUpdateController extends RestfulController
{

    /**
     * @var ShowroomBulkUpdateRepository
     */
    protected $bulkUpdateRepository;


    /**
     * Create a new controller instance.
     *
     * @param ShowroomBulkUpdateRepository $bulkUpdateRepository
     */
    public function __construct(
        ShowroomBulkUpdateRepository $bulkUpdateRepository
    )
    {
        $this->bulkUpdateRepository = $bulkUpdateRepository;
    }

    /**
     * Retrieves a list of Manufacturers
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request) {
        $request = new ShowroomGetRequest($request->all());

        if ( $request->validate() ) {
            return $this->response->collection($this->bulkUpdateRepository->getAll($request->all()), new ManufacturerTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Updates Showrooms year based on manufacturer
     *
     * @param Request $request
     * @return mixed
     */
    public function bulkUpdateYear(Request $request) {
        $request = new ShowroomBulkUpdateYearRequest($request->all());

        if ( $request->validate() ) {
            try {
                $this->bulkUpdateRepository->bulkUpdateYear($request->all());

                return $this->response->array([
                    'status' => 'success',
                    'message' => 'Updating Showrooms'
                ]);
            } catch (\Exception $e) {
                return $this->response->array([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }

        }

        return $this->response->errorBadRequest();
    }

    /**
     * Updates Showrooms visibility based on manufacturer
     *
     * @param Request $request
     * @return mixed
     */
    public function bulkUpdateVisibility(Request $request) {
        $request = new ShowroomBulkUpdateVisibilityRequest($request->all());

        if ( $request->validate() ) {
            try {
                $this->bulkUpdateRepository->bulkUpdateVisibility($request->all());

                return $this->response->array([
                    'status' => 'success',
                    'message' => 'Updating Showrooms'
                ]);
            } catch (\Exception $e) {
                return $this->response->array([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }

        }

        return $this->response->errorBadRequest();
    }
}
