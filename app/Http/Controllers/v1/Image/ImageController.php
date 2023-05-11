<?php

namespace App\Http\Controllers\v1\Image;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Image\UploadImageRequest;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Integrations\TrailerCentral\Api\Image\ImageServiceInterface;

class ImageController extends AbstractRestfulController
{
    public function __construct(
        private ImageServiceInterface $imageService
    ) {
        parent::__construct();
    }

    public function index(IndexRequestInterface $request)
    {
        return new NotImplementedException();
    }

    public function create(CreateRequestInterface $request)
    {
        if ($request->validate()) {
            $user = auth('api')->user();

            return $this->response->array($this->imageService->uploadImage(
                $user->tc_user_id, $request->file
            ));
        }

        return $this->response->errorBadRequest();
    }

    public function show(int $id)
    {
        throw new NotImplementedException();
    }

    public function update(int $id, UpdateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function destroy(int $id)
    {
        throw new NotImplementedException();
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(CreateRequestInterface::class, function () {
            return inject_request_data(UploadImageRequest::class);
        });
    }
}
