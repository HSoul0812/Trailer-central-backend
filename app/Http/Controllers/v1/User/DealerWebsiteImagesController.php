<?php

namespace App\Http\Controllers\v1\User;

use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\User\GetDealerImageRequest;
use App\Repositories\User\DealerImageRepositoryInterface;
use App\Transformers\User\DealerImageTransformer;
use Dingo\Api\Exception\ResourceException;

class DealerWebsiteImagesController extends RestfulControllerV2
{
    /** @var DealerImageRepositoryInterface */
    protected $dealerImage;

    /** @var DealerImageTransformer */
    protected $transformer;


    public function __construct(DealerImageRepositoryInterface $dealerImageRepository)
    {
        $this->middleware('setDealerIdOnRequest');

        $this->dealerImage = $dealerImageRepository;
        $this->transformer = new DealerImageTransformer();
    }

    /**
     * @return Response|void
     * @throws ResourceException when there was a failed validation
     */
    public function index(Request $request)
    {
        $request = new GetDealerImageRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->dealerImage->getAll($request->all()), $this->transformer);
        }

        $this->response->errorBadRequest();
    }
}
