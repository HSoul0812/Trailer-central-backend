<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Integration;

use App\Exceptions\Common\BusyJobException;
use App\Http\Controllers\v1\Jobs\MonitoredJobsController;
use App\Http\Requests\Integration\CVR\SendFileRequest;
use App\Jobs\Integration\CVR\CvrSendFileJob;
use App\Models\Integration\CVR\CvrFile;
use App\Models\Integration\CVR\CvrFilePayload;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Repositories\Integration\CVR\CvrFileRepositoryInterface;
use App\Services\Integration\CVR\CvrFileServiceInterface;
use App\Transformers\Integration\CVR\CrvFileTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CvrController extends MonitoredJobsController
{
    /**
     * @var CvrFileRepositoryInterface
     */
    protected $repository;

    /**
     * @var CvrFileServiceInterface
     */
    protected $service;

    public function __construct(
        CvrFileRepositoryInterface $repository,
        MonitoredJobRepositoryInterface $jobsRepository,
        CvrFileServiceInterface $service)
    {
        parent::__construct($jobsRepository);

        $this->middleware('setDealerIdOnRequest')->only(['create']);

        $this->repository = $repository;
        $this->service = $service;
    }

    /**
     * @OA\Put(
     *     path="/api/integration/cvr",
     *     description="Enqueue a job to send a zipped file to the dealer CVR account",
     *     tags={"CVR"},
     *     operationId="create",
     *     @OA\Parameter(
     *         name="dealer_id",
     *         description="The dealer ID.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         description="The token for the job.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="zipped file to upload",
     *                     property="document",
     *                     type="string",
     *                     format="file",
     *                 ),
     *                 required={"document"}
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return Response|void
     * @throws BusyJobException
     * @throws HttpException when some validation rule has not passed
     */
    public function create(Request $request): Response
    {
        $request = SendFileRequest::createFrom($request);

        if ($request->validate()) {
            $payload = CvrFilePayload::from(['document' => $request->file('document')]);
            $dealerId = $request->get('dealer_id');
            $token = $request->get('token');

            $model = $this->service
                ->setup($dealerId, $payload, $token)
                ->withQueueableJob(static function (CvrFile $job): CvrSendFileJob {
                    return new CvrSendFileJob($job);
                });

            $this->service->dispatch($model);

            return $this->response->item($model, new CrvFileTransformer);
        }

        $this->response->errorBadRequest();
    }

    /**
     *
     * @OA\Get(
     *     path="/api/integration/cvr/{token}",
     *     description="Check status of the process",
     *     tags={"CVR"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true
     *     )
     * )
     */
}
