<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Inventory\Overlay\BulkUpdateCustomOverlaysRequest;
use App\Http\Requests\Inventory\Overlay\UpdateCustomOverlayRequest;
use App\Services\Inventory\CustomOverlay\CustomOverlayServiceInterface;
use App\Transformers\Inventory\CustomOverlayTransformer;
use App\Http\Requests\Inventory\Overlay\GetCustomOverlaysRequest;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class CustomOverlayController extends RestfulControllerV2
{

    /** @var CustomOverlayServiceInterface */
    private $service;

    /** @var CustomOverlayTransformer */
    private $transformer;

    public function __construct(CustomOverlayServiceInterface $service, CustomOverlayTransformer $transformer)
    {
        $this->service = $service;
        $this->transformer = $transformer;

        $this->middleware('setDealerIdOnRequest')->only(['index', 'bulkUpdate', 'update']);
    }

    /**
     * @param Request $request
     * @return \Dingo\Api\Http\Response|void
     *
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function index(Request $request): Response
    {
        $request = new GetCustomOverlaysRequest($request->all());

        if ($request->validate()) {
            return $this->response->collection($this->service->list($request->dealer_id), $this->transformer);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param Request $request
     * @return \Dingo\Api\Http\Response|void
     *
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function bulkUpdate(Request $request)
    {
        $request = new BulkUpdateCustomOverlaysRequest($request->all());

        if ($request->validate() && $this->service->bulkUpsert($request->dealer_id, $request->overlays)) {
            return $this->successResponse();
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param Request $request
     * @return \Dingo\Api\Http\Response|void
     *
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function update(Request $request)
    {
        $request = new UpdateCustomOverlayRequest($request->all());

        if ($request->validate() && $this->service->upsert($request->dealer_id, $request->name, $request->value)) {
            return $this->successResponse();
        }

        $this->response->errorBadRequest();
    }
}
