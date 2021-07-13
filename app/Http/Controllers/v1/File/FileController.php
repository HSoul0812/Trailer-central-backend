<?php

namespace App\Http\Controllers\v1\File;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\File\UploadFileLocalRequest;
use App\Http\Requests\Request;
use App\Services\File\FileService;
use App\Services\File\ImageService;
use App\Transformers\File\FileTransformer;

/**
 * Class FileController
 * @package App\Http\Controllers\v1\File
 */
class FileController extends RestfulControllerV2
{
    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var ImageService
     */
    private $imageService;

    /**
     * FileController constructor.
     * @param FileService $fileService
     * @param ImageService $imageService
     */
    public function __construct(FileService $fileService, ImageService $imageService)
    {
        $this->fileService = $fileService;
        $this->imageService = $imageService;

        //$this->middleware('setDealerIdOnRequest')->only(['uploadLocal']);
    }


    public function uploadLocal(Request $request)
    {
        $fileRequest = new UploadFileLocalRequest($request->all());

        print_r($request->file('file'));exit();

        if (!$fileRequest->validate() || !($result = $this->fileService->uploadLocal($fileRequest->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->response->item($result, new FileTransformer());
    }
}
