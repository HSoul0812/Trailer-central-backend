<?php

namespace App\Http\Controllers\v1\Bulk\Parts;

use App\Http\Controllers\RestfulController;
use App\Models\Bulk\Parts\BulkUploadPayload;
use App\Repositories\Bulk\BulkUploadRepositoryInterface;
use App\Services\Export\Parts\BulkUploadJobServiceInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\Bulk\Parts\CreateBulkUploadRequest;
use App\Http\Requests\Bulk\Parts\GetBulkUploadsRequest;
use App\Transformers\Bulk\Parts\BulkUploadTransformer;
use Dingo\Api\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BulkUploadController extends RestfulController
{
    /**
     * @var BulkUploadRepositoryInterface
     */
    protected $repository;

    /**
     * @var BulkUploadJobServiceInterface
     */
    protected $service;

    /**
     * Create a new controller instance.
     *
     * @param BulkUploadRepositoryInterface $repository
     * @param BulkUploadJobServiceInterface $service
     */
    public function __construct(BulkUploadRepositoryInterface $repository, BulkUploadJobServiceInterface $service)
    {
        $this->middleware('setDealerIdOnRequest')->only(['create']);
        $this->repository = $repository;
        $this->service = $service;
    }

    /**
     * @param Request $request
     * @return Response|void
     * @throws HttpException when there was a bad request
     */
    public function index(Request $request): Response
    {
        $request = new GetBulkUploadsRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->repository->getAll($request->all()), new BulkUploadTransformer);
        }

        $this->response->errorBadRequest();
    }


    /**
     * @param Request $request
     * @return Response|void
     * @throws HttpException when there was a bad request
     */
    public function create(Request $request): Response
    {
        $request = new CreateBulkUploadRequest($request->all());

        if ($request->validate()) {
            $dealerId = $request->get('dealer_id');
            $payload = BulkUploadPayload::from(['csv_file' => $request->get('csv_file')]);

            $model= $this->service->setup($dealerId, $payload);

            $this->service->dispatch($model);

            return $this->response->item($model, new BulkUploadTransformer);
        }

        $this->response->errorBadRequest();
    }
}
