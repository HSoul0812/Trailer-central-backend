<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Leads\GetProductsRequest;
use App\Repositories\CRM\Leads\UnitRepositoryInterface;
use App\Transformers\CRM\Leads\ProductTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class ProductController
 * @package App\Http\Controllers\v1\CRM\Leads
 */
class ProductController extends RestfulControllerV2
{
    /**
     * @var UnitRepositoryInterface
     */
    private $unitRepository;

    /**
     * @param UnitRepositoryInterface $unitRepository
     */
    public function __construct(UnitRepositoryInterface $unitRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);

        $this->unitRepository = $unitRepository;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException|BindingResolutionException
     */
    public function index(Request $request): Response
    {
        $request = new GetProductsRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {
            $transformer = app()->make(ProductTransformer::class);
            return $this->collectionResponse($this->unitRepository->getAll($requestData), $transformer);
        }

        return $this->response->errorBadRequest();
    }
}
