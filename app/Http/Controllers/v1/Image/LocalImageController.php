<?php

namespace App\Http\Controllers\v1\Image;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Image\UploadLocalImageRequest;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Integrations\TrailerCentral\Api\Image\ImageServiceInterface;

class LocalImageController extends AbstractRestfulController
{
    public function __construct(private ImageServiceInterface $imageService)
    {
        parent::__construct();
    }

    public function index(IndexRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function create(CreateRequestInterface $request)
    {
        $request->validate();

        $imageUrl = $this->imageService->uploadLocalImage($request->input('file'));

        return $this->response->array([
            'data' => [
                'url' => $imageUrl,
            ],
        ]);
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
            return inject_request_data(UploadLocalImageRequest::class);
        });
    }
}
