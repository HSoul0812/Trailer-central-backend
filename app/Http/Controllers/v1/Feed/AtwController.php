<?php

namespace App\Http\Controllers\v1\Feed;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Feed\CreateAtwInventoryRequest;
use App\Http\Requests\Feed\UpdateAtwInventoryRequest;
use App\Services\Import\Feed\DealerFeedUploaderService;
use Dingo\Api\Http\Request;
use App\Repositories\Feed\TransactionExecuteQueueRepositoryInterface;

/**
 * 
 * @package App\Http\Controllers\v1\Feed
 */
class AtwController extends RestfulControllerV2
{

    private const CREATE_RESPONSE_SUCCESFUL_MESSAGE = 'Payload was successful.';
    
    /**
     *
     * @var TransactionExecuteQueueRepositoryInterface 
     */
    protected $repository;
    
    public function __construct(TransactionExecuteQueueRepositoryInterface $transactionExecuteQueueRepo)
    {
        $this->repository = $transactionExecuteQueueRepo;
    }
    
    /**
     * Upload source data
     *
     * @param Request $request
     * @param string $code
     * @param DealerFeedUploaderService $feedUploader
     * @return \Dingo\Api\Http\Response|void
     *
     * @QA\Post(
     *     path="/api/feed/uploader/{code}",
     *     description="Upload source data",
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Page Limit",
     *         required=false
     *     )
     * )
     */
    public function create(Request $request)
    {
        $request = new CreateAtwInventoryRequest($request->all());

        if ($request->validate()) {
            $vins = $this->repository->createBulk($request->all());
            return $this->response->array([
                'message' => self::CREATE_RESPONSE_SUCCESFUL_MESSAGE,
                'vins' => $vins
            ]);
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function update(Request $request)
    {
        $request = new UpdateAtwInventoryRequest($request->all());

        if ($request->validate()) {
            $vins = $this->repository->updateBulk($request->all());
            return $this->response->array([
                'message' => self::CREATE_RESPONSE_SUCCESFUL_MESSAGE,
                'vins' => $vins
            ]);
        }
        
        return $this->response->errorBadRequest();
    }
}
