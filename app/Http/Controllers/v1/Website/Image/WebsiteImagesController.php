<?php

namespace App\Http\Controllers\v1\Website\Image;

use App\Http\Requests\Website\Image\CreateWebsiteImageRequest;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Website\Image\GetWebsiteImageRequest;
use App\Http\Requests\Website\Image\UpdateWebsiteImageRequest;
use App\Repositories\Website\Image\WebsiteImageRepositoryInterface;
use App\Transformers\Website\Image\WebsiteImageTransformer;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     * @return Response|void
     * @throws ResourceException when there was a failed validation
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
     * @return Response|void
     *
     * @throws ModelNotFoundException
     * @throws ResourceException when there was a failed validation
     * @throws HttpException when the provided resource id does not belongs to dealer who has made the request
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

    public function create(int $websiteId, Request $request)
    {
        $request = new CreateWebsiteImageRequest(['website_id' => $websiteId] + $request->all());
        if ($request->validate()) {
            $image = $this->websiteImage->create($request->all());
            return $this->response->item($image, $this->transformer);
        }

        $this->response->errorBadRequest();
    }
}
