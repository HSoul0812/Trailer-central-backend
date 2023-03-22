<?php

namespace App\Http\Controllers\v1\Website\Image;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Requests\Website\Image\CreateWebsiteImageRequest;
use App\Http\Requests\Website\Image\DeleteWebsiteImageRequest;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Website\Image\GetWebsiteImageRequest;
use App\Http\Requests\Website\Image\UpdateWebsiteImageRequest;
use App\Repositories\Website\Image\WebsiteImageRepositoryInterface;
use App\Transformers\Website\Image\WebsiteImageTransformer;

class WebsiteImagesController extends RestfulControllerV2
{
    /** @var WebsiteImageRepositoryInterface */
    protected $websiteImage;

    /** @var WebsiteImageTransformer */
    protected $transformer;


    public function __construct(WebsiteImageRepositoryInterface $websiteImageRepository)
    {
        $this->middleware('setDealerIdOnRequest');

        $this->websiteImage = $websiteImageRepository;
        $this->transformer = new WebsiteImageTransformer();
    }

    /**
     * @param int $websiteId
     * @param Request $request
     * @return Response|void
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function index(int $websiteId, Request $request)
    {
        $data = ['website_id' => $websiteId] + $request->all();
        $request = new GetWebsiteImageRequest($data);

        if ($request->validate()) {
            return $this->response->paginator($this->websiteImage->getAll($data), $this->transformer);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param int $websiteId
     * @param int $imageId
     * @param Request $request
     * @return Response|void
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function update(int $websiteId, int $imageId, Request $request)
    {
        $request = new UpdateWebsiteImageRequest(['id' => $imageId, 'website_id' => $websiteId] + $request->all());

        if ($request->validate()) {
            $image = $this->websiteImage->update($request->all());
            return $this->response->item($image, $this->transformer);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param int $websiteId
     * @param Request $request
     * @return Response|void
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function create(int $websiteId, Request $request)
    {
        $request = new CreateWebsiteImageRequest(['website_id' => $websiteId] + $request->all());
        if ($request->validate()) {
            $image = $this->websiteImage->create($request->all());
            return $this->response->item($image, $this->transformer);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param int $websiteId
     * @param int $imageId
     * @param Request $request
     * @return Response|void
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function delete(int $websiteId, int $imageId, Request $request)
    {
        $request = new DeleteWebsiteImageRequest(['id' => $imageId, 'website_id' => $websiteId] + $request->all());
        if ($request->validate()) {
            $this->websiteImage->delete($request->all());
            return $this->deletedResponse();
        }

        $this->response->errorBadRequest();
    }
}
