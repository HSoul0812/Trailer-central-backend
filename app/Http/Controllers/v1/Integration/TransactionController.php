<?php

namespace App\Http\Controllers\v1\Integration;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Integration\PostTransactionRequest;
use App\Services\Integration\Transaction\TransactionServiceInterface;
use Dingo\Api\Http\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;

/**
 * Class TransactionController
 * @package App\Http\Controllers\v1\Integration
 */
class TransactionController extends RestfulControllerV2
{
    /**
     * @var TransactionServiceInterface
     */
    private $transactionService;

    public function __construct(TransactionServiceInterface $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|null
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function post(Request $request)
    {
        $request = new PostTransactionRequest($request->all());

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $result = $this->transactionService->post($request->all());

        return $this->xmlResponse($result);
    }
}
