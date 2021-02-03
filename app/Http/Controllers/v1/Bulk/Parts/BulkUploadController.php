<?php

namespace App\Http\Controllers\v1\Bulk\Parts;

use App\Exceptions\Common\BusyJobException;
use App\Http\Controllers\RestfulController;
use App\Jobs\ProcessBulkUpload;
use App\Models\Bulk\Parts\BulkUpload;
use App\Models\Bulk\Parts\BulkUploadPayload;
use App\Repositories\Bulk\Parts\BulkUploadRepositoryInterface;
use App\Services\Common\MonitoredGenericJobServiceInterface;
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
     * @var MonitoredGenericJobServiceInterface
     */
    protected $service;

    /**
     * Create a new controller instance.
     *
     * @param BulkUploadRepositoryInterface $repository
     * @param MonitoredGenericJobServiceInterface $service
     */
    public function __construct(BulkUploadRepositoryInterface $repository, MonitoredGenericJobServiceInterface $service)
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
     * @throws BusyJobException when there is currently other job working
     */
    public function create(Request $request): Response
    {
        $request = new CreateBulkUploadRequest($request->all());

        if ($request->validate()) {
            $payload = BulkUploadPayload::from(['csv_file' => $request->get('csv_file')]);

            $model = $this->service
                ->setup($request->get('dealer_id'), $payload, $request->get('token'), BulkUpload::class)
                ->withQueueableJob(static function (BulkUpload $job): ProcessBulkUpload {
                    return new ProcessBulkUpload($job);
                });

            $this->service->dispatch($model);

            return $this->response->item($model, new BulkUploadTransformer);
        }

        $this->response->errorBadRequest();
    }
}
