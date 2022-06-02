<?php

namespace App\Http\Controllers\v1\File;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\File\UploadFileLocalRequest;
use App\Http\Requests\File\UploadTwilioFileLocalRequest;
use App\Services\File\FileServiceInterface;
use App\Transformers\File\FileTransformer;
use Dingo\Api\Http\Response;
use Illuminate\Http\Request;

/**
 * Class FileController
 * @package App\Http\Controllers\v1\File
 */
class FileController extends RestfulControllerV2
{
    /**
     * @var FileServiceInterface
     */
    private $fileService;

    /**
     * FileController constructor.
     * @param FileServiceInterface $fileService
     */
    public function __construct(FileServiceInterface $fileService)
    {
        $this->fileService = $fileService;

        $this->middleware('setDealerIdOnRequest')->only(['uploadLocal', 'twilioUploadLocal']);
    }

    /**
     *  @OA\Post(
     *     path="/api/files/local",
     *     description="Upload a file local",
     *     tags={"Files"},
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="file",
     *         in="query",
     *         description="The uploaded file data",
     *         required=true,
     *         @OA\Schema(type="file")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a file url",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param Request $request
     * @return Response
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function uploadLocal(Request $request): Response
    {
        $fileRequest = new UploadFileLocalRequest($request->all());

        if (!$fileRequest->validate() || !($result = $this->fileService->uploadLocal($fileRequest->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->response->item($result, new FileTransformer());
    }

    /**
     *  @OA\Post(
     *     path="/api/files/local",
     *     description="Upload a file local",
     *     tags={"Files"},
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="file",
     *         in="query",
     *         description="The uploaded file data",
     *         required=true,
     *         @OA\Schema(type="file")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a file url",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param Request $request
     * @return Response
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function twilioUploadLocal(Request $request): Response
    {
        $fileRequest = new UploadTwilioFileLocalRequest($request->all());

        if (!$fileRequest->validate() || !($result = $this->fileService->uploadLocal($fileRequest->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->response->item($result, new FileTransformer());
    }
}
